<?php

namespace KgBot\RackbeatDashboard\Console\Commands;

use Illuminate\Console\Command;

class InstallMissingPluginUninstallWebhooks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rb-integration-dashboard:fix-plugin-uninstall-webhooks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register missing plugin uninstall webhooks on Rackbeat';

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

        /** @var $this ->laravel['config']['rackbeat-integration-dashboard.connection_class'] $connection */
        foreach ($connections as $connection) {

            if (!empty($connection->rackbeat_token)) {

                try {

                    $rb = $connection->createRackbeatClient();

                    $webhook_exist = $rb->rb->webhooks()->get([
                        ['event', '=', 'plugin.uninstalled'],
                    ]);

                    if (!count($webhook_exist)) {

                        $rb->setupWebhook('plugin.uninstalled', route('webhooks.rackbeat.plugin.uninstall', [$connection->rackbeat_user_account_id, $connection->internal_token]));
                    }
                } catch (\Exception $exception) {

                    $error = $exception->getMessage();
                    $connection->logFail($exception);
                    $connection->logError($exception->getMessage());
                }
            }
        }
    }
}
