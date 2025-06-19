<?php

namespace App\Services;

use GuzzleHttp\Client;

class MailchimpService
{
    protected $client;
    protected $apiKey;
    protected $listId;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://<dc>.api.mailchimp.com/3.0/',
            'auth' => ['apikey', config('services.mailchimp.api_key')],
        ]);

        $this->listId = config('services.mailchimp.list_id');
    }

    public function addSubscriber($email)
    {
        $response = $this->client->post("lists/{$this->listId}/members", [
            'json' => [
                'email_address' => $email,
                'status' => 'subscribed',
            ],
        ]);

        return $response->getStatusCode();
    }
}
