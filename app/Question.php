<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    public function add(){//新增问题

    	//检查用户是否登录
    	if(!user_ins() -> is_logged_in()){
    		
    		return ['status' => 0,'msg' => '请先登录'];
    	}

    	//检测title是否有填写
    	if(!rq('title')){
    		return ['status' => 0,'msg' => '问题标题必须填写'];
    	}

    	$this -> title = rq('title');
    	$this -> user_id = session('user_id');

    	if(rq('desc')){
    		$this -> desc = rq('desc');
    	}

    	return $this -> save() ?
    		['status' => 1,'id' => $this -> id] :
    		['status' => 0,'msg' => '数据插入失败'];
    }

    public function edit(){//更新问题

    	//检查用户是否登录
    	if(!user_ins() -> is_logged_in()){
    		
    		return ['status' => 0,'msg' => '请先登录'];
    	}

    	//检测要更新的问题的id是否存在
    	if(!rq('id')){
    		return ['status' => 0,'msg' => 'id is required'];
    	}

    	//查找出指定id的那条信息
    	$question = $this -> find(rq('id'));

    	//如果该id不存在
    	if(!$question){
    		return ['status' => 0,'msg' => '该问题不存在'];
    	}

    	//如果当前操作的用户并不是发表问题的用户,将被禁止该更新操作
    	if($question -> user_id != session('user_id')){
    		return ['status' => 0,'msg' => 'permission denied'];
    	}

    	//如果title有更新的话
    	if(rq('title')){
    		$question -> title = rq('title');
    	}

    	//如果desc有更新的话
    	if(rq('desc')){
    		$question -> desc = rq('desc');
    	}    	

    	return $question -> save() ?
    		['status' => 1] :
    		['status' => 0,'msg' => '数据更新失败'];    	
    }

    public function read(){//查看问题

    	//检测要查看的问题的id是否存在
    	//查看一条数据
    	if(rq('id')){
    		return ['status' => 1,'data' => $this -> find(rq('id'))];
    	}

    	//查看多条数据
    	//默认返回15条数据
    	// $limit = rq('limit') ?: 15;
    	// $skip = (rq('page') ? rq('page') - 1 : 0) * $limit;
            //改写成
            list($limit,$skip) = paginate(rq('page'),rq('limit'));

    	$r = $this
    		-> orderBy('created_at')
    		-> limit($limit)
    		-> skip($skip)
    		-> get(['id','title','desc','created_at','updated_at'])
    		-> keyBy('id');

    	return ['status' => 1,'data' => $r];
    }

    public function remove(){//删除问题

    	//检查用户是否登录
    	if(!user_ins() -> is_logged_in()){
    		
    		return ['status' => 0,'msg' => '请先登录'];
    	}

    	if(!rq('id')){
    		return ['status' => 0,'msg' => 'id is required'];
    	}

    	$question = $this -> find(rq('id'));

    	if(!$question){
    		return ['status' => 0,'msg' => '该问题不存在'];
    	}

    	if(session('user_id') != $question -> user_id){
    		return ['status' => 0,'msg' => 'permission denied'];
    	}

    	return $question -> delete() ?
		['status' => 1] :
		['status' => 0,'msg' => '数据删除失败'];	    		
    }
}
