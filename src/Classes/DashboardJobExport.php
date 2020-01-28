<?php


namespace KgBot\RackbeatDashboard\Classes;


use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use KgBot\RackbeatDashboard\Models\Job;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;

class DashboardJobExport implements FromCollection, ShouldQueue
{
    use Exportable;

    public function collection()
    {
        return Job::whereState('success')->whereDate('created_at', '<', Carbon::now()->subDays(7))->get();
    }
}