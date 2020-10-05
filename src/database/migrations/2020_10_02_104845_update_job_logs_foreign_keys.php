<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateJobLogsForeignKeys extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table( 'job_logs', function ( Blueprint $table ) {
			$table->foreign( 'job_id' )->references( 'id' )->on( 'dashboard_jobs' )->onDelete( 'cascade' );
		} );
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table( 'job_logs', function ( Blueprint $table ) {
			$table->dropForeign( 'job_logs_job_id_foreign' );
		} );
	}
}
