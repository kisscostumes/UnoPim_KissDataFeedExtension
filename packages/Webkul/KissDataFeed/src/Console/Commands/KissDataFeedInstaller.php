<?php

namespace Webkul\KissDataFeed\Console\Commands;

use Illuminate\Console\Command;

class KissDataFeedInstaller extends Command
{
    protected $signature = 'kiss-datafeed:install';

    protected $description = 'Install the Kiss DataFeed package';

    public function handle()
    {
        $this->info('Installing Kiss DataFeed extension...');

        if ($this->confirm('Would you like to run the migrations now?', true)) {
            $this->call('migrate');
            $this->call('db:seed', ['--class' => 'Webkul\KissDataFeed\Database\Seeders\KissDataFeedSeeder']);
        }

        $this->info('Kiss DataFeed extension installed successfully!');
    }
}
