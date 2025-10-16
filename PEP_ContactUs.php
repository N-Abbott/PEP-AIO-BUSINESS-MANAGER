<!DOCTYPE html>
<html lang="en">
<head>
<title>Contact Us - Petrongolo Evergreen Plantation</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;700&family=Roboto:wght@400;700&display=swap">
<style>
html, body {
  width: 100%;
  overflow-x: hidden;
  scroll-behavior: smooth;
}
body {
font-family: 'Roboto', sans-serif;
background-color: #f8f9fa;
color: #333;
}
.banner {
width: 100%;
height: auto;
}
.navigation-bar {
background-color: #2c5530;
padding: 10px 0;
}
.navigation-bar .nav-link {
color: #fff !important;
font-weight: 700;
margin: 0 20px;
text-decoration: underline;
}
.navigation-bar .tree-divider {
height: 20px;
width: 20px;
border-radius: 50%;
margin: 0 10px;
vertical-align: middle;
}
.section-title {
text-align: center;
margin-bottom: 40px;
color: #2c5530;
font-family: 'Oswald', sans-serif;
font-weight: 700;
}
h1, h2, h3, h4, .card-title {
font-family: 'Oswald', sans-serif;
font-weight: 700;
color: #2c5530;
}
.card {
background-color: #fff;
color: #333;
border: 1px solid #e9ecef;
}
.bg-mid {
background-color: #e9ecef;
}
footer {
background-color: #2c5530;
color: #fff;
padding: 20px 0;
}
.animate-bottom {
opacity: 0;
transform: translateY(100px);
transition: opacity 1.5s ease-out, transform 1.5s ease-out;
}
.animate-bottom.visible {
opacity: 1;
transform: translateY(0);
}
.animate-left {
opacity: 0;
transform: translateX(-100px);
transition: opacity 1.5s ease-out, transform 1.5s ease-out;
}
.animate-left.visible {
opacity: 1;
transform: translateX(0);
}
.animate-zoom {
opacity: 0;
transform: scale(0.8);
transition: opacity 1.5s ease-out, transform 1.5s ease-out;
}
.animate-zoom.visible {
opacity: 1;
transform: scale(1);
}
.animate-right {
opacity: 0;
transform: translateX(100px);
transition: opacity 1.5s ease-out, transform 1.5s ease-out;
}
.animate-right.visible {
opacity: 1;
transform: translateX(0);
}
form .form-control {
background-color: #fff;
color: #333;
border: 1px solid #e9ecef;
}
.login-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  z-index: 10;
  background-color: #2c5530;
  color: #fff;
  border: none;
}
</style>
</head>
<body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<img src="Banner_Logo.png" class="img-fluid banner" alt="Banner Logo">
<div class="navigation-bar">
<div class="container">
<div class="d-flex justify-content-between align-items-center">
<a class="nav-link" href="PEP_Main.html">Home</a>
<img src="Tree.png" class="tree-divider" alt="Tree Divider">
<a class="nav-link" href="PEP_AboutUs.html">About</a>
<img src="Tree.png" class="tree-divider" alt="Tree Divider">
<a class="nav-link" href="PEP_Catalog.html">Catalog</a>
<img src="Tree.png" class="tree-divider" alt="Tree Divider">
<a class="nav-link" href="PEP_Reviews.html">Reviews</a>
<img src="Tree.png" class="tree-divider" alt="Tree Divider">
<a class="nav-link" href="PEP_VisitUs.html">Visit Us</a>
</div>
</div>
</div>
<section id="contact-intro" class="py-5 animate-bottom">
<div class="container">
<h1 class="section-title">Contact Us</h1>
<p>Have questions about our trees, hours, or anything else? Reach out to us! We're here to help make your holiday season merry and bright.</p>
</div>
</section>
<section id="contact-info" class="py-5 bg-mid animate-left">
<div class="container text-center">
<h2 class="section-title">Our Contact Details</h2>
<p><strong>Address:</strong> 7541 Weymouth Rd, Hammonton, NJ 08037</p>
<p><strong>Phone:</strong> (609) 567-0336</p>
<p><strong>Email:</strong> info@petrongolo.com</p>
<p><strong>Facebook:</strong> <a href="https://www.facebook.com/p/Petrongolo-Evergreen-Plantation-100064604442455/" target="_blank">Visit our Facebook Page</a></p>
<p>Note: Our phone is a home line, so for off-hour inquiries, please use the form below or check our website for details.</p>
</div>
</section>
<section id="contact-form" class="py-5 animate-zoom">
<div class="container">
<h2 class="section-title">Send Us a Message</h2>
<form>
<div class="mb-3">
<label for="name" class="form-label">Name</label>
<input type="text" class="form-control" id="name" placeholder="Your Name">
</div>
<div class="mb-3">
<label for="email" class="form-label">Email</label>
<input type="email" class="form-control" id="email" placeholder="Your Email">
</div>
<div class="mb-3">
<label for="message" class="form-label">Message</label>
<textarea class="form-control" id="message" rows="5" placeholder="Your Message"></textarea>
</div>
<button type="submit" class="btn btn-primary">Send Message</button>
</form>
</div>
</section>
<section id="location-map" class="py-5 bg-mid animate-right">
<div class="container">
<h2 class="section-title">Find Us</h2>
<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3074.663747983165!2d-74.739235684646!3d39.641058279464!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c13a4a5b5e4b0b%3A0x9f2c4e5d6a7b8c9d!2s7541%20Weymouth%20Rd%2C%20Hammonton%2C%20NJ%2008037!5e0!3m2!1sen!2sus!4v1728321600000!5m2!1sen!2sus" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" style="width: 100%; height: 450px; border: 0px;"></iframe>
</div>
</section>
<footer class="text-center">
<p><a href="https://www.facebook.com/p/Petrongolo-Evergreen-Plantation-100064604442455/" target="_blank" style="color: #fff;">Facebook</a> | Email: info@petrongolo.com | Phone: (609) 567-0336</p>
<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3074.663747983165!2d-74.739235684646!3d39.641058279464!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c13a4a5b5e4b0b%3A0x9f2c4e5d6a7b8c9d!2s7541%20Weymouth%20Rd%2C%20Hammonton%2C%20NJ%2008037!5e0!3m2!1sen!2sus!4v1728321600000!5m2!1sen!2sus" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
<p>&copy; 2025 Petrongolo Evergreen Plantation. All rights reserved.</p>
<div>
<a href="index.html" style="color: #fff;">Home</a> | <a href="PEP_AboutUs.html" style="color: #fff;">About</a> | <a href="PEP_Catalog.html" style="color: #fff;">Catalog</a> | <a href="PEP_Reviews.html" style="color: #fff;">Reviews</a> | <a href="PEP_VisitUs.html" style="color: #fff;">Visit Us</a> | <a href="#" style="color: #fff;">Contact</a>
</div>
</footer>
<script>
// Scroll animation observer
const sections = document.querySelectorAll('.animate-bottom, .animate-left, .animate-zoom, .animate-right');
const options = {
threshold: 0.1
};
const observer = new IntersectionObserver((entries, observer) => {
entries.forEach(entry => {
if (entry.isIntersecting) {
entry.target.classList.add('visible');
    } else {
entry.target.classList.remove('visible');
    }
  });
}, options);
sections.forEach(section => {
observer.observe(section);
});
</script>
</body>
</html>