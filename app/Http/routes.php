<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {// 表示访问zhihu.local/

    return view('index');
});

Route::get('api', function () {// 表示访问zhihu.local/api

    return ['version' => 0.1];
});

Route::get('api2', function () {// 表示访问zhihu.local/api2

    return 'abc';	
});

//项目开始

//公共函数

//接收值
function rq($key = null,$default = null){

	if(!$key){
		return Request::all();
	}
	return Request::get($key,$default);
}

//分页
function paginate($page = 1,$limit){

	$limit = $limit ?: 16;
	$skip = ($page ? $page - 1 : 0) * $limit;
	return [$limit,$skip];
}

//返回错误信息
function err($msg = null){

	return ['status' => 0,'msg' => $msg];
}
//返回正确信息
function suc($data_to_merge = []){
	$data = ['status' => 1,'data' => []];
	if($data_to_merge){
		$data['data'] = array_merge($data['data'],$data_to_merge);
	}
	return $data;
}

    // 检测用户是否登录
    function is_logged_in(){
        //如果session中存在user_id就返回user_id,否则返回false
        return session('user_id') ?:false; 
    }

//用户api
// Route::any('api/user', function () {// 表示访问zhihu.local/api/user
	
// 	$user = new App\User;
// 	return $user -> signup();
// });

// 改写成
function user_ins(){

	return new App\User;
}

Route::any('api/signup', function () {// 表示访问zhihu.local/api/user
	
	return user_ins() -> signup();
});

Route::any('api/login', function () {
	
	return user_ins() -> login();
});

Route::any('api/logout', function () {
	
	return user_ins() -> logout();
});

Route::any('api/user/edit_password', function () {//修改密码(记得旧密码的情况)
	
	return user_ins() -> edit_password();
});

Route::any('api/user/reset_password', function () {//找回密码(最后得重置密码,忘了旧密码的情况)
	
	return user_ins() -> reset_password();
});

Route::any('api/user/validate_reset_password', function () {//找回密码2(最后得重置密码,忘了旧密码的情况)
	
	return user_ins() -> validate_reset_password();
});

Route::any('api/user/readUserInfo', function () {//读取用户个人信息,游客也可以看得到
	
	return user_ins() -> readUserInfo();
});

Route::any('api/user/exist', function () {//验证用户名是否已存在
	
	return user_ins() -> exist();
});

//问题api
function question_ins(){

	return new App\Question;
}

Route::any('api/question/add', function () {
	
	return question_ins() -> add();
});

Route::any('api/question/edit', function () {
	
	return question_ins() -> edit();
});

Route::any('api/question/read', function () {
	
	return question_ins() -> read();
});

Route::any('api/question/remove', function () {
	
	return question_ins() -> remove();
});

//回答api
function answer_ins(){

	return new App\Answer;
}

Route::any('api/answer/add', function () {
	
	return answer_ins() -> add();
});

Route::any('api/answer/edit', function () {
	
	return answer_ins() -> edit();
});

Route::any('api/answer/read', function () {
	
	return answer_ins() -> read();
});

Route::any('api/answer/vote', function () {//给回答点赞成、反对
	
	return answer_ins() -> vote();
});

//评论api
function comment_ins(){

	return new App\Comment;
}

Route::any('api/comment/add', function () {
	
	return comment_ins() -> add();
});

Route::any('api/comment/read', function () {
	
	return comment_ins() -> read();
});

Route::any('api/comment/remove', function () {
	
	return comment_ins() -> remove();
});

//时间线api
Route::any('api/timeline','CommonController@timeline');

Route::any('test', function () {
	
	dd(user_ins() -> is_logged_in());
});

