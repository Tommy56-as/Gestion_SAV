<?php

@include 'config.php';

session_start();

if(isset($_POST['submit'])){

   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $pass = md5($_POST['password']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);

   $sql = "SELECT * FROM `utilisateur` WHERE Email = ? AND MotDePasse = ?";
   $stmt = $conn->prepare($sql);
   $stmt->execute([$email, $pass]);
   $rowCount = $stmt->rowCount();  

   $row = $stmt->fetch(PDO::FETCH_ASSOC);

   if($rowCount > 0){

      if($row['TypeDeCompte'] == 'Administrateur'){

         $_SESSION['admin_id'] = $row['id'];
         header('location:index.php');

      }elseif($row['TypeDeCompte'] == 'user'){

         $_SESSION['user_id'] = $row['id'];
         header('location:index.php');

      }else{
         $message[] = 'no user found!';
      }

   }else{
      $message[] = 'incorrect email or password!';
   }

}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/component.css">
</head>
<body>
    
    <?php

if(isset($message)){
   foreach($message as $message){
      echo '
      <div class="message">
         <span>'.$message.'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}

?>

    <div class="container">
        <div class="header">
            <div class="logo">
                <i class="fas fa-coins"></i>
            </div>
            <h1>Systeme de Gestion</h1>
            <h1>des servies apres-vente</h1>
        </div>
        
        <div class="forms-container">
            <!-- Connexion Form -->
            <section class="form-section">
                <h2>Connexion</h2>
                
                <form id="loginForm" action="" method="POST">
                    <div class="form-group">
                        <label for="login-email">Email</label>
                        <div class="input-with-icon">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="login-email" name="email" placeholder="Entrez votre adresse email" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="login-password">Mot de passe</label>
                        <div class="input-with-icon password-input">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="login-password" name="password" placeholder="Entrez votre mot de passe" required>
                            <button type="button" class="password-toggle" id="login-toggle">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" class="button">Se connecter</button>
                    
                    <div class="link-container">
                        <p><a href="Register.php" class="link">mot de passe oublie ?</a></p>
                    </div>
                </form>
            </section>
        </div>
    </div>
    <?php
    require_once('footer.php')
    ?>
</body>
</html>