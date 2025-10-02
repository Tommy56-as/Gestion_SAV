<?php

@include 'config.php';

session_start();

if(isset($_POST['submit'])){

   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $pass = md5($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);

   $sql = "SELECT * FROM `utilisateur` WHERE email = ? AND password = ?";
   $stmt = $conn->prepare($sql);
   $stmt->execute([$email, $pass]);
   $rowCount = $stmt->rowCount();  

   $row = $stmt->fetch(PDO::FETCH_ASSOC);

   if($rowCount > 0){

      if($row['user_type'] == 'admin'){

         $_SESSION['admin_id'] = $row['id'];
         header('location:admin_page.php');

      }elseif($row['user_type'] == 'user'){

         $_SESSION['user_id'] = $row['id'];
         header('location:home.php');

      }else{
         $message[] = "l'utilisateur n'exite pas!";
         
      }

   }else{
      $message[] = 'email ou mot de passe incorect!';
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>login</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
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
   
<section class="form-container">

   <form action="" method="POST">
      <div class="logo-block">
         <i class="fas fa-money-bill-wave"></i>
         <p class="logo-name">Mon Argent</p>
      </div>
      <h3>Connexion</h3><br><br>
      <div class="input-group">
         <label for="email">Email</label>
         <div class="input-icon">
            <i class="fas fa-envelope"></i>
            <input type="email" name="email" class="box" id="input" placeholder="Entrez votre adresse email" required>
         </div>
      </div>
      <div class="input-group">
         <label for="pass">Mot de passe</label>
         <div class="input-icon">
            <i class="fas fa-lock"></i>
            <input type="password" name="pass" class="box" id="password-input" placeholder="Entrez votre mot de passe" required>
            <i class="fas fa-eye" id="toggle-password"></i>
         </div>
      </div>
      <input type="submit" value="connectez-vous" class="btn" name="submit">
      <p>vous n'avez pas de compte ? <a href="register.php">inscrivez-vous</a></p>
   </form>

</section>


<script>
document.getElementById('toggle-password').addEventListener('click', function() {
    const inputP = document.getElementById('password-input');
    if (inputP.type === 'password') {
        inputP.type = 'text';
        this.classList.remove('fa-eye');
        this.classList.add('fa-eye-slash');
    } else {
        inputP.type = 'password';
        this.classList.remove('fa-eye-slash');
        this.classList.add('fa-eye');
    }

});
</script>

</body>
</html>