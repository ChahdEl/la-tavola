<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $commande_id = $_POST['commande_id'] ?? null;
    $nouveau_statut = $_POST['statut'] ?? null;

    $statuts_valides = ['en préparation', 'livrée', 'annulée','en route'];
    if ($commande_id && in_array($nouveau_statut, $statuts_valides)) {
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "restaurants";

        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $conn->prepare("UPDATE commandes SET statut = :statut WHERE id = :id");
            $stmt->execute(['statut' => $nouveau_statut, 'id' => $commande_id]);

            header("Location: dashboard_admin.php"); // Remplace par le nom de ta page dashboard
            exit;

        } catch (PDOException $e) {
            die("Erreur lors de la mise à jour : " . $e->getMessage());
        }
    } else {
        die("Données invalides.");
    }
} else {
    header("Location: dashboard_admin.php");
    exit;
}
?>