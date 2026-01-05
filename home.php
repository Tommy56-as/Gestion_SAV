<?php 
require_once 'Controller/admin_auth.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$title = $page;
?>
<?php include 'Model/header.php'; ?>

<div class="dashboard-container">

    <!-- Sidebar -->
    <?php include 'views/siderbar.php'; ?>

    <!-- Contenu dynamique -->
    <div class="main-container">
        <?php 
            $file = "views/".$page . ".php";
            if (file_exists($file)) {
                include $file;
                $title = $page."php";
            } else {
    echo "<h2 style='padding:20px'>Page introuvable !</h2>";
      }
        ?>
    </div>

</div>


<?php include 'Model/footer.php'; ?>