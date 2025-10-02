<?php

//include 'config.php';

if(isset($_POST['submit'])){

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $phone = $_POST['phone'];
   $phone = filter_var($phone, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $pass = md5($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);
   $cpass = md5($_POST['cpass']);
   $cpass = filter_var($cpass, FILTER_SANITIZE_STRING);

   $select = $conn->prepare("SELECT * FROM `utilisateurs` WHERE email = ?");
   $select->execute([$email]);

   if($select->rowCount() > 0){
      $message[] = 'cette adresse email existe deja!';
   }else{
      if($pass != $cpass){
         $message[] = 'les mot de passe ne correspondent!';
      }else{
         $insert = $conn->prepare("INSERT INTO `utilisateurs`(name, telephone, email, password) VALUES(?,?,?,?)");
         $insert->execute([$name,$phone, $email, $pass]);

      }
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>inscription</title>

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

   <form action="" enctype="multipart/form-data" method="POST">
      <div class="logo-block">
         <i class="fas fa-money-bill-wave"></i>
         <p class="logo-name">Mon Argent</p>
      </div>
      <h3>Enregistrement</h3><br><br>
      <div class="input-group">
         <label for="name">Nom complet</label>
         <div class="input-icon">
            <i class="fas fa-user"></i>
            <input type="text" name="name" class="box" placeholder="entrer votre nom complet" required>
         </div>
      </div>
      <div class="input-group">
        <label for="phone">Numero de telephone</label>
         <div class="input-icon">
            <i class="fas fa-phone"></i>
            <input type="phone" name="phone" class="box" placeholder="enter votre numero de telephone" required>
         </div>
      </div>
      <div class="input-group">
        <label for="email">Email</label>
         <div class="input-icon">
            <i class="fas fa-envelope"></i>
            <input type="email" name="email" class="box" placeholder="enter your email" required>
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
       <div class="input-group">
         <label for="cpass">Confirmer le mot de passe</label>
         <div class="input-icon">
            <i class="fas fa-lock"></i>
            <input type="password" name="cpass" class="box" placeholder="confirm your password" required>
            <i class="fas fa-eye" id="toggle-password"></i>
         </div>
      </div>
    
      <input type="submit" value="register now" class="btn" name="submit">
      <p>vous avez un compte? <a href="login.php">connectez-vous</a></p>
   </form>

</section>

</body>
</html>