<?php

function send_password_reset_email(string $email, string $token): bool
{
    $baseUrl = rtrim((string) getenv('GESTION_SAV_BASE_URL'), '/');
    if ($baseUrl === '') {
        $serverName = (string) ($_SERVER['SERVER_NAME'] ?? '');
        if (!preg_match('/^[a-zA-Z0-9.-]+$/', $serverName)) {
            return false;
        }

        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['SERVER_PORT'] ?? null) === '443');
        $scheme = $isHttps ? 'https' : 'http';
        $port = (int) ($_SERVER['SERVER_PORT'] ?? ($isHttps ? 443 : 80));
        $portSuffix = in_array($port, [80, 443], true) ? '' : ':' . $port;
        $scriptDirectory = str_replace('\\', '/', dirname((string) ($_SERVER['SCRIPT_NAME'] ?? '/')));
        $path = $scriptDirectory === '/' || $scriptDirectory === '.' ? '' : '/' . trim($scriptDirectory, '/');
        $baseUrl = $scheme . '://' . $serverName . $portSuffix . $path;
    }

    if (!filter_var($baseUrl, FILTER_VALIDATE_URL) || !in_array(parse_url($baseUrl, PHP_URL_SCHEME), ['http', 'https'], true)) {
        return false;
    }

    $sender = (string) (getenv('GESTION_SAV_FROM_EMAIL') ?: 'no-reply@gestion-sav.local');
    if (!filter_var($sender, FILTER_VALIDATE_EMAIL)) {
        error_log('Adresse GESTION_SAV_FROM_EMAIL invalide.');
        return false;
    }

    $resetUrl = $baseUrl . '/mot_de_passe_reinitialiser.php?token=' . rawurlencode($token);
    $subject = 'Reinitialisation de votre mot de passe G.S.A.V';
    $body = "Bonjour,\n\n"
        . "Une demande de reinitialisation du mot de passe de votre compte G.S.A.V a ete effectuee.\n"
        . "Pour choisir un nouveau mot de passe, ouvrez ce lien dans l'heure qui suit :\n"
        . $resetUrl . "\n\n"
        . "Si vous n'etes pas a l'origine de cette demande, ignorez cet e-mail.\n";
    $headers = [
        'From' => $sender,
        'Content-Type' => 'text/plain; charset=UTF-8',
        'X-Mailer' => 'PHP/' . PHP_VERSION,
    ];

    return @mail($email, $subject, $body, $headers);
}