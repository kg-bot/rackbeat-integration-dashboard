<?php

namespace KgBot\RackbeatDashboard\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
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

        \Log::debug('Running clear job');
        $handle = fopen(storage_path('app/jobs-' . Carbon::now()->toDateString() . '.csv'), 'w');

        fputcsv($handle, [
            'id',
            'command',
            'created_by',
            'created_at',
            'finished_at',
            'title',
            'rackbeat_account',
        ]);
        
        Job::whereDate('created_at', '<', Carbon::now()->subDays(7))->with('owner')->each(function ($job) use ($handle) {
            fputcsv($handle, [

                $job->id,
                $job->command,
                $job->created_by,
                $job->created_at,
                $job->finished_at,
                $job->title,
                ($job->owner !== null) ? ($job->owner->rackbeat_user_account_id ?? '') : '',
            ]);
        });

        fclose($handle);
      
        Job::whereDate('created_at', '<', Carbon::now()->subDays(7))->delete();

        \Log::debug('Jobs exported and deleted');
    }
}
