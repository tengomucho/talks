<?php

use Illuminate\Database\Migrations\Migration;

class CreateComments extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('comments', function($table) {
			$table->increments('id');
			$table->text('content'); 
			$table->integer('user_id')->unsigned();
			$table->foreign('user_id')->references('id')->on('users');
			$table->integer('session_id')->unsigned();
			$table->foreign('session_id')->references('id')->on('sessions');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('comments');
	}

}
