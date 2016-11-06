<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Request;
use Hash;

class User extends Model
{
    public function signup(){//注册api

    	// 检查用户名和密码是否为空
    	$has_username_and_password = $this -> _has_username_and_password();

    	if(!$has_username_and_password){
    		return err('用户名和密码皆不可为空');
    	}
    	$username = $has_username_and_password[0];
    	$password = $has_username_and_password[1];

    	// 检查用户名是否已存在
    	$user_exists = $this
    		-> where('username',$username)
    		-> exists();

    	if($user_exists){
    		return err('用户名已存在');    		
    	}

    	// 加密密码
    	$hashed_password = bcrypt($password);

    	// 存入数据库
    	$this -> username = $username;
    	$this -> password = $hashed_password;

    	if($this -> save()){
    		return suc(['id' => $this -> id]);
    	}else{
    		return err('数据插入失败');
    	} 	
    	
    }

    public function login(){//登录api

        $has_username_and_password = $this -> _has_username_and_password();

        // 检查用户名和密码是否为空
        if(!$has_username_and_password){
            return err('用户名和密码皆不可为空');
        }

        // 检查用户名是否存在
        $username = $has_username_and_password[0];
        $password = $has_username_and_password[1];    

        // 检查用户是否存在
        $user = $this
            -> where('username',$username)
            -> first();

        if(!$user){
            return err('用户不存在');
        }

        // 检查密码是否正确
        $hashed_password = $user -> password;
        if(!Hash::check($password,$hashed_password)){
            return err('密码有误');
        }

        // 将用户信息写入session
        session() -> put('username',$user -> username);
        session() -> put('user_id',$user -> id);

        return suc(['id' => $user -> id]);

    }

    private function _has_username_and_password(){

    	$username = rq('username');
    	$password = rq('password');

    	if($username && $password){
    		return [$username,$password];
    	}

    	return false;
    }

    public function logout(){//登出api

        // 删除username、user_id
        // session() -> flush();//清除所有session
        // 或者
        // session() -> put('username',null);
        // session() -> put('user_id',null);
        // 或者
        session() -> forget('username');
        session() -> forget('user_id');

        // session() -> set('person.friend.xiamgming.age','20');

        // dd(session() -> all());
        
        return suc();

        //跳转到首页
        return redirect('/');
    }

    // 检测用户是否登录
    public function is_logged_in(){

        // //如果session中存在user_id就返回user_id,否则返回false
        // return session('user_id') ?:false; 
        
        //重写
        return is_logged_in();
    }

    //修改密码api
    public function edit_password(){

        if(!$this -> is_logged_in()){

            // return ['status' => 0,'msg' => '请先登录'];
            return err('请先登录');
        }

        if(!rq('old_password') || !rq('new_password')){

            // return ['status' => 0,'msg' => 'old_password and new_password are required'];
            return err('old_password and new_password are required');
        }

        $user = $this -> find(session('user_id'));

        if(!Hash::check(rq('old_password'),$user -> password)){
            // return ['status' => 0,'msg' => '密码错误'];
            return err('密码错误');
        }

        $user -> password = bcrypt(rq('new_password'));

        return $user -> save() ?
            suc() :
            err('数据更新失败');
    }

    //找回密码
    public function reset_password(){

            //验证是不是机器破解
            if($this -> _is_robot(2)){

                return err('max frequency reached');
            }

            //通过手机号重置密码
            if(!rq('phone')){
                return err('phone is required');
            }

            $user = $this -> where('phone',rq('phone')) -> first();

            if(!$user){
                return err('invalid phone number');
            }

            //生成验证码
            $captcha = $this -> _generate_captcha();
            $user -> phone_captcha = $captcha;

            if($user -> save()){

                //如果验证码保存成功,发送验证码短信
                $this -> _send_sms();

                $this -> _update_robot_time();

                return suc();
            }

            return err('数据更新失败');
    }

    //找回密码2
    public function validate_reset_password(){

        //防止用户在输入正确的验证码的情况下多次请求修改密码
        if($this -> _is_robot()){

            return err('max frequency reached');
        }        

        if(!rq('phone') || !rq('phone_captcha') || !rq('new_password')){

            return err('phone,phone_captcha and new_password are required');
        }

        $user = $this -> where([

            'phone' => rq('phone'),
            'phone_captcha' => rq('phone_captcha')
        ]) -> first();

        if(!$user){

            return err('invalid phone or phone_captcha');
        }

        $user -> password = bcrypt(rq('new_password'));
        $this -> _update_robot_time();
        return $user -> save() ?
            suc() :
            err('数据更新失败');
    }

    private function _is_robot($time = 10){

            $current_time = time();
            $last_sms_time = session('last_sms_time');

            return ($current_time - $last_sms_time < $time);        
    }

    private function _update_robot_time(){

            session() -> set('last_sms_time',time());
    }

    //发送短信
    private function _send_sms(){

        return true;
    }

    //生成找回密码的验证码
    private function _generate_captcha(){

        return rand(1000,9999);
    }

    public function answers(){

        return $this
            -> belongsToMany('App\Answer')
            -> withPivot('vote')//vote是自定义字段名
            -> withTimestamps();//更新时更新时间会变化,创建时创建、更新时间会变化
    }    

    public function questions(){

        return $this
            -> belongsToMany('App\Question')
            -> withPivot('vote')//vote是自定义字段名
            -> withTimestamps();//更新时更新时间会变化,创建时创建、更新时间会变化
    }  

    //读取用户个人信息,游客也可以看得到
    public function readUserInfo(){

        if(!rq('id')){
            return err('required id');
        }

        $get = ['id','username','avatar_url','intro'];
        $user = $this -> find(rq('id'),$get);
        // $user = $this -> get($get);
        $data = $user -> toArray();

        //获得该用户的回答数和提问数
        
        //需要建立问题和用户关系表,自己做！！！
        // $answer_count = $user -> answers() -> count();
        // $question_count = $user -> questions() -> count();
        
        $answer_count = answer_ins() -> where('user_id',rq('id')) -> count();
        $question_count = question_ins() -> where('user_id',rq('id')) -> count();

        // dd($answer_count,$question_count);
        
        $data['answer_count'] = $answer_count;
        $data['question_count'] = $question_count;

        return suc($data);


    }

    public function exist(){

        return suc(['count' => $this -> where(rq()) -> count()]);
    }
}
