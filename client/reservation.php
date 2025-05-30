<?php
$message = "";
$erreur = [];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "restaurants";

$nom = "";
$email = "";
$telephone = "";
$date_reservation = "";
$heure_reservation = "";
$nombre_personne = "";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $erreur['connexion'] = "Échec de la connexion : " . $e->getMessage();
}


function validerNom($nom) {
    return preg_match("/^[a-zA-Z\s'-]+$/", $nom);
}

function validerEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validerTelephone($telephone) {
    return preg_match("/^(\+212|0)[5-7]\d{8}$/", $telephone);
}

function validerNombrePersonne($nombre_personne) {
    return is_numeric($nombre_personne) && $nombre_personne > 0 && $nombre_personne <= 20;
}


// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nom'])) {
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = $_POST['telephone'] ?? '';
    $date_reservation = $_POST['date_reservation'] ?? '';
    $heure_reservation = $_POST['heure_reservation'] ?? '';
    $nombre_personne = $_POST['nombre_personne'] ?? 0;

    // Validation
    if (!validerNom($nom)) {
        $erreur['nom'] = "Le nom est invalide. Seules les lettres et les espaces sont autorisés.";
    }
    
    if (!validerEmail($email)) {
        $erreur['email'] = "L'adresse email est invalide.";
    }
    
    if (!validerTelephone($telephone)) {
        $erreur['telephone'] = "Le numéro de téléphone est invalide. Format attendu : +212 6xxxxxxxx ou 06xxxxxxxx.";
    }
    
    if (!validerNombrePersonne($nombre_personne)) {
        $erreur['nombre_personne'] = "Veuillez entrer un nombre de personnes valide (1-20).";
    }


        $stmt = $conn->query("SELECT date_jour FROM places_par_jour WHERE places_libres > 0 AND date_jour >= CURDATE()");
        $dates_disponibles = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($erreur)) {
            $checkDateStmt = $conn->prepare("SELECT places_libres FROM places_par_jour WHERE date_jour = :date");
            $checkDateStmt->bindParam(':date', $date_reservation);
            $checkDateStmt->execute();
            $places = $checkDateStmt->fetchColumn();

            if ($places === false) {
                $erreur['places'] = "Les réservations ne sont pas encore ouvertes pour cette date.";
            } elseif ($nombre_personne > $places) {
                $erreur['places'] = "Il n'y a que $places places disponibles pour cette date.";
            } else {
                // Insertion de la réservation
                $sql = "INSERT INTO reservations (nom, email, phone, date_reservation, heure, nbrPersonne)
                        VALUES (:nom, :email, :telephone, :date_reservation, :heure_reservation, :nombre_personne)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':nom' => $nom,
                    ':email' => $email,
                    ':telephone' => $telephone,
                    ':date_reservation' => $date_reservation,
                    ':heure_reservation' => $heure_reservation,
                    ':nombre_personne' => $nombre_personne
                ]);

                $update = $conn->prepare("UPDATE places_par_jour 
                                        SET places_libres = places_libres - :nb 
                                        WHERE date_jour = :date");
                $update->execute([
                    ':nb' => $nombre_personne,
                    ':date' => $date_reservation
                ]);

                $message = "Réservation enregistrée avec succès !";
            }
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
    <title>Reservation</title>
</head>
<body>
    <section class="reservation">
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
            <h2>FORMULAIRE DE RÉSERVATION DE TABLE</h2>
            <form action="#" method="POST" class="reservation-form">
                <div class="form-group">
                    <label for="nom">Nom Complet <span class="required">*</span></label>
                    <input type="text" id="nom-complet" name="nom" value="<?php echo htmlspecialchars($nom); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email <span class="required">*</span></label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="ex: myname@gmail.com" required>
                </div>
                <div class="form-group">
                    <label for="telephone">Numéro de téléphone <span class="required">*</span></label>
                    <input type="tel" id="telephone" name="telephone" value="<?php echo htmlspecialchars($telephone); ?>" placeholder="+212 6xxxxxxxxx" required>
                </div>
                <div class="form-date-heure">
                    <div class="form-half-width">
                        <label for="date">Date de réservation <span class="required">*</span></label>
                        <input type="date" id="date" name="date_reservation" value="<?php echo htmlspecialchars($date_reservation); ?>" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="form-half-width">
                        <label for="heure">Heure de réservation <span class="required">*</span></label>
                        <input type="time" id="heure" name="heure_reservation" value="<?php echo htmlspecialchars($heure_reservation); ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="personne">le nombre des personnes <span class="required">*</span></label>
                    <input type="number" id="pers" name="nombre_personne" value="<?php echo htmlspecialchars($nombre_personne); ?>" required>
                </div>
                <button type="submit" class="submit-btn">Réserver</button>
            </form>
        </div>

        <?php if (!empty($message)): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
            <?php if (!empty($erreur['email'])): ?>
                <div class="error-message"><?php echo htmlspecialchars($erreur['email']); ?></div>
            <?php endif; ?>
            <?php if (!empty($erreur['places'])): ?>
                <div class="error-message"><?php echo htmlspecialchars($erreur['places']); ?></div>
            <?php endif; ?>
            <?php if (!empty($erreur['nombre_personne'])): ?>
                <div class="error-message"><?php echo htmlspecialchars($erreur['nombre_personne']); ?></div>
            <?php endif; ?>
            <?php if (!empty($erreur['date_heure'])): ?>
                <div class="error-message"><?php echo htmlspecialchars($erreur['date_heure']); ?></div>
            <?php endif; ?>
            <?php if (!empty($erreur['telephone'])): ?>
                <div class="error-message"><?php echo htmlspecialchars($erreur['telephone']); ?></div>
            <?php endif; ?>
            <?php if (!empty($erreur['nom'])): ?>
                <div class="error-message"><?php echo htmlspecialchars($erreur['nom']); ?></div>
            <?php endif; ?>
            <?php if (!empty($erreur['general'])): ?>
                <div class="error-message"><?php echo htmlspecialchars($erreur['general']); ?></div>
            <?php endif; ?>
        <?php endif; ?>
    </section>

    
</body>
</html>