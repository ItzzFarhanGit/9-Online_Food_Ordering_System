<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | Delight Dinning</title>
    <link rel="stylesheet" href="about.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body>

    <?php include 'header.php'; ?>

    <!-- HERO -->
    <section class="about-hero">
        <div class="container">
            <h1>About Delight Dinning</h1>
            <p>Serving happiness with every bite 🍽️</p>
        </div>
    </section>

    <!-- STORY -->
    <section class="story">
        <div class="container story-grid">
            <div class="story-img">
                <img src="delight Rest.png" alt="Restaurant">
            </div>
            <div class="story-text">
                <h2>Our Story</h2>
                <p>
                    Delight Dinning started with a simple idea — to bring fresh, delicious,
                    and affordable meals straight to your doorstep. What began as a small
                    kitchen has now grown into a trusted food delivery brand loved by
                    hundreds of customers.
                </p>
                <p>
                    We believe food is not just about taste, but about experience, emotion,
                    and connection.
                </p>
            </div>
        </div>
    </section>

    <!-- MISSION -->
    <section class="mission">
        <div class="container">
            <h2>Our Mission</h2>
            <p>
                To deliver high-quality meals with speed, care, and consistency while
                creating unforgettable dining experiences for every customer.
            </p>
        </div>
    </section>

    <!-- CHEFS -->
    <section class="chefs">
        <div class="container">
            <h2>Meet Our Chefs</h2>
            <div class="chef-grid">
                <div class="chef-card">
                    <img src="chef2.jpg" alt="Chef Aruna">
                    <h3>Chef Aruna</h3>
                    <p>Head Chef</p>
                </div>
                <div class="chef-card">
                    <img src="chef1.jpg" alt="Chef Nimal">
                    <h3>Chef Nimal</h3>
                    <p>Italian Specialist</p>
                </div>
                <div class="chef-card">
                    <img src="chef3.jpg" alt="Chef Sara">
                    <h3>Chef Sara</h3>
                    <p>Dessert Expert</p>
                </div>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>

</body>
</html>
