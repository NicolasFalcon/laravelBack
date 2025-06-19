<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\View;

class MailchimpTransactionalService
{
    protected $client;
    protected $apiKey;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = env('MAILCHIMP_TRANSACTIONAL_APIKEY');
    }

    public function sendEmail($to, $subject, $view, $data = [])
    {
        $apiKey = env('MAILCHIMP_TRANSACTIONAL_APIKEY');
        // Render the view into HTML
        $htmlContent = View::make($view, $data)->render();

        $response = $this->client->post('https://mandrillapp.com/api/1.0/messages/send.json', [
            'json' => [
                'key' => $apiKey,
                'message' => [
                    'html' => $htmlContent,
                    'subject' => $subject,
                    'from_email' => '',
                    'to' => [
                        [
                            'email' => $to,
                            'type' => 'to'
                        ]
                    ]
                ]
            ]
        ]);

        return json_decode($response->getBody(), true);
    }
}
