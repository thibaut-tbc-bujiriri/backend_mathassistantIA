<?php
/**
 * Endpoint API pour demander la réinitialisation de mot de passe
 * Méthode: POST
 * URL: http://localhost:8080/Math_AssistantApp/api/forgot_password.php
 * 
 * Paramètres JSON attendus:
 * {
 *   "email": "email@example.com"
 * }
 */

require_once 'config.php';

// Vérifier que la méthode est POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSONResponse([
        'success' => false,
        'message' => 'Méthode non autorisée. Utilisez POST.'
    ], 405);
}

try {
    // Récupérer les données JSON
    $input = getJSONInput();
    
    // Vérifier si le JSON est valide
    if ($input === null || json_last_error() !== JSON_ERROR_NONE) {
        sendJSONResponse([
            'success' => false,
            'message' => 'Données JSON invalides'
        ], 400);
    }
    
    // Valider les données
    if (empty($input['email'])) {
        sendJSONResponse([
            'success' => false,
            'message' => 'Email requis'
        ], 400);
    }
    
    $email = trim($input['email']);
    
    // Valider l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendJSONResponse([
            'success' => false,
            'message' => 'Format d\'email invalide'
        ], 400);
    }
    
    // Obtenir la connexion à la base de données
    $pdo = getDBConnection();
    
    // Vérifier si l'utilisateur existe dans la base de données
    $stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    // Vérifier que l'utilisateur existe avant de continuer
    if (!$user) {
        sendJSONResponse([
            'success' => false,
            'message' => 'Cette adresse email n\'existe pas dans notre base de données. Veuillez vérifier votre adresse email ou créer un compte.'
        ], 404);
    }
    
    // Générer un code de réinitialisation (6 chiffres)
    $resetCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    
    // Créer un token sécurisé
    $token = bin2hex(random_bytes(32));
    
    // Supprimer les anciens tokens non utilisés pour cet utilisateur
    $stmt = $pdo->prepare("DELETE FROM password_reset_tokens WHERE user_id = ? AND used = FALSE AND expires_at > NOW()");
    $stmt->execute([$user['id']]);
    
    // Insérer le nouveau token avec le code (utiliser DATE_ADD de MySQL pour éviter les problèmes de timezone)
    $stmt = $pdo->prepare("INSERT INTO password_reset_tokens (user_id, token, reset_code, expires_at) VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 15 MINUTE))");
    $stmt->execute([$user['id'], $token, $resetCode]);
    
    // Préparer le message email avec design HTML professionnel
    $subject = "Réinitialisation de votre mot de passe - Math Assistant";
    $message = "
    <!DOCTYPE html>
    <html lang='fr'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <style>
            body {
                margin: 0;
                padding: 0;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background-color: #f5f5f5;
                line-height: 1.6;
            }
            .email-container {
                max-width: 600px;
                margin: 0 auto;
                background-color: #ffffff;
            }
            .header-banner {
                background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
                padding: 40px 20px;
                text-align: center;
            }
            .header-banner h1 {
                color: #ffffff;
                margin: 0;
                font-size: 28px;
                font-weight: 600;
                letter-spacing: 0.5px;
            }
            .content {
                padding: 40px 30px;
                color: #333333;
            }
            .greeting {
                font-size: 16px;
                color: #333333;
                margin-bottom: 20px;
            }
            .message-text {
                font-size: 16px;
                color: #555555;
                margin-bottom: 30px;
                line-height: 1.8;
            }
            .code-container {
                background-color: #3B82F6;
                border-radius: 8px;
                padding: 25px;
                text-align: center;
                margin: 30px 0;
                box-shadow: 0 4px 6px rgba(59, 130, 246, 0.2);
            }
            .code-label {
                color: #ffffff;
                font-size: 14px;
                font-weight: 500;
                margin-bottom: 10px;
                text-transform: uppercase;
                letter-spacing: 1px;
            }
            .code {
                color: #ffffff;
                font-size: 36px;
                font-weight: 700;
                letter-spacing: 8px;
                font-family: 'Courier New', monospace;
                margin: 10px 0;
            }
            .validity-info {
                background-color: #FEF3C7;
                border-left: 4px solid #F59E0B;
                padding: 15px 20px;
                margin: 25px 0;
                border-radius: 4px;
            }
            .validity-info p {
                margin: 0;
                color: #92400E;
                font-size: 14px;
            }
            .footer {
                background-color: #F9FAFB;
                padding: 25px 30px;
                text-align: center;
                border-top: 1px solid #E5E7EB;
            }
            .footer-text {
                color: #6B7280;
                font-size: 12px;
                margin: 0;
            }
            .divider {
                height: 1px;
                background-color: #E5E7EB;
                margin: 30px 0;
            }
        </style>
    </head>
    <body>
        <div class='email-container'>
            <div class='header-banner'>
                <h1>Math Assistant</h1>
            </div>
            <div class='content'>
                <div class='greeting'>
                    Bonjour " . htmlspecialchars($user['name']) . ",
                </div>
                <div class='message-text'>
                    Vous avez demandé à réinitialiser votre mot de passe. Utilisez le code suivant pour continuer :
                </div>
                <div class='code-container'>
                    <div class='code-label'>Code de réinitialisation</div>
                    <div class='code'>" . htmlspecialchars($resetCode) . "</div>
                </div>
                <div class='validity-info'>
                    <p><strong>⏱️ Important :</strong> Ce code est valide pendant 15 minutes</p>
                </div>
                <div class='divider'></div>
                <div class='message-text' style='font-size: 14px; color: #6B7280;'>
                    Si vous n'avez pas demandé cette réinitialisation, veuillez ignorer cet email. Votre mot de passe restera inchangé.
                </div>
            </div>
            <div class='footer'>
                <p class='footer-text'>
                    Cet email a été envoyé automatiquement par Math Assistant App.<br>
                    Merci de ne pas répondre à cet email.
                </p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Envoyer l'email avec capture des erreurs détaillées
    // Utiliser HTML (isHTML = true) pour le design
    $errorDetails = '';
    $emailSent = sendEmail($user['email'], $subject, $message, true, $errorDetails);
    
    if ($emailSent) {
        // Email envoyé avec succès - ne pas retourner le code dans la réponse pour la sécurité
        sendJSONResponse([
            'success' => true,
            'message' => 'Un code de réinitialisation a été envoyé à votre adresse email. Veuillez vérifier votre boîte de réception.',
            'token' => $token, // Token nécessaire pour la réinitialisation
            'expires_in' => 15 // minutes
        ], 200);
    } else {
        // Erreur lors de l'envoi de l'email
        error_log("Erreur lors de l'envoi de l'email de réinitialisation pour l'utilisateur ID: " . $user['id'] . " - Détails: " . $errorDetails);
        
        sendJSONResponse([
            'success' => false,
            'message' => 'Erreur lors de l\'envoi de l\'email. Veuillez réessayer plus tard.',
            'error' => 'Email sending failed',
            'error_details' => $errorDetails
        ], 500);
    }
    
} catch (PDOException $e) {
    sendJSONResponse([
        'success' => false,
        'message' => 'Erreur lors de la génération du code de réinitialisation',
        'error' => $e->getMessage()
    ], 500);
} catch (Exception $e) {
    sendJSONResponse([
        'success' => false,
        'message' => 'Erreur: ' . $e->getMessage()
    ], 500);
}
?>

