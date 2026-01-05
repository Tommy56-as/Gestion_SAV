<?php
require_once '../admin_auth.php';
require_once '../../inc/Database.php';
header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation des champs requis
    $required_fields = ['Nom_Utilisateur', 'Email', 'TypeDeCompte', 'MotDePasse', 'NomComplet', 'Telephone', 'Adresse'];
    foreach($required_fields as $field) {
        if(empty($_POST[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Le champ ' . $field . ' est requis']);
            exit;
        }
    }

    $username = trim($_POST['Nom_Utilisateur']);
    $email = trim($_POST['Email']);
    $account_type = $_POST['TypeDeCompte'];
    $password = $_POST['MotDePasse'];
    $full_name = trim($_POST['NomComplet']);
    $phone = trim($_POST['Telephone']);
    $address = trim($_POST['Adresse']);
    $status = 0; // Par défaut, l'utilisateur est actif
    
    // Validation supplémentaire
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email invalide']);
        exit;
    }
    
    if(strlen($password) < 6) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Le mot de passe doit contenir au moins 6 caractères']);
        exit;
    }
    
    try {
        // Vérifier si l'email existe déjà
        $checkStmt = $pdo->prepare("SELECT idUser FROM utilisateur WHERE Email = ?");
        $checkStmt->execute([$email]);
        if($checkStmt->fetch()) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé']);
            exit;
        }
        
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO utilisateur (Nom_Utilisateur, Email, TypeDeCompte, MotDePasse, NomComplet, Telephone, Adresse, Statut) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$username, $email, $account_type, $hashed_password, $full_name, $phone, $address, $status]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Utilisateur ajouté avec succès',
            'id' => $pdo->lastInsertId()
        ]);
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}
?>