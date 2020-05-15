<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateDashboardJobsCreatedByIndex extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table( 'dashboard_jobs', function ( Blueprint $table ) {
			$table->index( 'created_by' );
		} );
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table( 'dashboard_jobs', function ( Blueprint $table ) {
			$table->dropIndex( 'dashboard_jobs_created_by_index' );
		} );
	}
}
