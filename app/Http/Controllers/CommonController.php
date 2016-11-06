<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

class CommonController extends Controller
{
    public function timeline(){

            list($limit,$skip) = paginate(rq('page'),rq('limit'));

            //获取问题数据
            $questions = question_ins()
                  -> limit($limit)
                  -> skip($skip)
                  -> orderBy('created_at','desc')
                  -> get()
                  -> toArray();     //bug                              

            //获取回答数据
            $answers = answer_ins()
                  -> limit($limit)
                  -> skip($skip)
                  -> orderBy('created_at','desc')
                  -> get()
                  -> toArray();    //bug                          	

            //合并以上两者数据
            // $data = $questions -> merge($answers);
            $data = array_merge($questions,$answers);//bug

            //将合并的数据按时间排序
            // $data = $data -> sortByDesc(function($item){
            // 	return $item -> created_at;
            // });

            // $data = $data -> values() -> all();

            //按创建时间排序
            $data = array_sort($data,function ($item){
                  return $item['created_at'];
            });

            return suc(['data' => $data]);
    }
}
