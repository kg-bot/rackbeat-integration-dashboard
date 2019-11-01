<?php

namespace KgBot\RackbeatDashboard\Console\Commands;

use Illuminate\Console\Command;

class DeactivateDeadRackbeat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'integration:deactivate-rackbeat {--deactivate=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check or deactivate all integrations where Rackbeat API connection can\'t be established';

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
        $connections = $this->laravel['config']['rackbeat-integration-dashboard.connection_class']::Active()->get();
        $response = [];

        /** @var $this ->laravel['config']['rackbeat-integration-dashboard.connection_class'] $connection */
        foreach ($connections as $connection) {

            $failed = false;
            $error = '';
            if (!empty($connection->rackbeat_token)) {

                try {

                    $connection->createRackbeatClient()->self();
                } catch (\Exception $exception) {

                    $error = $exception->getMessage();
                    $failed = true;
                }
            } else {

                $error = 'Rackbeat API token is not present';
                $failed = true;
            }

            if ($failed) {

                $data = [

                    'Connection' => $connection->id,
                    'Rackbeat account' => $connection->rackbeat_user_account_id,
                    'Rackbeat API token' => $connection->rackbeat_token,
                    'Error' => $error,
                    'Deactivated' => false,
                ];
                if ((bool)$this->option('deactivate') === true) {

                    if (method_exists($connection, 'deactivate')) {

                        $connection->deactivate();
                        $data['Deactivated'] = true;
                    }
                }

                $response[] = $data;
            }
        }

        echo json_encode($response);
        echo 'Total un-active connections: ' . count($response);
    }
}
