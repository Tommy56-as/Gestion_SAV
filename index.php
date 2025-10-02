<?php 
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
?>
<?php include 'header.php'; ?>

<div class="dashboard-container">
    
    <!-- Sidebar -->
    <?php include 'siderbar.php'; ?>

    <!-- Contenu dynamique -->
    <div class="main-container">
        <?php 
            $file =  $page . ".php";
            if (file_exists($file)) {
                include $file;
            } else {
    echo "<h2 style='padding:20px'>Page introuvable !</h2>";
      }
        ?>
    </div>

</div>
 
   
<?php include 'footer.php'; ?>
