<?php
/**
 * Fichier de configuration pour l'API
 * Contient les paramètres de configuration généraux
 */

// Configuration CORS pour permettre les requêtes depuis le frontend React
// Autoriser les origines spécifiées
$allowedOrigins = [
    'http://localhost:5173',  // Vite dev server (local)
    'http://localhost:3000',   // Autre serveur dev possible (local)
    'http://127.0.0.1:5173',
    'http://127.0.0.1:3000',
    'https://mathassistant-app-ia.vercel.app',  // Frontend Vercel (production)
    'https://mathassistant-app-ia.vercel.app/', // Avec slash
    '*' // Fallback pour le développement
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
if (in_array($origin, $allowedOrigins) || in_array('*', $allowedOrigins)) {
    header('Access-Control-Allow-Origin: ' . ($origin === '*' ? '*' : $origin));
}

header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, Accept, X-Requested-With');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Max-Age: 3600');
header('Content-Type: application/json; charset=utf-8');

// Gérer les requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_PORT', '3307');  // Port MySQL personnalisé (XAMPP)
define('DB_NAME', 'mathassistant_bd');
define('DB_USER', getenv('DB_USER') ?: 'tbc');
define('DB_PASS', getenv('DB_PASS') ?: 'YOUR_DB_PASSWORD_HERE');

// Fonction pour obtenir la connexion PDO
function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                )
            );
            
            // Définir le timezone
            date_default_timezone_set('Europe/Paris');
            
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erreur de connexion à la base de données',
                'error' => $e->getMessage()
            ]);
            exit();
        }
    }
    
    return $pdo;
}

// Fonction pour envoyer une réponse JSON
function sendJSONResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit();
}

// Fonction helper pour envoyer une réponse JSON standardisée
function sendJSONResponseHelper($success, $message, $data = null, $statusCode = 200) {
    $response = [
        'success' => $success,
        'message' => $message
    ];
    if ($data !== null) {
        $response['data'] = $data;
    }
    sendJSONResponse($response, $statusCode);
}

// Fonction pour gérer CORS
function handleCORS() {
    $allowedOrigins = [
        'http://localhost:5173',
        'http://localhost:3000',
        'http://127.0.0.1:5173',
        'http://127.0.0.1:3000',
        'https://mathassistant-app-ia.vercel.app',  // Frontend Vercel (production)
        'https://mathassistant-app-ia.vercel.app/', // Avec slash
        '*' // Fallback pour le développement
    ];
    
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
    if (in_array($origin, $allowedOrigins) || in_array('*', $allowedOrigins)) {
        header('Access-Control-Allow-Origin: ' . ($origin === '*' ? '*' : $origin));
    }
    
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, Accept, X-Requested-With');
    header('Access-Control-Allow-Credentials: true');
    
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
}

// Fonction pour obtenir les données JSON de la requête
function getJSONInput() {
    $json = file_get_contents('php://input');
    return json_decode($json, true);
}

// Configuration Gmail SMTP
// Charger la configuration depuis email_config.php si elle existe
$emailConfigFile = __DIR__ . '/email_config.php';
if (file_exists($emailConfigFile)) {
    $emailConfig = require $emailConfigFile;
    define('SMTP_HOST', $emailConfig['SMTP_HOST']);
    define('SMTP_PORT', $emailConfig['SMTP_PORT']);
    define('SMTP_USER', $emailConfig['SMTP_USER']);
    define('SMTP_PASS', $emailConfig['SMTP_PASS']);
    define('SMTP_FROM_EMAIL', $emailConfig['SMTP_FROM_EMAIL']);
    define('SMTP_FROM_NAME', $emailConfig['SMTP_FROM_NAME']);
} else {
    // Configuration par défaut (à modifier directement ici ou via email_config.php)
    define('SMTP_HOST', 'smtp.gmail.com');
    define('SMTP_PORT', 587);
    // IMPORTANT: Remplacez 'votre.email@gmail.com' par votre adresse Gmail réelle
    define('SMTP_USER', 'votre.email@gmail.com'); // À modifier avec votre adresse Gmail
    define('SMTP_PASS', 'xfwelhxgflvxvdcr'); // Mot de passe d'application Gmail
    define('SMTP_FROM_EMAIL', 'votre.email@gmail.com'); // Email expéditeur (doit être le même que SMTP_USER)
    define('SMTP_FROM_NAME', 'Math Assistant App'); // Nom de l'expéditeur
}

/**
 * Fonction pour envoyer un email via Gmail SMTP
 * @param string $to Adresse email du destinataire
 * @param string $subject Sujet de l'email
 * @param string $message Corps de l'email (HTML ou texte)
 * @param bool $isHTML Si true, le message est en HTML
 * @return bool True si l'email a été envoyé avec succès, False sinon
 */
function sendEmail($to, $subject, $message, $isHTML = true, &$errorDetails = null) {
    // Vérifier que l'email Gmail est configuré
    if (SMTP_USER === 'votre.email@gmail.com' || SMTP_USER === 'VOTRE_EMAIL@gmail.com' || 
        SMTP_FROM_EMAIL === 'votre.email@gmail.com' || SMTP_FROM_EMAIL === 'VOTRE_EMAIL@gmail.com') {
        $errorMsg = "ERREUR: L'adresse email Gmail n'est pas configurée. Veuillez modifier SMTP_USER et SMTP_FROM_EMAIL dans config.php ou utiliser setup_email.php";
        error_log($errorMsg);
        $errorDetails = $errorMsg;
        return false;
    }
    
    // Initialiser $errorDetails si ce n'est pas déjà fait
    if ($errorDetails === null) {
        $errorDetails = '';
    }
    
    // Essayer d'utiliser PHPMailer si disponible
    $phpmailerPath = __DIR__ . '/../vendor/autoload.php';
    if (file_exists($phpmailerPath)) {
        try {
            require_once $phpmailerPath;
            
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            
            // Configuration SMTP
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USER;
            $mail->Password = SMTP_PASS;
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = SMTP_PORT;
            $mail->CharSet = 'UTF-8';
            $mail->SMTPDebug = 0; // Désactiver le debug en production
            
            // Expéditeur et destinataire
            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress($to);
            $mail->addReplyTo(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            
            // Contenu
            $mail->isHTML($isHTML);
            $mail->Subject = $subject;
            $mail->Body = $message;
            
            // Envoyer
            $mail->send();
            return true;
            
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            $errorMsg = "Erreur PHPMailer: " . $mail->ErrorInfo;
            error_log($errorMsg);
            $errorDetails = $errorMsg;
            // Continuer avec la méthode SMTP native si PHPMailer échoue
        } catch (Exception $e) {
            $errorMsg = "Erreur PHPMailer: " . $e->getMessage();
            error_log($errorMsg);
            $errorDetails = $errorMsg;
            // Continuer avec la méthode SMTP native si PHPMailer échoue
        }
    }
    
    // Méthode SMTP native (fallback)
    try {
        // Créer une connexion socket au serveur SMTP
        $socket = @stream_socket_client(
            'tcp://' . SMTP_HOST . ':' . SMTP_PORT,
            $errno,
            $errstr,
            30
        );
        
        if (!$socket) {
            $errorMsg = "Impossible de se connecter à " . SMTP_HOST . ":" . SMTP_PORT . " - $errstr ($errno)";
            error_log("Erreur SMTP: " . $errorMsg);
            $errorDetails = $errorMsg;
            return false;
        }
        
        // Lire la réponse initiale du serveur
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) !== '220') {
            $errorMsg = "Réponse initiale invalide du serveur SMTP: " . trim($response);
            error_log("Erreur SMTP: " . $errorMsg);
            fclose($socket);
            $errorDetails = $errorMsg;
            return false;
        }
        
        // Envoyer EHLO
        fputs($socket, "EHLO " . SMTP_HOST . "\r\n");
        $response = '';
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') break;
        }
        
        // Activer TLS
        fputs($socket, "STARTTLS\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) !== '220') {
            $errorMsg = "STARTTLS échoué: " . trim($response);
            error_log("Erreur SMTP: " . $errorMsg);
            fclose($socket);
            $errorDetails = $errorMsg;
            return false;
        }
        
        // Activer le cryptage TLS
        $cryptoMethods = STREAM_CRYPTO_METHOD_TLS_CLIENT;
        // Essayer différentes versions de TLS
        if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
            $cryptoMethods |= STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
        }
        if (defined('STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT')) {
            $cryptoMethods |= STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT;
        }
        
        if (!@stream_socket_enable_crypto($socket, true, $cryptoMethods)) {
            $lastError = error_get_last();
            $errorMsg = "Échec du cryptage TLS. " . ($lastError ? $lastError['message'] : 'Erreur inconnue. Vérifiez que l\'extension OpenSSL est activée.');
            error_log("Erreur SMTP: " . $errorMsg);
            fclose($socket);
            $errorDetails = $errorMsg;
            return false;
        }
        
        // Envoyer EHLO à nouveau après TLS
        fputs($socket, "EHLO " . SMTP_HOST . "\r\n");
        $response = '';
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') break;
        }
        
        // Authentification
        fputs($socket, "AUTH LOGIN\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) !== '334') {
            $errorMsg = "AUTH LOGIN échoué: " . trim($response);
            error_log("Erreur SMTP: " . $errorMsg);
            fclose($socket);
            $errorDetails = $errorMsg;
            return false;
        }
        
        // Envoyer le nom d'utilisateur (base64)
        fputs($socket, base64_encode(SMTP_USER) . "\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) !== '334') {
            $errorMsg = "Authentification utilisateur échouée: " . trim($response);
            error_log("Erreur SMTP: " . $errorMsg);
            fclose($socket);
            $errorDetails = $errorMsg;
            return false;
        }
        
        // Envoyer le mot de passe (base64)
        // S'assurer que le mot de passe n'a pas d'espaces et est correctement formaté
        $cleanPassword = trim(str_replace(' ', '', SMTP_PASS));
        
        // Vérifier que le mot de passe n'est pas vide
        if (empty($cleanPassword)) {
            $errorMsg = "Le mot de passe d'application est vide";
            error_log("Erreur SMTP: " . $errorMsg);
            fclose($socket);
            $errorDetails = $errorMsg;
            return false;
        }
        
        fputs($socket, base64_encode($cleanPassword) . "\r\n");
        $response = fgets($socket, 515);
        
        if (substr($response, 0, 3) !== '235') {
            $errorMsg = "Authentification mot de passe échouée: " . trim($response) . " (Vérifiez que le mot de passe d'application est correct. Longueur: " . strlen($cleanPassword) . " caractères, User: " . SMTP_USER . ")";
            error_log("Erreur SMTP: " . $errorMsg);
            error_log("SMTP_USER utilisé: " . SMTP_USER);
            error_log("SMTP_PASS longueur: " . strlen($cleanPassword));
            fclose($socket);
            $errorDetails = $errorMsg;
            return false;
        }
        
        // Envoyer MAIL FROM
        fputs($socket, "MAIL FROM: <" . SMTP_FROM_EMAIL . ">\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) !== '250') {
            $errorMsg = "MAIL FROM échoué: " . trim($response);
            error_log("Erreur SMTP: " . $errorMsg);
            fclose($socket);
            $errorDetails = $errorMsg;
            return false;
        }
        
        // Envoyer RCPT TO
        fputs($socket, "RCPT TO: <" . $to . ">\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) !== '250') {
            $errorMsg = "RCPT TO échoué: " . trim($response);
            error_log("Erreur SMTP: " . $errorMsg);
            fclose($socket);
            $errorDetails = $errorMsg;
            return false;
        }
        
        // Envoyer DATA
        fputs($socket, "DATA\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) !== '354') {
            $errorMsg = "DATA échoué: " . trim($response);
            error_log("Erreur SMTP: " . $errorMsg);
            fclose($socket);
            $errorDetails = $errorMsg;
            return false;
        }
        
        // Construire les en-têtes de l'email
        $headers = "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">\r\n";
        $headers .= "Reply-To: " . SMTP_FROM_EMAIL . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        if ($isHTML) {
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        } else {
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        }
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        
        // Envoyer les en-têtes et le message
        fputs($socket, "Subject: " . $subject . "\r\n");
        fputs($socket, $headers);
        fputs($socket, "\r\n");
        fputs($socket, $message . "\r\n");
        fputs($socket, ".\r\n");
        
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) !== '250') {
            $errorMsg = "Envoi du message échoué: " . trim($response);
            error_log("Erreur SMTP: " . $errorMsg);
            fclose($socket);
            $errorDetails = $errorMsg;
            return false;
        }
        
        // Quitter
        fputs($socket, "QUIT\r\n");
        fclose($socket);
        
        return true;
        
    } catch (Exception $e) {
        $errorMsg = "Exception: " . $e->getMessage();
        error_log("Erreur lors de l'envoi de l'email: " . $errorMsg);
        $errorDetails = $errorMsg;
        return false;
    }
}

