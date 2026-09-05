<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
    <title><?= ucfirst($title)?> </title>
    <script>
        (function () {
            try {
                const preferences = JSON.parse(localStorage.getItem('appPreferences') || '{}');
                const root = document.documentElement;
                if (preferences.primary) root.style.setProperty('--fuscha', preferences.primary);
                if (preferences.secondary) root.style.setProperty('--cyan', preferences.secondary);
                if (preferences.fontSize) root.style.setProperty('--base-font-size', preferences.fontSize + 'px');
                if (preferences.font) root.style.setProperty('--app-font', preferences.font);
                if (localStorage.getItem('theme') === 'dark') root.classList.add('dark-mode');
            } catch (error) {}
        }());
    </script>

    <!--material icons-->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet" />

    <link rel="stylesheet" href="css/palette.css" />
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="css/responsive.css" />
    <link rel="stylesheet" href="css/produit.css" />
    <link rel="stylesheet" href="css/vente.css" />
    <link rel="stylesheet" href="css/graphe.css" />
    <link rel="stylesheet" href="css/dashboard.css" />
    <link rel="stylesheet" href="css/sidebar.css" />
    <link rel="stylesheet" href="css/settings.css" />
    <link rel="stylesheet" href="css/permissions.css" />
    <link rel="stylesheet" href="css/sessions.css" />
    <link rel="stylesheet" href="css/historiques.css" />
    <link rel="stylesheet" href="css/commandes.css" />
    <link rel="stylesheet" href="css/reparations.css" />
    <script src="js/icon-fallback.js" defer></script>

</head>

<body>