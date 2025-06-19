<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
class Monday extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monday:command';

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
          DB::table('wallet')->insert([
            'user_id'=>'monday_IN'
            ]);

            $apiBaseUrl = config('app.base_api_url');
            $url = $apiBaseUrl . 'send_push_notification_monday';
        
         $response = Http::get($url);

        if ($response->successful()) {
            $this->info('API request was successful');
        } else {
            $this->error('API request failed: ' . $response->body());
        }
        return 0;
    }
}
