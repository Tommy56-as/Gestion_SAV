<?php
require_once '../admin_auth.php';
require_permission('reparation.update');
require_csrf();
require_once '../../inc/history.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$idrep = filter_input(INPUT_POST, 'idrep', FILTER_VALIDATE_INT);
$statut = $_POST['statut'] ?? '';
$diagnostic = trim($_POST['diagnostic'] ?? '');
$solution = trim($_POST['solution'] ?? '');
if (!$idrep || !in_array($statut, ['en_attente', 'en_cours', 'terminee'], true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Réparation ou statut invalide']);
    exit;
}

try {
    $pdo->beginTransaction();
    $statement = $pdo->prepare('SELECT nomClient, telephone, email, appareil, prixTotal, statut, message_envoye FROM reparation WHERE idrep = ? FOR UPDATE');
    $statement->execute([$idrep]);
    $repair = $statement->fetch(PDO::FETCH_ASSOC);
    if (!$repair) {
        throw new RuntimeException('Réparation introuvable');
    }
    if ($repair['statut'] === 'terminee' && $statut !== 'terminee') {
        throw new RuntimeException('Une réparation terminée ne peut plus être réouverte');
    }

    $update = $pdo->prepare('UPDATE reparation SET statut = ?, diagnostic = ?, solution = ? WHERE idrep = ?');
    $update->execute([$statut, $diagnostic ?: null, $solution ?: null, $idrep]);
    $notification = null;
    if ($statut === 'terminee' && !$repair['message_envoye']) {
        $message = "Bonjour {$repair['nomClient']}, votre appareil ({$repair['appareil']}) est réparé. Vous pouvez passer le récupérer. Montant à payer : {$repair['prixTotal']} FCFA.";
        $messageUpdate = $pdo->prepare('UPDATE reparation SET message_envoye = 1, message_envoye_at = NOW() WHERE idrep = ?');
        $messageUpdate->execute([$idrep]);
        $notification = ['telephone' => $repair['telephone'], 'email' => $repair['email'], 'message' => $message];
    }
    $pdo->commit();
    log_history($pdo, "Mise à jour de la réparation {$idrep} : {$statut}");
    echo json_encode(['success' => true, 'message' => 'Réparation mise à jour', 'notification' => $notification]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $safeMessages = ['Réparation introuvable', 'Une réparation terminée ne peut plus être réouverte'];
    error_log('Erreur mise à jour réparation: ' . $e->getMessage());
    http_response_code(in_array($e->getMessage(), $safeMessages, true) ? 409 : 500);
    echo json_encode(['success' => false, 'message' => in_array($e->getMessage(), $safeMessages, true) ? $e->getMessage() : 'Erreur lors de la mise à jour de la réparation']);
}