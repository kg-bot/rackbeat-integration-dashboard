<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateJobLogsExtraAsJson extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table( 'job_logs', function ( Blueprint $table ) {
			$table->json( 'extra' )->nullable()->change();
		} );
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table( 'job_logs', function ( Blueprint $table ) {
			$table->text( 'extra' )->nullable()->change();
		} );
	}
}
