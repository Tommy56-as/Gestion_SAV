# TODO - Améliorations Dashboard & Graphe (Statistiques)

## Dashboard (terminé)

- [x] Design moderne (dashboard.css)
- [x] Sections analytiques (tendances, alertes stock, garanties)
- [x] Sidebar moderne + état actif

## Graphe/Statistiques - Feedback (terminé)

- [x] 1. Remplacer les icônes Font Awesome non visibles par les **material-icons-sharp**
- [x] 2. Ajouter un **graphique circulaire (doughnut)** pour le Top 5 des produits les plus vendus
- [x] 3. Ajouter un **graphique de comparaison hebdomadaire** (7 derniers jours vs 7 jours précédents) basé sur les requêtes de dashboard.php
- [x] 4. Ajouter un doughnut pour la répartition des **réparations par statut**
- [x] 5. Rendre la lecture des stats plus facile (résumés, badges, légendes, valeurs formatées)
- [x] 6. Styles CSS des nouvelles sections (graphe-hebdo, graphe-double, graphe-doughnut) dans `css/graphe.css`
- [x] 7. Vérifier la syntaxe PHP

## Fichiers modifiés

- `views/graphe.php` (icônes material + 2 nouveaux graphiques Chart.js + données réelles)
- `css/graphe.css` (styles des nouveaux graphiques + responsive)
- `css/dashboard.css` (styles analytiques)
- `Model/header.php` (link graphe.css)
