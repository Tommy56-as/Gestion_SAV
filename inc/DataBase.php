<?php
// En production, fournir ces valeurs via les variables d'environnement.
define('DB_HOST', getenv('GESTION_SAV_DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('GESTION_SAV_DB_NAME') ?: 'gestion_sav_web');
define('DB_USER', getenv('GESTION_SAV_DB_USER') ?: 'root');
define('DB_PASS', getenv('GESTION_SAV_DB_PASS') ?: '');


// Connexion à la base de données
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . 
        ";dbname=" . DB_NAME, 
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch(PDOException $e) {
    error_log('Erreur de connexion à la base: ' . $e->getMessage());
    http_response_code(500);
    die('Le service est temporairement indisponible.');
}
?>