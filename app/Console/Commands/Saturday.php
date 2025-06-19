<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
class Saturday extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'saturday:command';

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
            'user_id'=>'saturday'
            ]);
            $apiBaseUrl = config('app.base_api_url');
            $url = $apiBaseUrl . 'send_mail_winner';
        
         $response = Http::get($url);

        if ($response->successful()) {
            $this->info('API request was successful');
        } else {
            $this->error('API request failed: ' . $response->body());
        }
        return 0;
    }
}
