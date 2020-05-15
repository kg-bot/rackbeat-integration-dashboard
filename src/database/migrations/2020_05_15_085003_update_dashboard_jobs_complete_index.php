<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateDashboardJobsCompleteIndex extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table( 'dashboard_jobs', function ( Blueprint $table ) {
			$table->index( [ 'state', 'created_at', 'created_by' ] );
		} );
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table( 'dashboard_jobs', function ( Blueprint $table ) {
			$table->dropIndex( 'dashboard_jobs_state_created_at_created_by_index' );
		} );
	}
}
