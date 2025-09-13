<?php

namespace App\Traits;

trait ApiResponse
{
    /**
     * Réponse de succès
     */
    protected function success($data = null, string $message = 'Succès', int $code = 200, array $meta = [])
    {
        $response = [
            'status'  => true,
            'message' => $message,
            'data'    => $data,
        ];

        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $code);
    }

    /**
     * Réponse d'erreur
     */
    protected function error(string $message = 'Erreur', int $code = 400, $errors = [])
    {
        $response = [
            'status'  => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }
}
