<?php

namespace App\Services\produit;

use OpenAI\Client;

class OpenAIService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = \OpenAI::client(config('services.openai.key'));
    }

    public function generateResponse(string $prompt): ?string
    {
        $response = $this->client->chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
        ]);

        return $response->choices[0]->message->content ?? null;
    }
}