<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    public function add(){//评论新增

    	//检查用户是否登录
    	if(!user_ins() -> is_logged_in()){
    		
    		return ['status' => 0,'msg' => '请先登录'];
    	}

    	if(!rq('content')){
    		return ['status' => 0,'msg' => '评论内容不能为空'];
    	}

    	if(//都不存在 和 都存在 都是不被允许的
    		(!rq('question_id') && !rq('answer_id')) ||
    		(rq('question_id') && rq('answer_id'))
    	){
    		return ['status' => 0,'msg' => 'question_id or answer_id is required'];
    	}

    	if(rq('question_id')){//如果是给问题添加评论

    		$question = question_ins() -> find(rq('question_id'));
    		if(!$question){
    			return ['status' => 0,'msg' => '该问题不存在'];
    		}
    		$this -> question_id = rq('question_id');
    	}else{//如果是给回答添加评论

    		$answer = answer_ins() -> find(rq('answer_id'));
    		if(!$answer){
    			return ['status' => 0,'msg' => '该回答不存在'];
    		}
    		$this -> answer_id = rq('answer_id');    		
    	}

    	//检查是否在评论中评论
    	if(rq('reply_to')){
    		$target = $this -> find(rq('reply_to'));
    		//检查目标评论是否存在
    		if(!$target){
    			return ['status' => 0,'msg' => '目标评论不存在'];
    		}

    		//检查是否在评论自己的评论
    		if($target -> user_id == session('user_id')){
    			return ['status' => 0,'msg' => 'cannot reply to yourself'];
    		}
    		$this -> reply_to = rq('reply_to');
    	}

    	$this -> content = rq('content');
    	$this -> user_id = session('user_id');
    	return $this -> save() ?
    		['status' => 1,'id' => $this -> id] :
    		['status' => 0,'msg' => '数据插入失败'];

    }

    public function read(){//评论查看

    	if(!rq('question_id') && !rq('answer_id')){
    		return ['status' => 0,'msg' => 'question_id or answer_id is required'];
    	}

    	if(rq('question_id')){
    		$question = question_ins() -> find(rq('question_id'));
    		if(!$question){
    			return ['status' => 0,'msg' => '该问题不存在'];
    		}
    		$data = $this -> where('question_id',rq('question_id'));
    	}else{
    		$answer = answer_ins() -> find(rq('answer_id'));
    		if(!$answer){
    			return ['status' => 0,'msg' => '该回答不存在'];
    		}
    		$data = $this -> where('answer_id',rq('answer_id'));    		
    	}

    	$data = $data -> get() -> keyBy('id');
    	return ['status' => 1,'data' => $data];
    }

    public function remove(){//评论删除

    	//检查用户是否登录
    	if(!user_ins() -> is_logged_in()){
    		
    		return ['status' => 0,'msg' => '请先登录'];
    	}

    	if(!rq('id')){
    		return ['status' => 0,'msg' => 'id is required'];
    	}

    	$comment = $this -> find(rq('id'));
    	if(!$comment){
    		return ['status' => 0,'msg' => '该评论不存在'];
    	}

    	if($comment -> user_id != session('user_id')){
    		return ['status' => 0,'msg' => 'permission denied'];
    	}

    	//删除评论之前,应先删除对该评论的评论
    	$this -> where('reply_to',rq('id')) -> delete();

    	return $comment -> delete() ?
    		['status' => 1] :
    		['status' => 0,'数据删除失败'];

    }
}
