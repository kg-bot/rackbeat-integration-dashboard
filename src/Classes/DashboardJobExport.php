<?php


namespace KgBot\RackbeatDashboard\Classes;


use Carbon\Carbon;
use KgBot\RackbeatDashboard\Models\Job;

class DashboardJobExport extends DashboardJob
{

    public function execute()
    {
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

        Job::whereState('success')->whereDate('created_at', '<', Carbon::now()->subDays(7))->chunk(5000, function ($jobs) use ($handle) {

            foreach ($jobs as $job) {
                fputcsv($handle, [

                    $job->id,
                    $job->command,
                    $job->created_by,
                    $job->created_at,
                    $job->finished_at,
                    $job->title,
                    ($job->owner !== null) ? ($job->owner->rackbeat_user_account_id ?? '') : '',
                ]);
            }
        });

        fclose($handle);

        Job::whereState('success')->whereDate('created_at', '<', Carbon::now()->subDays(7))->delete();
    }

    public function fail($exception = null)
    {
        \Log::error($exception->getMessage());
    }
}