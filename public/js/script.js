// ;(function(){})();
;(function(){

	'use strict';

	angular.module('zhihu',[

		'ui.router',

	])//[]里放要引用的模块,是个数组,没有就放空

	//配置
	.config([

		'$interpolateProvider','$stateProvider','$urlRouterProvider',

		function(

		$interpolateProvider,
		$stateProvider,
		$urlRouterProvider

	){//$interpolateProvider 进行注入依赖,解析变量的符号,默认的{{ $name }} 会和laravel的产生冲突

		$interpolateProvider.startSymbol('[:');
		$interpolateProvider.endSymbol(':]');

		$urlRouterProvider.otherwise('/home');//url中没有的情况,默认显示的地址

		//前端路由
		$stateProvider
			.state('home',{
				url:'/home',//访问的url地址
				// template:'<h1>首页</h1>'//插入到模板的ui-view的div中
				
				//访问服务器的页面
				templateUrl:'home.tpl'//如果在当前模板中没有找到<script type="text/ng-template" id="home.tpl">,才会到服务器上找
			})
			.state('signup',{
				url:'/signup',
				// template:'<h1>注册</h1>'
				templateUrl:'signup.tpl'
				
			})			
			.state('login',{//"login" 是路由名称
				url:'/login',
				// template:'<h1>登录</h1>'
				templateUrl:'login.tpl'
				
			})			
			.state('question',{
				abstract:true,//抽象的路由,旨在不允许被访问的路由
				url:'/question',
				template:'<div ui-view></div>'				
				
			})				
			.state('question.add',{//添加提问路由,是上面的子路由
				url:'/add',
				templateUrl:'question.add.tpl'
				
			})					
	}])


	//服务
	.service('UserService',[
		'$state',
		'$http',//发送http的ajax请求
		function($state,$http){

		var me = this;

		me.signup_data = {};
		me.login_data = {};

		me.signup = function(){
			// console.log('signup');
			
			$http.post(
				'/api/signup',//请求的api路径
				//传参
				me.signup_data)
				.then(function(r){//成功返回
					if(r.data.status){
						me.signup_data = {};//数据用完后得清空
						$state.go('login');//跳转到登录页,"login" 是路由名称
					}
				},function(e){//失败返回
					// console.log('e',e);
				})
		}

		//登录
		me.login = function(){

			$http.post(
				'/api/login',
				me.login_data)
				.then(function(r){
					if(r.data.status){
						location.href = '/';//刷新页面
					}else{
						me.login_failed = true;
					}
				},function(e){

				})
		}

		//验证用户名是否已存在的ajax请求,需要调用后台的api接口
		me.username_exists = function(){

			$http.post(
				'/api/user/exist',//请求的api路径
				//传参
				{username:me.signup_data.username})
				.then(function(r){//成功返回
					// console.log('r',r);
					
					//第一个data：laravel制定的；第二个data：自己指定的
					if(r.data.status && r.data.data.count){

						//给赋值
						me.signup_username_exists = true;
					}else{
						me.signup_username_exists = false;
					}
				},function(e){//失败返回
					// console.log('e',e);
				})
		}
	}])

	.controller('SignupController',[

		'$scope',
		'UserService',
		function($scope,UserService){

			$scope.User = UserService;

			$scope.$watch(function(){

				return UserService.signup_data;
			},function(n,o){
				if(n.username != o.username){
					UserService.username_exists();
				}

			},true)
		}
	])

	.controller('LoginController',[

		'$scope',
		'UserService',
		function($scope,UserService){

			$scope.User = UserService;
		}
	])	

	//注入
	.service('QuestionService',[
		'$http',
		'$state',
		function($http,$state){
			var me = this;
			me.new_question = {};

			me.go_add_question = function(){
				$state.go('question.add');//是个前端路由
			}

			me.add = function(){

				if(!me.new_question.title){
					return;
				}

				$http.post(
					'/api/question/add',
					me.new_question)
					.then(function(r){
							if(r.data.status){
								me.new_question = {};
								$state.go('home');								
							}

					},function(e){

					})
			}			
		}
	])

	//提问
	.controller('QuestionAddController',[
		//注入参数
		'$scope',
		'QuestionService',
		function($scope,QuestionService){

			$scope.Question = QuestionService;
		}
	])		

})();