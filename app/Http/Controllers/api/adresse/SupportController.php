<?php

namespace App\Http\Controllers\Api\adresse;

use App\Http\Controllers\Controller;
use App\Models\SupportChat;
use App\Models\SupportMessage;
use App\Services\produit\OpenAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SupportController extends Controller
{
    protected $openAI;

    public function __construct(OpenAIService $openAI)
    {
        $this->openAI = $openAI;
    }

    // Créer un nouveau chat
    public function createChat(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
        ]);

        $chat = SupportChat::create([
            'user_id' => Auth::id(),
            'subject' => $request->subject,
        ]);

        return response()->json($chat);
    }

    // Récupérer tous les chats d'un utilisateur
    public function getChats()
    {
        $chats = SupportChat::with('messages')->where('user_id', Auth::id())->get();
        return response()->json($chats);
    }

    // Envoyer un message
    public function sendMessage(Request $request, $chatId)
    {
        $request->validate([
            'text' => 'required|string',
        ]);

        $chat = SupportChat::findOrFail($chatId);

        try {
            // Message client
            $clientMessage = SupportMessage::create([
                'chat_id' => $chat->id,
                'type' => 'client',
                'text' => $request->text,
            ]);

            // Générer réponse IA
            $aiText = $this->openAI->generateResponse($request->text);

            $aiMessage = SupportMessage::create([
                'chat_id' => $chat->id,
                'type' => 'ai',
                'text' => $aiText ?? 'Erreur lors de la génération IA',
            ]);

            return response()->json([
                'client' => $clientMessage,
                'ai' => $aiMessage,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur SupportController sendMessage: ' . $e->getMessage());
            return response()->json([
                'error' => 'Impossible d’envoyer le message',
                'details' => $e->getMessage()
            ], 500);
        }
    }

}
