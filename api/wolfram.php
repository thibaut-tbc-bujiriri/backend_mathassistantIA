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
    
    // Configuration WolframAlpha
    $appId = getenv('WOLFRAM_APP_ID') ?: '7YTHWGQL7L';
    
    if (empty($appId)) {
        sendJSONResponseHelper(false, 'Configuration WolframAlpha manquante. Veuillez configurer WOLFRAM_APP_ID.', null, 500);
        exit;
    }

    // Convertir LaTeX en texte lisible pour WolframAlpha (simplification basique)
    // WolframAlpha comprend généralement le LaTeX, mais on peut aussi envoyer du texte
    $query = urlencode($latex);
    
    $url = "https://api.wolframalpha.com/v2/query?input={$query}&appid={$appId}&output=json";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        sendJSONResponseHelper(false, 'Erreur lors de la communication avec WolframAlpha: ' . $curlError, null, 500);
        exit;
    }

    if ($httpCode !== 200) {
        sendJSONResponseHelper(false, 'Erreur WolframAlpha (HTTP ' . $httpCode . ')', null, $httpCode);
        exit;
    }

    $wolframData = json_decode($response, true);
    
    // Extraire la réponse de WolframAlpha
    $result = '';
    $pods = $wolframData['queryresult']['pods'] ?? [];
    
    foreach ($pods as $pod) {
        if (isset($pod['subpods']) && is_array($pod['subpods'])) {
            foreach ($pod['subpods'] as $subpod) {
                if (isset($subpod['plaintext']) && !empty($subpod['plaintext'])) {
                    $result .= $pod['title'] . ': ' . $subpod['plaintext'] . "\n";
                }
            }
        }
    }

    if (empty($result)) {
        $result = 'Aucune solution trouvée par WolframAlpha.';
    }

    sendJSONResponseHelper(true, 'Solution WolframAlpha obtenue.', [
        'solution' => trim($result),
        'raw_response' => $wolframData
    ]);

} catch (Exception $e) {
    error_log('WolframAlpha error: ' . $e->getMessage());
    sendJSONResponseHelper(false, 'Erreur serveur: ' . $e->getMessage(), null, 500);
}
?>

