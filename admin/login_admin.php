<?php
session_start();
$erreur = [];

try {
    $conn = new PDO("mysql:host=localhost;dbname=restaurants", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $erreur['connexion'] = "Erreur de connexion : " . $e->getMessage();
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    $mdp = $_POST['mdp'] ?? '';

        if(!empty($email) || !empty($mdp)){
            $stmt = $conn->prepare('SELECT id, nom, prenom, email, mot_de_passe FROM admins WHERE email = :email AND actif = 1');
            $stmt->execute(['email' => $email]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);//admin est un tableau associatif de cle nom de la colonne de la table admins
            //PDO::FETCH_ASSOC ca qui determine la format d'admin

            if ($admin && password_verify($mdp, $admin['mot_de_passe'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_nom'] = $admin['nom'];
                $_SESSION['admin_email'] = $admin['email'];
                header('Location: dashboard_admin.php'); 
                exit;
            } else {
                $erreur['login'] = 'Email ou mot de passe incorrect.';
            }
        }
    
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion Admin</title>
    <link rel="stylesheet" href="style1.css">
</head>
<body>
    <section class="login_admin">
        <div class="container_login">
            <h2>Connexion </h2>
           
            <form action="" method="POST" class="reservation-form">
                <div class="form-group">
                    <label for="email">Email :</label>
                    <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="mdp">Mot de passe :</label>
                    <input type="password" id="mdp" name="mdp" required>
                </div>
                
                <button type="submit" class="submit-btn">Connexion</button>
                <a href="signup_admin.php" id='lien_creer'>-creér votre compte</a>
            </form>
            <?php if (!empty($erreur['login'])): ?>
                <div class="error-message"><?php echo htmlspecialchars($erreur['login']); ?></div>
            <?php endif; ?>
            <?php if (!empty($erreur['connexion'])): ?>
                <div class="error-message"><?php echo htmlspecialchars($erreur['connexion']); ?></div>
            <?php endif; ?>
           

        </div>
    </section>
</body>
</html>
