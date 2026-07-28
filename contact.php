<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact | Delight Dinning</title>
    <link rel="stylesheet" href="contact.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <!-- Leaflet.js Map Styles -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
</head>
<body>

    <?php include 'header.php'; ?>

    <!-- HERO -->
    <section class="contact-hero">
        <div class="container">
            <h1>Contact Us</h1>
            <p>We are here to help you anytime 🍽️</p>
        </div>
    </section>

    <!-- CONTACT SECTION -->
    <section class="contact-section">
        <div class="container contact-grid">
            <!-- INFO -->
            <div class="contact-info">
                <h2>Get in Touch</h2>
                <p>Feel free to contact us for orders, support, or any inquiries.</p>
                <div class="info-box">
                    <i class="fa-solid fa-location-dot"></i>
                    <span>Nintavur, Sri Lanka</span>
                </div>
                <div class="info-box">
                    <i class="fa-solid fa-phone"></i>
                    <span>+94 76 978 8951</span>
                </div>
                <div class="info-box">
                    <i class="fa-solid fa-envelope"></i>
                    <span>info@delightdinning.com</span>
                </div>
                <div class="info-box">
                    <i class="fa-solid fa-clock"></i>
                    <span>24/7 Service Available</span>
                </div>
            </div>

            <!-- FORM -->
            <div class="contact-form">
                <h2>Send Message</h2>
                <form action="#" method="POST" onsubmit="alert('Thank you for contacting us! We will get back to you shortly.'); return false;">
                    <input type="text" placeholder="Your Name" required>
                    <input type="email" placeholder="Your Email" required>
                    <input type="text" placeholder="Subject" required>
                    <textarea rows="6" placeholder="Your Message" required></textarea>
                    <button type="submit">Send Message</button>
                </form>
            </div>
        </div>
    </section>

    <!-- MAP -->
    <section class="map">
        <div class="container">
            <h2>Our Location</h2>
            <div id="contact-map" style="height: 450px; border-radius: 20px; box-shadow: var(--shadow); margin-top: 20px; border: 4px solid #fff;"></div>
        </div>
    </section>

    <?php include 'footer.php'; ?>

    <!-- Leaflet.js Map Library -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        // Initialize Map centered at Nintavur, Sri Lanka
        var map = L.map('contact-map').setView([7.326596, 81.850236], 15);

        // Add OpenStreetMap Tile Layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Custom Marker Pin Style
        var customIcon = L.divIcon({
            html: '<i class="fa-solid fa-location-pin" style="color: #FF6B00; font-size: 36px; text-shadow: 0 0 5px rgba(0,0,0,0.3);"></i>',
            iconSize: [36, 36],
            iconAnchor: [18, 36],
            popupAnchor: [0, -36],
            className: 'custom-leaflet-icon'
        });

        // Add Marker
        L.marker([7.345, 81.842], {icon: customIcon}).addTo(map)
            .bindPopup('<div style="font-family: Poppins, sans-serif; font-size: 13px;"><b>Delight Dinning Restaurant</b><br>Nintavur, Sri Lanka.<br><a href="menu.php" style="color: #FF6B00; font-weight:600;">Order Online Now &rarr;</a></div>')
            .openPopup();
    </script>

</body>
</html>
