<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="client/style.css">
    <title>La tavola</title>
</head>
<body>
    
    <div class="info">
        <p>
        <i class="fas fa-clock"></i>7j/7: 12h:22h | <i class="fas fa-phone"></i> 05.35.60.80.40 | <i class="fas fa-map-marker-alt"></i> Mag 1, Lot Hajar, Ain Amir ( en face clinique arrazi )
        </p>
        <div class="reseauSociaux">
             <a href="https://www.instagram.com/cappuccinofes/" id="instagram"><i class="fab fa-instagram"></i></a>   |
          <a href="https://www.instagram.com/duplexsteakhouse/" id="Facebook"><i class="fab fa-facebook"></i></a>     |
            <a href="https://www.instagram.com/duplexsteakhouse/" id="whatsap"><i class="fab fa-whatsapp"></i></a>
        </div>  
    </div>
    <div class="menu-container">
        <img src="client/images\logo.png" alt="images-removebg-preview.png">
        <a href="https://drive.google.com/file/d/1Y8r1DQLjJXhFySvNwzhqvceUiSiVONfU/view?fbclid=PAZXh0bgNhZW0CMTEAAaZCJLOyGHd-2HstLTprkteqfsq4W52nqERgsxZdGKT-nzeHcop0Prbscf0_aem_7ZUllV3Aguq520tVEfWnSw" id="carte"> <i class="fas fa-bars menu-icon"></i>Menu</a>
        <form action="client/reservation.php" method="POST">
            <button type="submit" name="page"><i class="fas fa-calendar calendar-icon"></i> Réserver</button>
        </form>
        <form action="client/commande.php" method="POST">
            <button type="submit" name="page" id="commande"><i class="fas fa-shopping-cart"></i>commander </button>
        </form>
        <form action="admin/login_admin.php" method="POST">
            <button type="submit" name="page" id="gerant">👤 gerant </button>
        </form>
    </div>
   
    <div class="gallery-container">
        
        <div class="focus-gallery" id="gallery">
            <div class="gallery-item ">
                <img src="images/trio.jpeg" alt="Spécialité maison">
                <div class="image-caption">Notre trio</div>
            </div>
            <div class="gallery-item ">
                <img src="images/burger.jpeg" alt="Dessert signature">
                <div class="image-caption">Notre burger</div>
            </div>
            <div class="gallery-item active">
                <img src="images/sandwich.jpeg" alt="Notre salle">
                <div class="image-caption">Notre Sandwich </div>
            </div>
            <div class="gallery-item">
                <img src="images/service.jpeg" alt="Café gourmand">
                <div class="image-caption">Café gourmand</div>
            </div>
            <div class="gallery-item">
                <img src="images/etage1.jpeg" alt="Notre terrasse">
                <div class="image-caption">Notre terrasse</div>
            </div>
        </div>
        
        <div class="gallery-nav">
            <button class="nav-button" onclick="prevSlide()">‹</button>
            <button class="nav-button" onclick="nextSlide()">›</button>
        </div>
    </div>
    <h3 id="bienvenue">Depuis toujours, la bonne cuisine rassemble. <br>Chez Tavola, venez partager bien plus qu’un repas.</h3>


    <footer>
        <div class="footer-container">
            <div class="footer-section">
                <h3>À propos</h3>
                <p>Depuis 2022, Cappuccino Fès vous accueille dans un cadre chaleureux pour partager notre passion pour la gastronomie.</p>
                <div class="social-icons">
                    <a href="https://www.instagram.com/cappuccinofes/"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-tripadvisor"></i></a>
                    <a href="#"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
            <div class="footer-section">
                <h3>Contact</h3>
                <p><i class="fas fa-map-marker-alt"></i> Mag 1, Lot Hajar, Ain Amir</p>
                <p><i class="fas fa-phone"></i> 05.35.60.80.40</p>
                <p><i class="fas fa-envelope"></i> contact@cappuccinofes.com</p>
                <p><i class="fas fa-clock"></i> Ouvert 7j/7 de 12h à 22h</p>
            </div>
            <div class="footer-section">
                <h3>Liens rapides</h3>
                <a href="#">Accueil</a>
                <a href="#">Notre menu</a>
                <a href="#">Galerie</a>
                <a href="#">Réservations</a>
                <a href="#">Événements</a>
            </div>
            <div class="footer-section">
                <h3>Newsletter</h3>
                <p>Abonnez-vous pour recevoir nos offres spéciales :</p>
                <form>
                    <input type="email" placeholder="Votre email" style="
                        padding: 10px;
                        width: 100%;
                        margin-bottom: 10px;
                        border: none;
                        border-radius: 4px;
                    ">
                    <button type="submit" style="
                        background-color: #F5F5DC;
                        color: #3d6660;
                        border: none;
                        padding: 10px 20px;
                        border-radius: 4px;
                        cursor: pointer;
                        font-weight: bold;
                    ">S'abonner</button>
                </form>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; 2023 Cappuccino Fès. Tous droits réservés. | <a href="#">Mentions légales</a> | <a href="#">Politique de confidentialité</a></p>
        </div>
    </footer>

    <script>
        const gallery = document.getElementById('gallery');
        const items = document.querySelectorAll('.gallery-item');
        let currentIndex = 0;
            function updateGallery() {
        const item = items[currentIndex];
        const galleryCenter = gallery.offsetWidth / 2;
        const itemCenter = item.offsetLeft + item.offsetWidth / 2;
        const newScrollLeft = itemCenter - galleryCenter;

        gallery.scrollTo({
            left: newScrollLeft,
            behavior: 'smooth'
        });

        items.forEach((item, index) => {
            item.classList.toggle('active', index === currentIndex);
        });
}
        function nextSlide() {
            currentIndex = (currentIndex + 1) % items.length;
            updateGallery();
        }
        function prevSlide() {
            currentIndex = (currentIndex - 1 + items.length) % items.length;
            updateGallery();
        }
        
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowRight') nextSlide();
            if (e.key === 'ArrowLeft') prevSlide();
        });
        updateGallery();
    </script>
</body>
</html>