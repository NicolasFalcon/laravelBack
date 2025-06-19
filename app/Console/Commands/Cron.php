<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Cron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()


    
    {
        $apiBaseUrl = config('app.base_api_url');
        $url = $apiBaseUrl . 'send_push_notification_sunday';
          $response = Http::post($url);

        if ($response->successful()) {
            $this->info('API request was successful');
        } else {
            $this->error('API request failed: ' . $response->body());
        }

        return 0;
    }
}
