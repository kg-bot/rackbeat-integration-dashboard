<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateJobLogsOnlyLogJobs extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table( 'job_logs', function ( Blueprint $table ) {
			$table->dropMorphs( 'loggable' );
			$table->unsignedInteger( 'job_id' );
		} );
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table( 'job_logs', function ( Blueprint $table ) {
			$table->dropColumn( 'job_id' );
			$table->nullableMorphs( 'loggable' );
		} );
	}
}
