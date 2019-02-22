<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobLogs extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create( 'job_logs', function ( Blueprint $table ) {

			$table->increments( 'id' );
			$table->nullableMorphs( 'loggable' );
			$table->string( 'level' );
			$table->text( 'message' );
			$table->text( 'context' );
			$table->text( 'extra' );
			$table->dateTime( 'created_at' );
		} );
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists( 'job_logs' );
	}
}
