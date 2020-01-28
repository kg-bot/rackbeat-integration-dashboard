<?php


namespace KgBot\RackbeatDashboard\Classes;


use Carbon\Carbon;
use KgBot\RackbeatDashboard\Models\Job;

class DashboardJobExport extends DashboardJob
{

    public function execute()
    {
        \Log::debug('DashboardJob started');
        $handle = fopen(storage_path('app/jobs-' . Carbon::now()->toDateString() . '.csv'), 'w');

        fputcsv($handle, [
            'id',
            'command',
            'created_by',
            'created_at',
            'finished_at',
            'title',
        ]);

        Job::whereState('success')->whereDate('created_at', '<', Carbon::now()->subDays(7))->chunk(5000, function ($jobs) use ($handle) {

            \Log::debug('Chunk fetched');
            foreach ($jobs as $job) {
                fputcsv($handle, [

                    $job->id,
                    $job->command,
                    $job->created_by,
                    $job->created_at,
                    $job->finished_at,
                    $job->title,
                ]);
            }
        });

        fclose($handle);
    }
}