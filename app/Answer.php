<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    public function add(){//回答增加

    	//检查用户是否登录
    	if(!user_ins() -> is_logged_in()){
    		
    		return ['status' => 0,'msg' => '请先登录'];
    	}

    	if(!rq('question_id') || !rq('content')){

    		return ['status' => 0,'msg' => 'question_id and content are required'];
    	}    	

    	$question = question_ins() -> find(rq('question_id'));
    	if(!$question){
    		return ['status' => 0,'msg' => '该问题不存在'];
    	}

    	//禁止同一个用户对同一个问题重复回答
    	$answered = $this
    		-> where(['question_id' => rq('question_id'),'user_id' => session('user_id')])
    		->count();

    	if($answered){
    		return ['status' => 0,'msg' => '禁止同一个用户对同一个问题重复回答'];
    	}

    	$this -> content = rq('content');
    	$this -> question_id = rq('question_id');
    	$this -> user_id = session('user_id');

    	return $this -> save() ?
    		['status' => 1,'id' => $this -> id] :
    		['status' => 0,'msg' => '数据插入失败'];
    }

    public function edit(){//回答更新

    	//检查用户是否登录
    	if(!user_ins() -> is_logged_in()){
    		
    		return ['status' => 0,'msg' => '请先登录'];
    	}

    	if(!rq('id') || !rq('content')){

    		return ['status' => 0,'msg' => 'id and content are required'];
    	}    

    	$answer = $this -> find(rq('id'));
    	if($answer -> user_id != session('user_id')){
    		return ['status' => 0,'msg' => 'permission denied'];
    	}

    	$answer -> content = rq('content');

    	return $answer -> save() ?
    		['status' => 1] :
    		['status' => 0,'msg' => '数据更新失败'];        	

    }

    public function read(){//回答查看

    	if(!rq('id') && !rq('question_id')){//至少要有一个存在,都不存在是不允许的
    		return ['status' => 0,'msg' => 'id or question_id is required'];
    	}

    	if(rq('id')){//只要有回答的id,就只查看回答
    		$answer = $this -> find(rq('id'));

    		if(!$answer){
    			return ['status' => 0,'msg' => '该回答不存在'];
    		}

    		return ['status' => 1,'data' => $answer];
    	}

    	//查看该问题下的所有回答
    	if(!question_ins() -> find(rq('question_id'))){
    		return ['status' => 0,'msg' => '该问题不存在'];
    	}

    	$answers = $this
    		-> where('question_id',rq('question_id'))
    		-> get()
    		-> keyBy('id');

    	return ['status' => 1,'data' => $answers];
    }

    public function vote(){//给回答点赞成、反对

        //检查用户是否登录
        if(!user_ins() -> is_logged_in()){
            
            return ['status' => 0,'msg' => '请先登录'];
        }

        //id：给哪个回答投票
        //vote：投的是赞成还是反对票
        if(!rq('id') || !rq('vote')){

            return ['status' => 0,'msg' => 'id and vote are required'];
        }

        $answer = $this -> find(rq('id'));
        if(!$answer){
            return ['status' => 0,'msg' => '该回答不存在'];
        }

        //1：赞同 ; 2：反对
        $vote = rq('vote') <= 1 ? 1: 2;

        //检查此用户是否在相同回答下投过票,如果投过就删除之前的投票信息
        $answer 
            -> users()
            -> newPivotStatement()//进入关联表,进行操作
            -> where('user_id',session('user_id'))
            -> where('answer_id',rq('id'))
            -> delete();

        //在关联表中增加数据
        $answer
            -> users()
            -> attach(session('user_id'),['vote' => $vote]);

        return ['status' => 1];

    }

    public function users(){

        return $this
            -> belongsToMany('App\User')
            -> withPivot('vote')//vote是自定义字段名,自定义字段都需要单独提取出来写在这里
            -> withTimestamps();//更新时更新时间会变化,创建时创建、更新时间会变化
    }
}
