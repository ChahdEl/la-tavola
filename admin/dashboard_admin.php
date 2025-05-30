<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit;
}

// Configuration de la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "restaurants";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Échec de la connexion : " . $e->getMessage());
}

// Récupération des données
try {
    // Réservations
    $stmt = $conn->prepare("SELECT * FROM reservations ORDER BY date_reservation DESC, heure DESC");
    $stmt->execute();
    $reservations = $stmt->fetchAll();
    $total_reservations = count($reservations);

    // Commandes
    $total_commandes = $conn->query("SELECT COUNT(*) FROM commandes")->fetchColumn();
    $commandes = $conn->query("
        SELECT c.id, c.date_commande, c.statut, c.adress,
               cl.nom AS client_nom, cl.telephone,
               SUM(lc.quantite * p.prix) AS montant_total,
               GROUP_CONCAT(CONCAT(lc.quantite, ' × ', p.description) SEPARATOR ' | ') AS details_plats
        FROM commandes c
        JOIN clients cl ON c.client_id = cl.id
        JOIN ligne_commande lc ON c.id = lc.commande_id
        JOIN plats p ON lc.plat_id = p.id
        GROUP BY c.id, c.date_commande, c.statut, cl.nom, cl.telephone
        ORDER BY c.date_commande DESC
        LIMIT 20
    ")->fetchAll();

    // Plats populaires
    $plats = $conn->query("
        SELECT p.*, 
               COUNT(lc.id) as nombre_commandes,
               SUM(lc.quantite) as quantite_vendue
        FROM plats p
        LEFT JOIN ligne_commande lc ON p.id = lc.plat_id
        GROUP BY p.id
        ORDER BY quantite_vendue DESC
    ")->fetchAll();

    // Statistiques
    $stats = $conn->query("
        SELECT 
            (SELECT COUNT(*) FROM clients) as total_clients,
            (SELECT COUNT(*) FROM commandes) as total_commandes,
            (SELECT SUM(lc.quantite * p.prix)
             FROM ligne_commande lc
             JOIN plats p ON lc.plat_id = p.id
             JOIN commandes c ON lc.commande_id = c.id
             WHERE DATE(c.date_commande) = CURDATE()
            ) as chiffre_affaires_jour,
            (SELECT COUNT(*) FROM reservations) as total_reservations,
            (SELECT places_libres 
            FROM places_par_jour 
            WHERE date_jour = CURDATE()
            LIMIT 1) as places_libres,
            (SELECT nom FROM admins LIMIT 1) as nom_gerant
    ")->fetch();

    // Places par jour
    $places_par_jour = $conn->query("
        SELECT * FROM places_par_jour 
        WHERE date_jour >= CURDATE()
        ORDER BY date_jour ASC
    ")->fetchAll();

} catch (PDOException $e) {
    die("Erreur lors de la récupération des données: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrateur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .stat-card { transition: transform 0.3s; }
        .stat-card:hover { transform: translateY(-5px); }
        .plat-img { width: 50px; height: 50px; object-fit: cover; border-radius: 4px; }
        .cursor-pointer { cursor: pointer; }
        .form-section {
            background-color: #fff;
            border-radius: 0.375rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            padding: 1.5rem;
            margin-bottom: 2rem;}
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Tableau de Bord</h1>
            <div class="text-end">
                <span class="badge bg-primary">
                    <i class="bi bi-person"></i> Gérant: <?= htmlspecialchars($stats['nom_gerant']) ?>
                </span>
            </div>
        </div>

        <div class="row mb-4">
            
            <div class="col-md-4">
                <div class="card stat-card shadow-sm border-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-muted mb-2">Commandes</h6>
                                <h3><?= $stats['total_commandes'] ?></h3>
                            </div>
                            <span class="badge bg-success rounded-circle p-3">
                                <i class="bi bi-cart fs-4"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card stat-card shadow-sm border-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-muted mb-2">Réservations</h6>
                                <h3><?= $stats['total_reservations'] ?></h3>
                            </div>
                            <span class="badge bg-warning rounded-circle p-3">
                                <i class="bi bi-calendar fs-4"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card stat-card shadow-sm border-info">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-muted mb-2">Chiffre d'affaires journalier</h6>
                                <h3><?= number_format($stats['chiffre_affaires_jour'], 2) ?> DH</h3>
                            </div>
                            <span class="badge bg-info rounded-circle p-3">
                            <i class="bi bi-cash-stack fs-4"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Gestion des places</h5>
                <span class="badge bg-primary"><?= $stats['places_libres'] ?> places disponibles</span>
            </div>
            <div class="card-body">
                <form class="row g-3" method="POST" action="update_places.php">
                    <div class="col-md-6">
                        <label class="form-label">Nouveau nombre de places</label>
                        <input type="date" class="form-control mb-2" name="date_jour" value="<?= date('Y-m-d') ?>" required>
<input type="number" class="form-control" name="places_libres" value="<?= $stats['places_libres'] ?>" min="0" required>

                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Mettre à jour
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Dernières réservations (<?= $reservations[0]['total_reservations'] ?? 0 ?>)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Client</th>
                                <th>email</th>
                                <th>phone</th>
                                <th>Date/Heure</th>
                                <th>Personnes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservations as $res): ?>
                            <tr class="cursor-pointer" onclick="window.location='reservation_detail.php?id=<?= $res['id'] ?>'">
                                <td><?= htmlspecialchars($res['nom']) ?></td>
                                <td>
                                    <?php if ($res['email']): ?>
                                        <a href="mailto:<?= htmlspecialchars($res['email']) ?>">
                                            <i class="bi bi-envelope"></i>
                                        </a>
                                    <?php endif; ?>
                                    </td>
                                    <td>
                                    <?php if ($res['phone']): ?>
                                        <a href="tel:<?= htmlspecialchars($res['phone']) ?>" class="ms-2">
                                            <i class="bi bi-telephone"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($res['date_reservation'] . ' ' . $res['heure'])) ?></td>
                                <td><?= $res['nbrPersonne'] ?></td>
                                
                               
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Dernières commandes (<?= $total_commandes ?>)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>N° Commande</th>
                                <th>Client</th>
                                <th>Plats commandés</th>
                                <th>Montant</th>
                                <th>Adresse</th>
                                <th>Date</th>
                                <th>Statut</th>
                                <th>Phone</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($commandes as $cmd): ?>
                            <tr class="cursor-pointer" >
                                <td>#<?= str_pad($cmd['id'], 4, '0', STR_PAD_LEFT) ?></td>
                                <td><?= htmlspecialchars($cmd['client_nom']) ?></td>
                                <td><?= htmlspecialchars($cmd['details_plats']) ?></td>
                                <td><?= number_format($cmd['montant_total']) ?>DH</td>
                                <td><?= htmlspecialchars($cmd['adress']) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($cmd['date_commande'])) ?></td>
                                <td>
  <form method="POST" action="update_statut_commande.php" class="d-flex align-items-center">
    <input type="hidden" name="commande_id" value="<?= $cmd['id'] ?>">
    <select name="statut" class="form-select form-select-sm me-2" onchange="this.form.submit()">
      <?php
      $statuts = ['en préparation', 'livrée', 'annulée','en route'];
      foreach ($statuts as $statutOption) {
          $selected = ($cmd['statut'] === $statutOption) ? 'selected' : '';
          echo "<option value=\"$statutOption\" $selected>" . ucfirst($statutOption) . "</option>";
      }
      ?>
    </select>
  </form>
</td>

                                <td>
                                    <?php if ($res['phone']): ?>
                                        <a href="tel:<?= htmlspecialchars($res['phone']) ?>" class="ms-2">
                                            <i class="bi bi-telephone"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    

        <div class="form-section">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h4 mb-0">Ajouter un plat</h2>
            </div>
            
            <form action="ajouter_plat.php" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="nom" class="form-label">Nom du plat <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nom" name="nom" required>
                        <div class="invalid-feedback">Veuillez saisir un nom.</div>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="prix" class="form-label">Prix <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="0.01" class="form-control" id="prix" name="prix" required>
                            <span class="input-group-text">DH</span>
                            <div class="invalid-feedback">Veuillez saisir un prix valide.</div>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="image" class="form-label">Image du plat</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                    </div>
                    
                    <div class="col-md-6 d-flex align-items-center">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="disponible" name="disponible" checked>
                            <label class="form-check-label" for="disponible">Disponible</label>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Ajouter le plat
                        </button>
                    </div>
                </div>
            </form>
        </div>
    <!-- Dans la partie HTML, après la section d'ajout de plat, ajoutez cette nouvelle section -->
<div class="card mb-4 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">Gestion des plats</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Image</th>
                        <th>Nom</th>
                        <th>Description</th>
                        <th>Prix</th>
                        <th>Quantité vendue</th>
                        <th>Disponible</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($plats as $plat): ?>
                    <tr>
                        <td>
                            <?php if (!empty($plat['image'])): ?>
                                <img src="<?= htmlspecialchars($plat['image']) ?>" class="plat-img" alt="<?= htmlspecialchars($plat['nom']) ?>">
                            <?php else: ?>
                                <span class="text-muted">Aucune image</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($plat['nom']) ?></td>
                        <td><?= htmlspecialchars($plat['description']) ?></td>
                        <td><?= number_format($plat['prix'], 2) ?> DH</td>
                        <td><?= $plat['quantite_vendue'] ?? 0 ?></td>
                        <td>
                            <form method="POST" action="toggle_plat_disponible.php" class="d-inline">
                                <input type="hidden" name="plat_id" value="<?= $plat['id'] ?>">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" 
                                           id="disponible_<?= $plat['id'] ?>" 
                                           name="disponible" 
                                           <?= $plat['disponible'] ? 'checked' : '' ?>
                                           onchange="this.form.submit()">
                                    <label class="form-check-label" for="disponible_<?= $plat['id'] ?>">
                                        <?= $plat['disponible'] ? 'Oui' : 'Non' ?>
                                    </label>
                                </div>
                            </form>
                        </td>
                        
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Script de validation Bootstrap
        (function () {
            'use strict'
            
            // Fetch all the forms we want to apply custom Bootstrap validation styles to
            var forms = document.querySelectorAll('.needs-validation')
            
            // Loop over them and prevent submission
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
        
        // Script pour rendre les lignes cliquables
        document.querySelectorAll('tr[onclick]').forEach(row => {
            row.style.cursor = 'pointer';
            row.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    window.location = row.getAttribute('onclick').replace("window.location=", "").replace(/'/g, "");
                }
            });
        });
    </script>
</body>
</html>