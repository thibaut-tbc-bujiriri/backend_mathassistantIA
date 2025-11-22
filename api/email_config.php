<?php
/**
 * Configuration Email Gmail
 * 
 * INSTRUCTIONS:
 * 1. Remplacez 'VOTRE_EMAIL@gmail.com' ci-dessous par votre adresse Gmail
 * 2. Le mot de passe d'application est déjà configuré
 * 3. Assurez-vous que l'authentification à deux facteurs est activée sur votre compte Gmail
 */

// Adresse Gmail configurée
$GMAIL_ADDRESS = getenv('GMAIL_ADDRESS') ?: 'YOUR_EMAIL@gmail.com';

// Mot de passe d'application Gmail (sans espaces)
$GMAIL_APP_PASSWORD = getenv('GMAIL_APP_PASSWORD') ?: 'YOUR_GMAIL_APP_PASSWORD_HERE';

// Retourner la configuration
return [
    'SMTP_USER' => $GMAIL_ADDRESS,
    'SMTP_PASS' => $GMAIL_APP_PASSWORD,
    'SMTP_FROM_EMAIL' => $GMAIL_ADDRESS,
    'SMTP_FROM_NAME' => 'Math Assistant App',
    'SMTP_HOST' => 'smtp.gmail.com',
    'SMTP_PORT' => 587
];

