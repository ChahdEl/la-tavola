<?php
$servername = 'localhost';
$username = 'root';
$password = '';
$dbname = 'restaurants';

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $erreur = "Échec de la connexion : " . $e->getMessage();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST['nom'];
    $description = $_POST['description'];
    $prix = $_POST['prix'];
    $disponible = isset($_POST['disponible']) ? 1 : 0;

    // Gérer l’image
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        // Vérification de l'existence du dossier uploads
        if (!file_exists('/Applications/XAMPP/xamppfiles/htdocs/projetPhp/uploads') && !is_dir('uploads')) {
            mkdir('uploads', 0777, true);  
        }
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = mime_content_type($_FILES['image']['tmp_name']);
        
        if (!in_array($file_type, $allowed_types)) {
            die("Type de fichier non autorisé. Seules les images JPEG, PNG, et GIF sont autorisées.");
        }
    
        if ($_FILES['image']['size'] > 2000000) {  // Limite de 2 Mo
            die("Le fichier est trop volumineux. La taille maximale autorisée est de 2 Mo.");
        }
    
        // Création du nom unique pour l'image
        $image_name = uniqid() . "_" . $_FILES['image']['name'];
        $image_path = "../uploads/" . $image_name;
    
        
        if (file_exists($image_path)) {
            die("Le fichier existe déjà dans le dossier 'uploads'.");
        }
    
        // Déplacer le fichier
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
            die("Échec du déplacement du fichier dans le dossier 'uploads'.");
        }
    
    } else {
        $image_path = null;  // Si pas de fichier ou erreur, on ne met pas de chemin d'image
    }

    // Préparer et exécuter la requête d'insertion
    $stmt = $conn->prepare("INSERT INTO plats (nom, description, prix, image, disponible) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$nom, $description, $prix, $image_path, $disponible]);

    // Rediriger vers le dashboard
    header("Location: dashboard_admin.php");
    exit;
}
?>