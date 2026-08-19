<?php
require_once __DIR__ . '/../inc/Database.php';

$search = trim($_GET['history_search'] ?? '');
$date = $_GET['history_date'] ?? '';
$pageNumber = max(1, (int) ($_GET['history_page'] ?? 1));
$perPage = 15;

$conditions = [];
$parameters = [];

if ($search !== '') {
	$conditions[] = '(utilisateur LIKE :search OR `operation_effectuée` LIKE :search)';
	$parameters[':search'] = '%' . $search . '%';
}

if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
	$conditions[] = 'DATE(date_action) = :history_date';
	$parameters[':history_date'] = $date;
} else {
	$date = '';
}

$where = $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';

try {
	$countStatement = $pdo->prepare("SELECT COUNT(*) FROM historiques{$where}");
	$countStatement->execute($parameters);
	$totalRows = (int) $countStatement->fetchColumn();
	$totalPages = max(1, (int) ceil($totalRows / $perPage));
	$pageNumber = min($pageNumber, $totalPages);
	$offset = ($pageNumber - 1) * $perPage;

	$historyStatement = $pdo->prepare(
		"SELECT id, utilisateur, `operation_effectuée` AS operation_effectue, date_action
		 FROM historiques{$where}
		 ORDER BY date_action DESC, id DESC
		 LIMIT :limit OFFSET :offset"
	);
	foreach ($parameters as $name => $value) {
		$historyStatement->bindValue($name, $value, PDO::PARAM_STR);
	}
	$historyStatement->bindValue(':limit', $perPage, PDO::PARAM_INT);
	$historyStatement->bindValue(':offset', $offset, PDO::PARAM_INT);
	$historyStatement->execute();
	$histories = $historyStatement->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $exception) {
	error_log('Erreur récupération historiques: ' . $exception->getMessage());
	$histories = [];
	$totalRows = 0;
	$totalPages = 1;
	$pageNumber = 1;
	$historyError = 'Impossible de charger les historiques pour le moment.';
}

$historyQuery = ['history_search' => $search, 'history_date' => $date];
?>

<section class="history-page">
    <div class="history-heading">
        <div>
            <p class="history-eyebrow">Suivi de l'activité</p>
            <h1><span class="material-icons-sharp">history</span> Historiques</h1>
            <p class="history-intro">Consultez les dernières actions enregistrées dans votre espace.</p>
        </div>
        <div class="history-count">
            <strong><?= number_format($totalRows, 0, ',', ' ') ?></strong>
            <span>événement<?= $totalRows > 1 ? 's' : '' ?></span>
        </div>
    </div>

    <form class="history-filters" method="get" action="home.php">
        <input type="hidden" name="page" value="historiques">
        <label>
            <span class="material-icons-sharp">search</span>
            <span class="sr-only">Rechercher dans les historiques</span>
            <input type="search" name="history_search" value="<?= htmlspecialchars($search) ?>"
                placeholder="Rechercher une action ou un utilisateur">
        </label>
        <label>
            <span class="material-icons-sharp">calendar_today</span>
            <span class="sr-only">Filtrer par date</span>
            <input type="date" name="history_date" value="<?= htmlspecialchars($date) ?>">
        </label>
        <button class="btn btn-primary" type="submit">
            <span class="material-icons-sharp">filter_alt</span> Filtrer
        </button>
        <?php if ($search !== '' || $date !== ''): ?>
        <a class="history-reset" href="home.php?page=historiques">
            <span class="material-icons-sharp">close</span> Réinitialiser
        </a>
        <?php endif; ?>
    </form>

    <?php if (isset($historyError)): ?>
    <div class="history-state history-error">
        <span class="material-icons-sharp">error_outline</span>
        <?= htmlspecialchars($historyError) ?>
    </div>
    <?php elseif (!$histories): ?>
    <div class="history-state">
        <span class="material-icons-sharp">inbox</span>
        <strong>Aucun historique trouvé</strong>
        <span>Les actions apparaîtront ici dès qu'elles seront enregistrées.</span>
    </div>
    <?php else: ?>
    <div class="history-table-wrap">
        <table class="history-table">
            <thead>
                <tr>
                    <th>Utilisateur</th>
                    <th>Action</th>
                    <th>Date et heure</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($histories as $history): ?>
                <tr>
                    <td>
                        <span class="history-user-icon material-icons-sharp">person</span>
                        <?= htmlspecialchars($history['utilisateur'] ?? 'Système') ?>
                    </td>
                    <td><?= nl2br(htmlspecialchars($history['operation_effectue'] ?? 'Action non précisée')) ?></td>
                    <td><?= htmlspecialchars(date('d/m/Y à H:i', strtotime($history['date_action']))) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
    <nav class="history-pagination" aria-label="Pagination des historiques">
        <?php if ($pageNumber > 1): ?>
        <a href="home.php?<?= http_build_query($historyQuery + ['page' => 'historiques', 'history_page' => $pageNumber - 1]) ?>"
            aria-label="Page précédente">
            <span class="material-icons-sharp">chevron_left</span>
        </a>
        <?php endif; ?>
        <span>Page <?= $pageNumber ?> sur <?= $totalPages ?></span>
        <?php if ($pageNumber < $totalPages): ?>
        <a href="home.php?<?= http_build_query($historyQuery + ['page' => 'historiques', 'history_page' => $pageNumber + 1]) ?>"
            aria-label="Page suivante">
            <span class="material-icons-sharp">chevron_right</span>
        </a>
        <?php endif; ?>
    </nav>
    <?php endif; ?>
    <?php endif; ?>
</section>