<?php
require_once 'config.php';

handleCORS();

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

    // Lire le contenu de l'image en base64
    $imageData = file_get_contents($imageFile);
    $base64Image = base64_encode($imageData);

    // Configuration OpenAI Vision API
    $apiKey = getenv('OPENAI_API_KEY') ?: '';
    
    if (empty($apiKey)) {
        sendJSONResponseHelper(false, 'Configuration OpenAI manquante. Veuillez configurer OPENAI_API_KEY.', null, 500);
        exit;
    }

    // Préparer la requête pour OpenAI Vision
    $url = 'https://api.openai.com/v1/chat/completions';
    
    // Déterminer le format MIME pour l'URL data
    $mimeType = $imageType;
    
    $data = [
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
                            'url' => 'data:' . $mimeType . ';base64,' . $base64Image
                        ]
                    ]
                ]
            ]
        ],
        'max_tokens' => 500,
        'temperature' => 0.1
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
    $latex = $openaiResponse['choices'][0]['message']['content'] ?? '';
    
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

    sendJSONResponseHelper(true, 'Image convertie en LaTeX avec succès.', [
        'latex' => $latex,
        'raw_response' => $openaiResponse
    ]);

} catch (Exception $e) {
    error_log('OpenAI Vision error: ' . $e->getMessage());
    sendJSONResponseHelper(false, 'Erreur serveur: ' . $e->getMessage(), null, 500);
}
?>


