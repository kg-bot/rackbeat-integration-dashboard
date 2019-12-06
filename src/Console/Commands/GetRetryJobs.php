<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use KgBot\RackbeatDashboard\Models\Job;

class GetRetryJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rb-integration-dashboard:retry-jobs {limit=10} {connection-id?}';

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
        if ($this->hasArgument('connection-id')) {

            $jobs = Job::where('created_by', $this->argument('connection-id'))->limit($this->argument('limit'))->get()->toArray();
        } else {

            $jobs = Job::limit($this->argument('limit'))->get()->toArray();
        }

        $headers = [

            'queue',
            'state',
            'progress',
            'command',
            'attempts',
            'created_at',
            'finished_at',
            'args',
            'title',
            'delay',
        ];

        $this->table($headers, $jobs);
    }
}
