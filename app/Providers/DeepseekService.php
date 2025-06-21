<?php

namespace App\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\ServiceProvider;

class DeepseekService extends ServiceProvider
{
    /**
     * Register services.
     */
    protected $client;
    protected $apiKey;
    public function __construct()
    {
        $this->apiKey = config('deepseek.api_key');
        $this->client = new Client([
            'base_uri' => config('deepseek.base_url'),
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'role'=>'user',
            ],
        ]);
    }
    public function chatCompletion(array $messages, string $model = 'deepseek/deepseek-prover-v2:free')
    {
        try {
            $response = $this->client->post('chat/completions', [
                'json' => [
                    'model' => $model,
                    'messages' => $messages,
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            // Handle exception or log error
            return ['error' => $e->getMessage()];
        }
    }
    public function register(): void
    {
        $this->app->singleton(DeepSeekService::class, function ($app) {
            return new DeepSeekService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
