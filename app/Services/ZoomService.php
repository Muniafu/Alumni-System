<?php

namespace App\Services;

use GuzzleHttp\Client;

class ZoomService
{
    protected $client;
    protected $apiKey;
    protected $apiSecret;

    public function __construct()
    {
        $this->client = new Client(['base_uri' => 'https://api.zoom.us/v2/']);
        $this->apiKey = config('services.zoom.key');
        $this->apiSecret = config('services.zoom.secret');
    }

    public function createMeeting(array $data)
    {
        $response = $this->client->post('users/me/meetings', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->generateJWT(),
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'topic' => $data['topic'],
                'type' => 2, // Scheduled meeting
                'start_time' => $data['start_time'],
                'duration' => $data['duration'],
                'timezone' => config('app.timezone'),
                'password' => $data['password'] ?? substr(md5(time()), 0, 8),
                'settings' => [
                    'host_video' => true,
                    'participant_video' => true,
                    'join_before_host' => false,
                    'mute_upon_entry' => true,
                    'waiting_room' => true,
                ]
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    protected function generateJWT()
    {
        $payload = [
            'iss' => $this->apiKey,
            'exp' => time() + 60,
        ];

        return \Firebase\JWT\JWT::encode($payload, $this->apiSecret, 'HS256');
    }
}