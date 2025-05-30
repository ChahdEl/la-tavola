<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit;
}

$servername = 'localhost';
$username = 'root';
$password = '';
$dbname = 'restaurants';

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $erreur="Échec de la connexion : " . $e->getMessage();
    
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['plat_id'])) {
    try {
        $plat_id = $_POST['plat_id'];
        $disponible = isset($_POST['disponible']) ? 1 : 0;
        
        $stmt = $conn->prepare("UPDATE plats SET disponible = :disponible WHERE id = :id");
        $stmt->bindParam(':disponible', $disponible);
        $stmt->bindParam(':id', $plat_id);
        $stmt->execute();
        
        header("Location: dashboard_admin.php");
        exit;
    } catch (PDOException $e) {
        die("Erreur lors de la mise à jour du plat: " . $e->getMessage());
    }
} else {
    header("Location: dashboard_admin.php");
    exit;
}
?>