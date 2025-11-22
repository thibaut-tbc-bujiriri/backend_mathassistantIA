<?php
/**
 * Endpoint API pour réinitialiser le mot de passe
 * Méthode: POST
 * URL: http://localhost:8080/Math_AssistantApp/api/reset_password.php
 * 
 * Paramètres JSON attendus:
 * {
 *   "token": "token_de_reinitialisation",
 *   "new_password": "nouveau_mot_de_passe"
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
    if (empty($input['token']) || empty($input['new_password'])) {
        sendJSONResponse([
            'success' => false,
            'message' => 'Token et nouveau mot de passe requis'
        ], 400);
    }
    
    $token = trim($input['token']);
    $newPassword = $input['new_password'];
    
    // Optionnel: vérifier aussi le code si fourni (pour double vérification)
    $resetCode = isset($input['reset_code']) ? trim($input['reset_code']) : null;
    
    // Valider la longueur du mot de passe
    if (strlen($newPassword) < 6) {
        sendJSONResponse([
            'success' => false,
            'message' => 'Le mot de passe doit contenir au moins 6 caractères'
        ], 400);
    }
    
    // Obtenir la connexion à la base de données
    $pdo = getDBConnection();
    
    // Vérifier le token (d'abord sans vérifier l'expiration pour voir si le token existe)
    $stmt = $pdo->prepare("SELECT prt.id, prt.user_id, prt.expires_at, prt.used, prt.reset_code, TIMESTAMPDIFF(MINUTE, NOW(), prt.expires_at) as minutes_remaining FROM password_reset_tokens prt WHERE prt.token = ? AND prt.used = FALSE");
    $stmt->execute([$token]);
    $resetToken = $stmt->fetch();
    
    if (!$resetToken) {
        // Vérifier si le token existe mais est utilisé
        $stmt2 = $pdo->prepare("SELECT prt.id, prt.used FROM password_reset_tokens prt WHERE prt.token = ?");
        $stmt2->execute([$token]);
        $tokenCheck = $stmt2->fetch();
        
        if ($tokenCheck) {
            if ($tokenCheck['used']) {
                sendJSONResponse([
                    'success' => false,
                    'message' => 'Ce code de réinitialisation a déjà été utilisé'
                ], 400);
            }
        }
        
        sendJSONResponse([
            'success' => false,
            'message' => 'Token invalide'
        ], 400);
    }
    
    // Vérifier si le token est expiré
    $minutesRemaining = (int)$resetToken['minutes_remaining'];
    if ($minutesRemaining <= 0) {
        sendJSONResponse([
            'success' => false,
            'message' => 'Le code de réinitialisation a expiré. Veuillez en demander un nouveau. (Expiré il y a ' . abs($minutesRemaining) . ' minutes)'
        ], 400);
    }
    
    // Vérifier le code si fourni
    if ($resetCode) {
        // Normaliser le code (enlever les espaces, s'assurer qu'il fait 6 caractères)
        $resetCode = str_pad(trim($resetCode), 6, '0', STR_PAD_LEFT);
        $storedCode = str_pad(trim($resetToken['reset_code']), 6, '0', STR_PAD_LEFT);
        
        if ($resetCode !== $storedCode) {
            sendJSONResponse([
                'success' => false,
                'message' => 'Code de réinitialisation incorrect. Code attendu: ' . $storedCode . ', Code reçu: ' . $resetCode
            ], 400);
        }
    } else {
        sendJSONResponse([
            'success' => false,
            'message' => 'Code de réinitialisation requis'
        ], 400);
    }
    
    // Hasher le nouveau mot de passe
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    if (!$hashedPassword) {
        sendJSONResponse([
            'success' => false,
            'message' => 'Erreur lors du hachage du mot de passe'
        ], 500);
    }
    
    // Démarrer une transaction pour s'assurer que tout se passe bien
    try {
        $pdo->beginTransaction();
        
        // Mettre à jour le mot de passe de l'utilisateur
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $result = $stmt->execute([$hashedPassword, $resetToken['user_id']]);
        
        if (!$result) {
            $errorInfo = $stmt->errorInfo();
            $pdo->rollBack();
            sendJSONResponse([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du mot de passe',
                'error' => $errorInfo[2] ?? 'Erreur inconnue',
                'error_code' => $errorInfo[0] ?? '00000'
            ], 500);
        }
        
        // Vérifier qu'une ligne a été mise à jour
        if ($stmt->rowCount() === 0) {
            $pdo->rollBack();
            sendJSONResponse([
                'success' => false,
                'message' => 'Aucun utilisateur trouvé avec cet ID: ' . $resetToken['user_id']
            ], 400);
        }
        
        // Marquer le token comme utilisé
        $stmt = $pdo->prepare("UPDATE password_reset_tokens SET used = TRUE WHERE id = ?");
        $result = $stmt->execute([$resetToken['id']]);
        
        if (!$result) {
            $errorInfo = $stmt->errorInfo();
            $pdo->rollBack();
            sendJSONResponse([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du token',
                'error' => $errorInfo[2] ?? 'Erreur inconnue'
            ], 500);
        }
        
        // Valider la transaction
        $pdo->commit();
        
        // Réponse de succès
        sendJSONResponse([
            'success' => true,
            'message' => 'Mot de passe réinitialisé avec succès!'
        ], 200);
        
    } catch (PDOException $e) {
        // Annuler la transaction en cas d'erreur
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    } catch (Exception $e) {
        // Annuler la transaction en cas d'erreur
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
    
} catch (PDOException $e) {
    // Logger l'erreur pour le débogage
    error_log('Reset password PDO error: ' . $e->getMessage());
    error_log('SQL State: ' . $e->getCode());
    
    sendJSONResponse([
        'success' => false,
        'message' => 'Erreur lors de la réinitialisation du mot de passe',
        'error' => $e->getMessage(),
        'error_code' => $e->getCode()
    ], 500);
} catch (Exception $e) {
    // Logger l'erreur pour le débogage
    error_log('Reset password error: ' . $e->getMessage());
    
    sendJSONResponse([
        'success' => false,
        'message' => 'Erreur: ' . $e->getMessage()
    ], 500);
}
?>

