<?php

namespace App\Console\Commands;
use Illuminate\Support\Facades\Http;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
class Sunday extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sunday:command';

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
        
        // DB::table('wallet')->insert([
        //     'user_id'=>'sunday'
        //     ]);
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
