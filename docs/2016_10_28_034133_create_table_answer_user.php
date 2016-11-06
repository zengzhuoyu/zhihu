<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableAnswerUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('answer_user', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('answer_id');
            $table->unsignedSmallInteger('vote');//只有两个值,故用这种数据类型
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');         
            $table->foreign('answer_id')->references('id')->on('answers');
            $table->unique(['user_id','answer_id','vote']);//在投票字段的行为上，三者的组合是唯一的         
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('answer_user');
    }
}
