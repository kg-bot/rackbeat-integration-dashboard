<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TableDashboardJobsArgs extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table( 'dashboard_jobs', function ( Blueprint $table ) {

			$table->text( 'args' )->nullable();
		} );
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table( 'dashboard_jobs', function ( Blueprint $table ) {

			$table->dropColumn( 'args' );
		} );
	}
}
