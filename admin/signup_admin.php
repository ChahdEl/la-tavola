<?php
// Ajouter en début de script

session_start();
$success = '';
$erreur = [];
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "restaurants";
$code_confi = 'Cappuccino2025';


if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $erreur['connexion'] = "Échec de la connexion : " . $e->getMessage();
}



if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_POST)) {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
           echo 'Erreur CSRF token invalide';
        }
    }
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mdp = trim($_POST['mdp'] ?? '');
    $c_mdp = trim($_POST['c_mdp'] ?? '');
    $code = trim($_POST['code'] ?? '');

    // Validation des champs
    if (!preg_match('/^[\p{L}\s\-]{2,}$/u', $nom)) {
        $erreur['nom'] = 'Le nom doit contenir au moins 2 caractères alphabétiques uniquement';
    }
    
    if (!preg_match('/^[\p{L}\s\-]{2,}$/u', $prenom)) {
        $erreur['prenom'] = 'Le prénom doit contenir au moins 2 caractères alphabétiques uniquement';
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur['email'] = 'L\'email n\'est pas valide';
    }
    
    $pwd_strength = strlen($mdp) >= 8 
        && preg_match('/[A-Z]/', $mdp) 
        && preg_match('/[0-9]/', $mdp) 
        && preg_match('/[^A-Za-z0-9]/', $mdp);
    
    if (!$pwd_strength) {
        $erreur['mdp'] = 'Mot de passe trop faible : 8 caractères min. + majuscule + chiffre + caractère spécial.';
    }
    
    if ($mdp !== $c_mdp) {
        $erreur['c_mdp'] = 'La confirmation du mot de passe est différente du mot de passe';
    }
    
    if ($code !== $code_confi) {
        $erreur['code'] = "Code d'invitation incorrect.";
    }
    
    if (empty($erreur)) {
        $stmt = $conn->prepare('SELECT id FROM admins WHERE email = :email');
        $stmt->execute(['email' => $email]);
        
        if ($stmt->fetch()) {
            $erreur['email'] = 'Un compte avec cette adresse existe déjà.';
        }
    }

    if (empty($erreur)) {
        $hash = password_hash($mdp, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('INSERT INTO admins (nom, prenom, email, mot_de_passe, role, actif) 
                               VALUES (:nom, :prenom, :email, :hash, "admin", 1)');
        $stmt->execute([
            'nom'    => $nom,
            'prenom' => $prenom,
            'email'  => $email,
            'hash'   => $hash,
        ]);

        $success = 'Compte créé avec succès ! Vous pouvez maintenant vous connecter.';
         header('Location: login_admin.php?signup=ok'); 
        exit;
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style1.css">
    <title>sign up</title>
</head>
<body>
    <section class="login_admin">
    <div class="info">
        <p>
        <i class="fas fa-clock"></i>7j/7: 12h:22h | <i class="fas fa-phone"></i> 05.35.60.80.40 | <i class="fas fa-map-marker-alt"></i> Mag 1, Lot Hajar, Ain Amir ( en face clinique arrazi )
        </p>
        <div class="reseauSociaux">
            <a href="https://www.instagram.com/cappuccinofes/" id="instagram"><i class="fab fa-instagram"></i></a> |
            <a href="https://www.instagram.com/duplexsteakhouse/" id="Facebook"><i class="fab fa-facebook"></i></a> |
            <a href="https://www.instagram.com/duplexsteakhouse/" id="whatsap"><i class="fab fa-whatsapp"></i></a>
        </div>  
    </div>

    <div class="container">
        <h2>sign up</h2>
        
        <form action="#" method="POST" class="reservation-form">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>"> 
            <div class="form-group">
                <label for="nom">Nom <span class="required">*</span></label>
                <input type="text" id="nom" name="nom" value="<?php echo isset($nom) ? htmlspecialchars($nom) : ''; ?>" placeholder="ecrivez votre nom ici" required>
               
            </div>
            <div class="form-group">
                <label for="prenom">Prenom <span class="required">*</span></label>
                <input type="text" id="prenom" name="prenom" value="<?php echo isset($prenom) ? htmlspecialchars($prenom) : ''; ?>" placeholder='ecrivez votre prenom ici' required>
            </div>
            <div class="form-group">
                <label for="email">Email <span class="required">*</span></label>
                <input type="email" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" placeholder="ex: myname@gmail.com" required>
               

            </div>
            <div class="form-group">
                <label for="mdp">Mot de passe <span class="required">*</span></label>
                <input type="password" id="mdp" name="mdp" value="<?php echo isset($mdp) ? htmlspecialchars($mdp) : ''; ?>" required>
              

            </div>
            <div class="form-group">
                <label for="c_mdp">confirmer le Mot de passe <span class="required">*</span></label>
                <input type="password" id="c_mdp" name="c_mdp" value="<?php echo isset($c_mdp) ? htmlspecialchars($c_mdp) : ''; ?>" required>

            </div>
            <div class="form-group">
                <label for="code">code d'invitation <span class="required">*</span></label>
                <input type="text" id="code" name="code" value="<?php echo isset($code) ? htmlspecialchars($code) : ''; ?>" required>
            </div>
            <button type="submit" class="submit-btn">Enregistrer</button>
        </form>
    </div>
    <?php if (!empty($success)): ?>
    <div class="success-message">
        <?php echo htmlspecialchars($success); ?>
    </div>
<?php endif; ?> 
<?php if (!empty($erreur['nom'])): ?>
    <div class="error-message">
        <?php  echo htmlspecialchars($erreur['nom']); ?>
    </div>
<?php endif; ?>

<?php if (!empty($erreur['prenom'])): ?>
    <div class="error-message">
        <?php echo htmlspecialchars($erreur['prenom']); ?>
    </div>
<?php endif; ?>
<?php if (!empty($erreur['mdp'])): ?>
    <div class="error-message">
        <?php echo htmlspecialchars($erreur['mdp']); ?>
    </div>
<?php endif; ?>
<?php if (!empty($erreur['email'])): ?>
    <div class="error-message">
        <?php echo htmlspecialchars($erreur['email']); ?>
    </div>
 <?php endif; ?>
    <?php if (!empty($erreur['c_mdp'])): ?>
    <div class="error-message">
        <?php echo htmlspecialchars($erreur['c_mdp']); ?>
    </div>

<?php endif; ?>
<?php if (!empty($erreur['code'])): ?>
    <div class="error-message">
        <?php echo htmlspecialchars($erreur['code']); ?>
    </div>
<?php endif; ?>
    </section>
</body>
</html>
