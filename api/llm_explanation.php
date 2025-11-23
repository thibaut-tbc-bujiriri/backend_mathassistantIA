<?php
require_once 'config.php';

handleCORS();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSONResponseHelper(false, 'Méthode non autorisée. Utilisez POST.', null, 405);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['latex']) || empty($input['latex'])) {
        sendJSONResponseHelper(false, 'LaTeX manquant dans la requête.', null, 400);
        exit;
    }

    $latex = $input['latex'];
    $wolframSolution = $input['wolfram_solution'] ?? '';
    
    // Configuration LLM - Support pour Gemini et OpenAI
    $llmProvider = getenv('LLM_PROVIDER') ?: 'openai'; // 'gemini' ou 'openai' - OpenAI par défaut
    
    $prompt = "Tu es un professeur de mathématiques expert. Ta mission est de synthétiser une explication humaine et pédagogique pour résoudre cette équation mathématique.\n\n";
    $prompt .= "Équation LaTeX: {$latex}\n\n";
    
    if (!empty($wolframSolution)) {
        $prompt .= "SOLUTION EXACTE DE WOLFRAMALPHA (utilise cette solution comme référence absolue pour l'exactitude):\n{$wolframSolution}\n\n";
        $prompt .= "IMPORTANT: La solution ci-dessus provient de WolframAlpha et est mathématiquement exacte. ";
        $prompt .= "Tu dois créer une explication pédagogique étape par étape qui:\n";
        $prompt .= "1. S'appuie sur l'exactitude de cette solution WolframAlpha\n";
        $prompt .= "2. Explique de manière claire et humaine comment arriver à cette solution\n";
        $prompt .= "3. Détaille chaque étape du raisonnement mathématique\n";
        $prompt .= "4. Utilise un langage accessible et pédagogique\n";
        $prompt .= "5. Montre le cheminement logique vers la solution exacte fournie\n\n";
    } else {
        $prompt .= "Fournis une explication détaillée étape par étape en français. ";
    }
    
    $prompt .= "Structure ta réponse avec des étapes numérotées clairement. Utilise le format LaTeX pour toutes les équations mathématiques. ";
    $prompt .= "Sois précis, pédagogique et assure-toi que ton explication mène exactement à la solution fournie.";

    if ($llmProvider === 'openai' || $llmProvider === '') {
        // OpenAI API
        $apiKey = getenv('OPENAI_API_KEY') ?: '';
        
        if (empty($apiKey)) {
            sendJSONResponseHelper(false, 'Configuration OpenAI manquante. Veuillez configurer OPENAI_API_KEY.', null, 500);
            exit;
        }

        $url = 'https://api.openai.com/v1/chat/completions';
        
        $data = [
            'model' => 'gpt-4',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Tu es un professeur de mathématiques expert qui synthétise des explications pédagogiques et humaines en t\'appuyant sur des solutions mathématiques exactes. Tu expliques les problèmes étape par étape de manière claire et accessible.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.5, // Température réduite pour plus de précision et d'alignement avec WolframAlpha
            'max_tokens' => 2000
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            sendJSONResponseHelper(false, 'Erreur lors de la communication avec OpenAI: ' . $curlError, null, 500);
            exit;
        }

        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMsg = $errorData['error']['message'] ?? 'Erreur inconnue d\'OpenAI';
            sendJSONResponseHelper(false, 'Erreur OpenAI: ' . $errorMsg, null, $httpCode);
            exit;
        }

        $openaiResponse = json_decode($response, true);
        $explanation = $openaiResponse['choices'][0]['message']['content'] ?? '';

    } else {
        // Gemini API
        $apiKey = getenv('GEMINI_API_KEY') ?: '';
        
        if (empty($apiKey)) {
            sendJSONResponseHelper(false, 'Configuration Gemini manquante. Veuillez configurer GEMINI_API_KEY.', null, 500);
            exit;
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key={$apiKey}";
        
        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.5, // Température réduite pour plus de précision et d'alignement avec WolframAlpha
                'maxOutputTokens' => 2000
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            sendJSONResponseHelper(false, 'Erreur lors de la communication avec Gemini: ' . $curlError, null, 500);
            exit;
        }

        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMsg = $errorData['error']['message'] ?? 'Erreur inconnue de Gemini';
            sendJSONResponseHelper(false, 'Erreur Gemini: ' . $errorMsg, null, $httpCode);
            exit;
        }

        $geminiResponse = json_decode($response, true);
        $explanation = $geminiResponse['candidates'][0]['content']['parts'][0]['text'] ?? '';
    }

    if (empty($explanation)) {
        sendJSONResponseHelper(false, 'Aucune explication générée par le LLM.', null, 500);
        exit;
    }

    sendJSONResponseHelper(true, 'Explication générée avec succès.', [
        'explanation' => $explanation
    ]);

} catch (Exception $e) {
    error_log('LLM error: ' . $e->getMessage());
    sendJSONResponseHelper(false, 'Erreur serveur: ' . $e->getMessage(), null, 500);
}
?>


