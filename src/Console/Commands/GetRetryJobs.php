<?php

namespace KgBot\RackbeatDashboard\Console\Commands;

use Illuminate\Console\Command;
use KgBot\RackbeatDashboard\Models\Job;

class GetRetryJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rb-integration-dashboard:retry-jobs {limit=10} {connection?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all dashboard jobs in RETRY state.';

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
        $columns = [

            'queue',
            'state',
            'progress',
            'command',
            'attempts',
            'created_at',
            'finished_at',
            'title',
            'delay',
        ];

        if (!empty($this->argument('connection'))) {

            $jobs = Job::where('created_by', $this->argument('connection'))->whereState('retry')->limit($this->argument('limit'))->get($columns);
        } else {

            $jobs = Job::whereState('retry')->limit($this->argument('limit'))->get($columns);
        }

        $headers = [

            'queue',
            'state',
            'progress',
            'command',
            'attempts',
            'created_at',
            'finished_at',
            'title',
            'delay',
        ];

        $this->table($headers, $jobs);
    }
}
