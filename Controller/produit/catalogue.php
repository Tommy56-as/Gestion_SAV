<?php
require_once '../admin_auth.php';
require_permission('produit.read');
require_entreprise_feature('produits');

$entrepriseId = require_current_entreprise_id();
$entreprise = current_entreprise($pdo);
$statement = $pdo->prepare(
    'SELECT designation, caracteristique, categorie, prixUnitaire, image
     FROM produit
     WHERE idEntreprise = ?
     ORDER BY designation, caracteristique'
);
$statement->execute([$entrepriseId]);
$produits = $statement->fetchAll(PDO::FETCH_ASSOC);
$escape = static fn ($value): string => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
$contact = array_filter([
    $entreprise['telephone'] ?? '',
    $entreprise['adresse'] ?? '',
    $entreprise['boite_postale'] ?? '',
], static fn ($value): bool => trim((string) $value) !== '');
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalogue - <?= $escape($entreprise['nom'] ?? 'Entreprise') ?></title>
    <style>
    :root {
        color-scheme: light;
        font-family: Arial, Helvetica, sans-serif;
        color: #202a35;
    }

    * {
        box-sizing: border-box;
    }

    body {
        margin: 0;
        background: #eef1f3;
    }

    .toolbar {
        display: flex;
        justify-content: center;
        padding: 18px;
    }

    .print-button {
        border: 0;
        border-radius: 5px;
        padding: 11px 18px;
        background: #176b5b;
        color: #fff;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
    }

    .catalogue {
        width: min(210mm, 100%);
        min-height: 297mm;
        margin: 0 auto 24px;
        padding: 18mm 16mm;
        background: #fff;
    }

    .catalogue-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 20px;
        padding-bottom: 9mm;
        border-bottom: 3px solid #176b5b;
    }

    .catalogue-header h1 {
        margin: 0 0 5px;
        font-size: 25px;
    }

    .catalogue-header p {
        margin: 3px 0;
        color: #57616b;
        font-size: 12px;
    }

    .catalogue-mark {
        color: #176b5b;
        font-size: 12px;
        font-weight: 700;
        text-align: right;
    }

    .catalogue-date {
        margin: 8mm 0 5mm;
        color: #57616b;
        font-size: 11px;
    }

    .product-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 6mm;
    }

    .product {
        display: grid;
        grid-template-columns: 31mm minmax(0, 1fr);
        gap: 4mm;
        min-height: 37mm;
        padding: 4mm;
        border: 1px solid #d8dfe3;
        break-inside: avoid;
    }

    .product-image {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 31mm;
        height: 29mm;
        overflow: hidden;
        background: #f1f4f3;
        color: #7b858d;
        font-size: 10px;
        text-align: center;
    }

    .product-image img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .product h2 {
        margin: 0 0 5px;
        font-size: 14px;
        overflow-wrap: anywhere;
    }

    .product p {
        margin: 3px 0;
        color: #57616b;
        font-size: 10px;
        line-height: 1.4;
        overflow-wrap: anywhere;
    }

    .product .price {
        margin-top: 7px;
        color: #176b5b;
        font-size: 13px;
        font-weight: 700;
    }

    .empty {
        grid-column: 1 / -1;
        padding: 20px 0;
        color: #57616b;
        text-align: center;
    }

    .catalogue-footer {
        margin-top: 10mm;
        padding-top: 4mm;
        border-top: 1px solid #d8dfe3;
        color: #6a747c;
        font-size: 9px;
        text-align: center;
    }

    @page {
        size: A4;
        margin: 0;
    }

    @media print {
        body {
            background: #fff;
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }

        .toolbar {
            display: none;
        }

        .catalogue {
            width: auto;
            min-height: 0;
            margin: 0;
            padding: 16mm;
        }
    }

    @media screen and (max-width: 650px) {
        .catalogue {
            padding: 24px 18px;
        }

        .catalogue-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .catalogue-mark {
            text-align: left;
        }

        .product-grid {
            grid-template-columns: 1fr;
        }
    }
    </style>
</head>

<body>
    </div>
    <main class="catalogue">
        <header class="catalogue-header">
            <div>
                <h1><?= $escape($entreprise['nom'] ?? 'Catalogue produits') ?></h1>
                <?php foreach ($contact as $detail): ?><p><?= $escape($detail) ?></p><?php endforeach; ?>
            </div>
            <div class="catalogue-mark">CATALOGUE PRODUITS</div>
        </header>
        <p class="catalogue-date">Édité le <?= date('d/m/Y') ?> · <?= count($produits) ?>
            produit<?= count($produits) === 1 ? '' : 's' ?></p>
        <section class="product-grid">
            <?php if (!$produits): ?>
            <p class="empty">Aucun produit à afficher dans le catalogue.</p>
            <?php endif; ?>
            <?php foreach ($produits as $produit): ?>
            <?php $image = basename((string) ($produit['image'] ?? '')); ?>
            <article class="product">
                <div class="product-image">
                    <?php if ($image !== ''): ?>
                    <img src="../../img/<?= rawurlencode($image) ?>" alt="<?= $escape($produit['designation']) ?>">
                    <?php else: ?>
                    Image non disponible
                    <?php endif; ?>
                </div>
                <div>
                    <h2><?= $escape($produit['designation']) ?></h2>
                    <p><?= $escape($produit['caracteristique']) ?></p>
                    <p><?= $escape(ucfirst((string) ($produit['categorie'] ?? ''))) ?></p>
                    <p class="price"><?= number_format((float) $produit['prixUnitaire'], 0, ',', ' ') ?> FCFA</p>
                </div>
            </article>
            <?php endforeach; ?>
        </section>
        <footer class="catalogue-footer"><?= $escape($entreprise['nom'] ?? '') ?> · Catalogue généré depuis Gestion SAV
        </footer>
    </main>
    <script>
    window.addEventListener('load', () => {
        const images = [...document.images];
        Promise.all(images.map((image) => image.decode().catch(() => {})))
            .then(() => setTimeout(() => window.print(), 250));
    });
    </script>
</body>

</html>