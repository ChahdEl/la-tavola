<?php
session_start();

// Initialisation du panier
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
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
try {
    $stmt = $conn->query("SELECT * FROM plats WHERE disponible = 1 ORDER BY nom");
    $plats = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
   $erreur="Erreur lors de la récupération des plats : " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ajouter'])) {
        $plat_id = $_POST['plat_id'];
        $quantite = intval($_POST['quantite'] ?? 1);

        $existe = false;
        foreach ($_SESSION['panier'] as &$item) {
            if ($item['id'] == $plat_id) {
                $item['quantite'] += $quantite;
                $existe = true;
                break;
            }
        }
        unset($item); // Important pour éviter des bugs de référence

        if (!$existe) {
            $stmt = $conn->prepare("SELECT * FROM plats WHERE id = :id AND disponible = 1");
            $stmt->execute(['id' => $plat_id]);
            $plat = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($plat) {
                $_SESSION['panier'][] = [
                    'id' => $plat['id'],
                    'nom' => $plat['nom'],
                    'prix' => $plat['prix'],
                    'quantite' => $quantite,
                    'image' => $plat['image']
                ];
            }
        }
    }

    elseif (isset($_POST['supprimer'])) {
        $plat_id = $_POST['plat_id'];
        foreach ($_SESSION['panier'] as $key => $item) {
            if ($item['id'] == $plat_id) {
                unset($_SESSION['panier'][$key]);
                break;
            }
        }
        $_SESSION['panier'] = array_values($_SESSION['panier']);
    }

    elseif (isset($_POST['vider'])) {
        $_SESSION['panier'] = [];
    }

    elseif (isset($_POST['commander'])) {
        header("Location: confirmation.php");
        exit();
    }
}


// Calcul du total
$total = 0;
foreach ($_SESSION['panier'] as $item) {
    $total += $item['prix'] * $item['quantite'];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commander - Cappuccino Fès</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style2.css">
</head>
<body>
    <div class="info">
        <p>
            <i class="fas fa-clock"></i>7j/7: 12h:22h | 
            <i class="fas fa-phone"></i> 05.35.60.80.40 | 
            <i class="fas fa-map-marker-alt"></i> Mag 1, Lot Hajar, Ain Amir (en face clinique arrazi)
        </p>
        <div class="reseauSociaux">
            <a href="https://www.instagram.com/cappuccinofes/" id="instagram"><i class="fab fa-instagram"></i></a> |
            <a href="https://www.facebook.com/" id="Facebook"><i class="fab fa-facebook"></i></a> |
            <a href="https://wa.me/212635608040" id="whatsap"><i class="fab fa-whatsapp"></i></a>
        </div>  
    </div>
    
    <div class="container">
        <h1>Notre Menu</h1>
        
        <div class="menu-grid">
            <?php foreach ($plats as $plat): ?>
            <div class="plat-card">
                <div class="card-header" style="background-image: url('<?= htmlspecialchars($plat['image']) ?>')">
                    <span class="card-price"><?= number_format($plat['prix'], 2) ?> DH</span>
                </div>
                <div class="card-body">
                    <h3 class="card-title"><?= htmlspecialchars($plat['nom']) ?></h3>
                    <p class="card-text"><?= htmlspecialchars($plat['description']) ?></p>
                </div>
                <div class="card-footer">
                    <form method="post">
                        <input type="hidden" name="plat_id" value="<?= $plat['id'] ?>">
                        <input type="number" name="quantite" value="1" min="1" class="quantity-input">
                        <button type="submit" name="ajouter" class="btn-add">
                            <i class="fas fa-plus"></i> Ajouter
                        </button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="panier-container" id="panier">
        <div class="panier-header">
            <h2>Votre Panier</h2>
            <button class="close-panier">&times;</button>
        </div>
        
        <div class="panier-items">
            <?php if (empty($_SESSION['panier'])): ?>
                <p>Votre panier est vide</p>
            <?php else: ?>
                <?php foreach ($_SESSION['panier'] as $item): ?>
                <div class="panier-item">
                    <div class="item-details">
                        <strong><?= htmlspecialchars($item['nom']) ?></strong>
                        <div><?= $item['quantite'] ?> x <?= number_format($item['prix'], 2) ?> DH</div>
                    </div>
                    <form method="post" class="item-actions">
                        <input type="hidden" name="plat_id" value="<?= $item['id'] ?>">
                        <button type="submit" name="supprimer" class="btn-remove">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="panier-total">
            Total: <?= number_format($total, 2) ?> DH
        </div>
        
        <?php if (!empty($_SESSION['panier'])): ?>
        <form method="post">
            
            <button type="submit" name="confirmer_commande" formaction="confirmation.php" style="background:rgb(93, 212, 57);" class="btn-commander">
        Passer la commande
    </button>
    <button type="submit" name="vider" class="btn-commander" style="background: #e74c3c; margin-bottom: 10px;">
                Vider le panier
            </button>
        </form>
        <?php endif; ?>
    </div>
    
    <!-- Bouton panier flottant -->
    <div class="btn-panier" id="btn-panier">
        <i class="fas fa-shopping-cart"></i>
        <span class="badge"><?= array_reduce($_SESSION['panier'], function($sum, $item) { return $sum + $item['quantite']; }, 0) ?></span>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const panierContainer = document.getElementById('panier');
            const btnPanier = document.getElementById('btn-panier');
            const closePanier = document.querySelector('.close-panier');
            
            btnPanier.addEventListener('click', function() {
                panierContainer.classList.add('open');
            });
            
            closePanier.addEventListener('click', function() {
                panierContainer.classList.remove('open');
            });
            
            const dateInput = document.getElementById('date-reservation');
            if (dateInput) {
                const today = new Date().toISOString().split('T')[0];
                dateInput.min = today;
            }
        });
    </script>
</body>
</html>