<?php

namespace KgBot\RackbeatDashboard\Console\Commands;

use Illuminate\Console\Command;
use KgBot\RackbeatDashboard\Classes\DashboardJobExport;
use KgBot\RackbeatDashboard\Models\Job;

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

        Job::create([

            'command' => DashboardJobExport::class,
            'args' => [],
            'queue' => config('queue.connections.redis.queue'),
            'title' => 'Export old jobs',
        ]);
    }
}
