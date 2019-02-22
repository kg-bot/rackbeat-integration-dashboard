<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDashboardJobs extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create( 'dashboard_jobs', function ( Blueprint $table ) {
			$table->increments( 'id' );
			$table->string( 'queue' )->default( 'default' );
			$table->text( 'payload' )->nullable();
			$table->text( 'report' )->nullable();
			$table->string( 'state' );
			$table->integer( 'progress' )->nullable();
			$table->string( 'command' );
			$table->smallInteger( 'attempts' )->unsigned()->default( 0 );
			$table->integer( 'created_by' )->unsigned()->nullable();
			$table->dateTime( 'created_at' );
			$table->dateTime( 'finished_at' )->nullable();
		} );
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists( 'dashboard_jobs' );
	}
}
