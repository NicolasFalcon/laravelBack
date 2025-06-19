<?php

namespace App\Console\Commands;
use Illuminate\Support\Facades\Http;
use Illuminate\Console\Command;
use DB;
use Mail;

class TestCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:testcron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'cron notification';

    /**
     * Execute the console command.
     */
    public function handle()
     {

        $apiBaseUrl = config('app.base_api_url');
        $url = $apiBaseUrl . 'send_notification_payment';
        $response = Http::get($url);
        // Handle the response as needed
        $this->info('API call completed.');
    }
}
