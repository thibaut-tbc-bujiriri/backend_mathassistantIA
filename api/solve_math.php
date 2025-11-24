<?php
// Désactiver l'affichage des erreurs pour éviter les warnings dans le JSON
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Headers CORS
header("Access-Control-Allow-Origin: https://mathassistant-app-ia.vercel.app");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json; charset=utf-8");

// Gérer les requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSONResponseHelper(false, 'Méthode non autorisée. Utilisez POST.', null, 405);
    exit;
}

try {
    // Vérifier si un fichier a été uploadé
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        sendJSONResponseHelper(false, 'Aucune image fournie ou erreur lors de l\'upload.', null, 400);
        exit;
    }

    $imageFile = $_FILES['image']['tmp_name'];
    $imageType = $_FILES['image']['type'];
    
    // Vérifier le type de fichier
    if (!in_array($imageType, ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'])) {
        sendJSONResponseHelper(false, 'Type de fichier non supporté. Utilisez JPEG, PNG, GIF ou WebP.', null, 400);
        exit;
    }

    // Étape 1: Convertir l'image en LaTeX avec OpenAI Vision
    $imageData = file_get_contents($imageFile);
    $base64Image = base64_encode($imageData);

    $openaiKey = getenv('OPENAI_API_KEY') ?: '';
    
    if (empty($openaiKey)) {
        sendJSONResponseHelper(false, 'Configuration OpenAI manquante. Veuillez configurer OPENAI_API_KEY.', null, 500);
        exit;
    }

    $openaiUrl = 'https://api.openai.com/v1/chat/completions';
    $openaiData = [
        'model' => 'gpt-4o', // ou 'gpt-4-vision-preview' selon disponibilité
        'messages' => [
            [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => 'Extract the mathematical equation from this image and convert it to LaTeX format. Return ONLY the LaTeX code, nothing else. If there are multiple equations, return them separated by newlines. Use standard LaTeX notation.'
                    ],
                    [
                        'type' => 'image_url',
                        'image_url' => [
                            'url' => 'data:' . $imageType . ';base64,' . $base64Image
                        ]
                    ]
                ]
            ]
        ],
        'max_tokens' => 500,
        'temperature' => 0.1
    ];

    $ch = curl_init($openaiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($openaiData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $openaiKey
    ]);

    $openaiResponse = curl_exec($ch);
    $openaiHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        sendJSONResponseHelper(false, 'Erreur lors de la communication avec OpenAI: ' . $curlError, null, 500);
        exit;
    }

    if ($openaiHttpCode !== 200) {
        $errorData = json_decode($openaiResponse, true);
        $errorMsg = $errorData['error']['message'] ?? 'Erreur lors de la conversion de l\'image en LaTeX.';
        sendJSONResponseHelper(false, 'Erreur OpenAI: ' . $errorMsg, null, $openaiHttpCode);
        exit;
    }

    $openaiData = json_decode($openaiResponse, true);
    $latex = $openaiData['choices'][0]['message']['content'] ?? '';
    
    // Nettoyer le LaTeX (enlever les markdown code blocks si présents)
    $latex = trim($latex);
    $latex = preg_replace('/^```latex\s*/', '', $latex);
    $latex = preg_replace('/^```\s*/', '', $latex);
    $latex = preg_replace('/\s*```$/', '', $latex);
    $latex = trim($latex);

    if (empty($latex)) {
        sendJSONResponseHelper(false, 'Aucune équation LaTeX trouvée dans l\'image.', null, 400);
        exit;
    }

    // Étape 2: Obtenir la solution de WolframAlpha
    $wolframSolution = '';
    $wolframAppId = getenv('WOLFRAM_APP_ID') ?: '7YTHWGQL7L';
    
    if (!empty($wolframAppId)) {
        $query = urlencode($latex);
        $wolframUrl = "https://api.wolframalpha.com/v2/query?input={$query}&appid={$wolframAppId}&output=json";
        
        $ch = curl_init($wolframUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $wolframResponse = curl_exec($ch);
        $wolframHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($wolframHttpCode === 200) {
            $wolframData = json_decode($wolframResponse, true);
            $pods = $wolframData['queryresult']['pods'] ?? [];
            
            foreach ($pods as $pod) {
                if (isset($pod['subpods']) && is_array($pod['subpods'])) {
                    foreach ($pod['subpods'] as $subpod) {
                        if (isset($subpod['plaintext']) && !empty($subpod['plaintext'])) {
                            $wolframSolution .= $pod['title'] . ': ' . $subpod['plaintext'] . "\n";
                        }
                    }
                }
            }
            $wolframSolution = trim($wolframSolution);
        }
    }

    // Étape 3: Obtenir l'explication du LLM
    $explanation = '';
    $llmProvider = getenv('LLM_PROVIDER') ?: 'openai'; // Utilise OpenAI par défaut
    
    $llmPrompt = "Tu es un professeur de mathématiques expert. Ta mission est de synthétiser une explication humaine et pédagogique pour résoudre cette équation mathématique.\n\n";
    $llmPrompt .= "Équation LaTeX: {$latex}\n\n";
    
    if (!empty($wolframSolution)) {
        $llmPrompt .= "SOLUTION EXACTE DE WOLFRAMALPHA (utilise cette solution comme référence absolue pour l'exactitude):\n{$wolframSolution}\n\n";
        $llmPrompt .= "IMPORTANT: La solution ci-dessus provient de WolframAlpha et est mathématiquement exacte. ";
        $llmPrompt .= "Tu dois créer une explication pédagogique étape par étape qui:\n";
        $llmPrompt .= "1. S'appuie sur l'exactitude de cette solution WolframAlpha\n";
        $llmPrompt .= "2. Explique de manière claire et humaine comment arriver à cette solution\n";
        $llmPrompt .= "3. Détaille chaque étape du raisonnement mathématique\n";
        $llmPrompt .= "4. Utilise un langage accessible et pédagogique\n";
        $llmPrompt .= "5. Montre le cheminement logique vers la solution exacte fournie\n\n";
    } else {
        $llmPrompt .= "Fournis une explication détaillée étape par étape en français. ";
    }
    
    $llmPrompt .= "Structure ta réponse avec des étapes numérotées clairement. Utilise le format LaTeX pour toutes les équations mathématiques. ";
    $llmPrompt .= "Sois précis, pédagogique et assure-toi que ton explication mène exactement à la solution fournie.";

    if ($llmProvider === 'openai' || $llmProvider === '') {
        $llmOpenaiKey = getenv('OPENAI_API_KEY') ?: '';
        
        if (!empty($llmOpenaiKey)) {
            $openaiUrl = 'https://api.openai.com/v1/chat/completions';
            
            $openaiData = [
                'model' => 'gpt-4',
                'messages' => [
                    ['role' => 'system', 'content' => 'Tu es un professeur de mathématiques expert qui synthétise des explications pédagogiques et humaines en t\'appuyant sur des solutions mathématiques exactes. Tu expliques les problèmes étape par étape de manière claire et accessible.'],
                    ['role' => 'user', 'content' => $llmPrompt]
                ],
                'temperature' => 0.5, // Température réduite pour plus de précision et d'alignement avec WolframAlpha
                'max_tokens' => 2000
            ];

            $ch = curl_init($openaiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($openaiData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $openaiKey
            ]);

            $openaiResponse = curl_exec($ch);
            $openaiHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($openaiHttpCode === 200) {
                $openaiData = json_decode($openaiResponse, true);
                $explanation = $openaiData['choices'][0]['message']['content'] ?? '';
            }
        }
    } else {
        $geminiKey = getenv('GEMINI_API_KEY') ?: '';
        
        if (!empty($geminiKey)) {
            $geminiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key={$geminiKey}";
            
            $geminiData = [
                'contents' => [['parts' => [['text' => $llmPrompt]]]],
                'generationConfig' => ['temperature' => 0.5, 'maxOutputTokens' => 2000] // Température réduite pour plus de précision et d'alignement avec WolframAlpha
            ];

            $ch = curl_init($geminiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($geminiData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

            $geminiResponse = curl_exec($ch);
            $geminiHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($geminiHttpCode === 200) {
                $geminiData = json_decode($geminiResponse, true);
                $explanation = $geminiData['candidates'][0]['content']['parts'][0]['text'] ?? '';
            }
        }
    }

    // Retourner la réponse complète
    sendJSONResponseHelper(true, 'Problème résolu avec succès.', [
        'latex' => $latex,
        'wolfram_solution' => $wolframSolution,
        'explanation' => $explanation,
        'steps' => parseExplanationIntoSteps($explanation)
    ]);

} catch (Exception $e) {
    error_log('Solve math error: ' . $e->getMessage());
    sendJSONResponseHelper(false, 'Erreur serveur: ' . $e->getMessage(), null, 500);
}

// Fonction pour parser l'explication en étapes
function parseExplanationIntoSteps($explanation) {
    if (empty($explanation)) {
        return [];
    }
    
    // Diviser par les numéros d'étapes ou les lignes vides
    $lines = explode("\n", $explanation);
    $steps = [];
    $currentStep = '';
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) {
            if (!empty($currentStep)) {
                $steps[] = $currentStep;
                $currentStep = '';
            }
            continue;
        }
        
        // Détecter le début d'une nouvelle étape (numéro, tiret, etc.)
        if (preg_match('/^(Étape\s+\d+|Step\s+\d+|\d+[\.\)]\s+|[-•]\s+)/i', $line)) {
            if (!empty($currentStep)) {
                $steps[] = $currentStep;
            }
            $currentStep = $line;
        } else {
            $currentStep .= ($currentStep ? "\n" : '') . $line;
        }
    }
    
    if (!empty($currentStep)) {
        $steps[] = $currentStep;
    }
    
    // Si aucune étape n'a été détectée, retourner l'explication complète comme une seule étape
    if (empty($steps)) {
        $steps = [$explanation];
    }
    
    return $steps;
}
?>


