<?php
session_start();
if (empty($_SESSION['panier'])) {
    header("Location: index.php"); // Redirige s'il n'y a rien dans le panier
    exit();
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

        .form-container {
            max-width: 500px;
            margin: auto;
            margin-top:100px;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #3d6660;
        }

        input[type="text"], input[type="tel"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .btn-valider {
            width: 100%;
            padding: 12px;
            background-color: #3d6660;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 15px;
        }

        .btn-valider:hover {
            background-color:rgb(38, 64, 60);
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Confirmer votre commande</h2>
        <form action="valider_commande.php" method="post">
            <label>Nom complet</label>
            <input type="text" name="nom" required>

            <label>Adresse</label>
            <input type="text" name="adresse" required>

            <label>Numéro de téléphone</label>
            <input type="tel" name="telephone" required pattern="(06|07|05)[0-9]{8}" placeholder="06xxxxxxxx">

            <button type="submit" name ="valider_commande" class="btn-valider">Valider la commande</button>
        </form>
    </div>
</body>
</html>
