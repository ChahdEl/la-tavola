<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date_jour = $_POST['date_jour'];
    $places_libres = intval($_POST['places_libres']);

    $conn = new PDO("mysql:host=localhost;dbname=restaurants", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("
        INSERT INTO places_par_jour (date_jour, places_libres)
        VALUES (:date_jour, :places_libres)
        ON DUPLICATE KEY UPDATE places_libres = :places_libres
    ");

    $stmt->execute([
        ':date_jour' => $date_jour,
        ':places_libres' => $places_libres
    ]);

    header("Location: dashboard_admin.php");
    exit;
}
?>
