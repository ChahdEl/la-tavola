<?php
session_start();
$servername = 'localhost';
$username = 'root';
$password = '';
$dbname = 'restaurants';

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(" Erreur de connexion : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Confirmation de commande</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-image: url('images/pgFlou.png'); 
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            padding: 40px;
            color: #333;
        }
        .recu {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: auto;
            margin-top:100px;
            padding: 30px;
        }
        h2 {
            color: #f44336;
            text-align: center;
        }
        p, li {
            font-size: 16px;
            margin-bottom: 10px;
        }
        ul {
            list-style-type: square;
            padding-left: 20px;
        }
        .total {
            font-weight: bold;
            font-size: 18px;
            color: #222;
            margin-top: 20px;
        }
        .btn {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            padding: 10px 20px;
            background-color: #3d6660;
            color: white;
            border-radius: 5px;
            transition: 0.3s ease;
        }
        .btn:hover {
            background-color:rgb(43, 72, 68);
        }
    </style>
</head>
<body>

<?php
if (isset($_POST['valider_commande'])) {
    $nom = $_POST['nom'];
    $adresse = $_POST['adresse'];
    $telephone = $_POST['telephone'];
    $panier = $_SESSION['panier'];

    try {
        // 1. Ajouter le client
        $stmt = $conn->prepare("INSERT INTO clients (nom, adresse, telephone) VALUES (?, ?, ?)");
        $stmt->execute([$nom, $adresse, $telephone]);
        $client_id = $conn->lastInsertId();

        // 2. Ajouter la commande
        $stmt = $conn->prepare("INSERT INTO commandes (client_id, date_commande, adress) VALUES (?, NOW(), ?)");
        $stmt->execute([$client_id, $adresse]); 

        $commande_id = $conn->lastInsertId();

        // 3. Ajouter chaque plat
        $stmt = $conn->prepare("INSERT INTO ligne_commande (commande_id, plat_id, quantite) VALUES (?, ?, ?)");
        foreach ($panier as $plat) {
            $stmt->execute([$commande_id, $plat['id'], $plat['quantite']]);
        }
        
        // 4. Affichage
        echo "<div class='recu'>";
        echo "<h2>Merci pour votre commande !</h2>";
        echo "<p><strong>Nom :</strong> $nom</p>";
        echo "<p><strong>Adresse :</strong> $adresse</p>";
        echo "<p><strong>Téléphone :</strong> $telephone</p>";
        echo "<h3>Détails de la commande :</h3>";
        echo "<ul>";
        $total = 0;
        foreach ($panier as $plat) {
            $nomPlat = htmlspecialchars($plat['nom']);
            $prix = $plat['prix'];
            $quantite = $plat['quantite'];
            $sous_total = $prix * $quantite;
            $total += $sous_total;
            echo "<li>$nomPlat x $quantite = $sous_total DH</li>";
        }
        echo "</ul>";
        echo "<p class='total'>Total : $total DH</p>";
        echo "<a href='../projet.php' class='btn'>Retour à l'accueil</a>";
        echo "<a href='#' onclick='window.print()' class='btn' style='margin-left:10px;'>Imprimer</a>";
        echo "</div>";

        unset($_SESSION['panier']);

    } catch (PDOException $e) {
        echo "<p>❌ Erreur : " . $e->getMessage() . "</p>";
    }
}
?>

</body>
</html>
