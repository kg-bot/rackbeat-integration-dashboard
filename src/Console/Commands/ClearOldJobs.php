<?php

namespace KgBot\RackbeatDashboard\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use KgBot\RackbeatDashboard\Classes\DashboardJobExport;
use KgBot\RackbeatDashboard\Models\Job;
use Maatwebsite\Excel\Excel;

class ClearOldJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rb-integration-dashboard:clear-old-jobs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export old jobs and delete them from database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        (new DashboardJobExport())->store('jobs-' . Carbon::now()->toDateString() . '.csv', 'local', Excel::CSV)->allOnQueue(Config::get('queue.connections.redis.queue'));

        Job::whereState('success')->whereDate('created_at', '<', Carbon::now()->subDays(7))->delete();

        return true;
    }
}
