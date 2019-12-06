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
    protected $signature = 'rb-integration-dashboard:retry-jobs {limit=10} {order-by=created_at} {sort-dir=desc} {connection?}';

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

            'id',
            'queue',
            'state',
            'progress',
            'command',
            'attempts',
            'created_at',
            'finished_at',
            'title',
            'delay',
            'created_by',
        ];

        $query = Job::query()->where('state', 'retry')->orderBy($this->argument('order-by'), $this->argument('sort-dir'))->limit($this->argument('limit'));

        if (!empty($this->argument('connection'))) {

            $query->where('created_by', $this->argument('connection'));
        }

        $jobs = $query->get($columns);

        $headers = [

            'id',
            'queue',
            'state',
            'progress',
            'command',
            'attempts',
            'created_at',
            'finished_at',
            'title',
            'delay',
            'created_by',
        ];

        $this->table($headers, $jobs);
    }
}
