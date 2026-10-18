<?php
/**
 * Tiano Hotel & Suites - Modular Navigation Bar
 */
$active_page = isset($active_page) ? $active_page : 'home';
?>
<!-- Navbar Area -->
<div class="navbar-area">
    
    <!-- Mobile Navigation -->
    <div class="mobile-nav">
        <a href="index.php" class="logo">
            <img src="assets/img/logos/logo-1.svg" class="logo-one" alt="Tiano Hotel Logo">
            <img src="assets/img/logos/footer-logo1.svg" class="logo-two" alt="Tiano Hotel Logo">
        </a>
    </div>

    <!-- Main Desktop Navigation -->
    <div class="main-nav nav-three">
        <div class="container">
            <nav class="navbar navbar-expand-md navbar-light">
                
                <!-- Brand Logo -->
                <a class="navbar-brand" href="index.php">
                    <img src="assets/img/logos/logo-1.svg" class="logo-one" alt="Tiano Hotel Logo">
                    <img src="assets/img/logos/footer-logo2.svg" class="logo-two" alt="Tiano Hotel Logo">
                </a>

                <!-- Collapsible Menu Content -->
                <div class="collapse navbar-collapse mean-menu" id="navbarSupportedContent">
                    <ul class="navbar-nav m-auto">
                        
                        <!-- Home -->
                        <li class="nav-item">
                            <a href="index.php" class="nav-link <?php echo ($active_page === 'home') ? 'active' : ''; ?>">Home</a>
                        </li>
                        
                        <!-- About -->
                        <li class="nav-item">
                            <a href="about.php" class="nav-link <?php echo ($active_page === 'about') ? 'active' : ''; ?>">About</a>
                        </li>

                        <!-- Rooms -->
                        <li class="nav-item">
                            <a href="rooms.php" class="nav-link <?php echo ($active_page === 'rooms') ? 'active' : ''; ?>">Rooms</a>
                        </li>

                        <!-- Gallery -->
                        <li class="nav-item">
                            <a href="gallery.php" class="nav-link <?php echo ($active_page === 'gallery') ? 'active' : ''; ?>">Gallery</a>
                        </li>

                        <!-- Services -->
                        <li class="nav-item">
                            <a href="services.php" class="nav-link <?php echo ($active_page === 'services') ? 'active' : ''; ?>">Services</a>
                        </li>
                        
                        <!-- Blog -->
                        <li class="nav-item">
                            <a href="blog.php" class="nav-link <?php echo ($active_page === 'blog') ? 'active' : ''; ?>">Blog</a>
                        </li>

                        <!-- Contact -->
                        <li class="nav-item">
                            <a href="contact.php" class="nav-link <?php echo ($active_page === 'contact') ? 'active' : ''; ?>">Contact</a>
                        </li>
                        
                        <!-- Book Now Button (in mobile mean-menu) -->
                        <li class="nav-item-btn">
                            <a href="booking.php" class="default-btn btn-bg-three">Book Now</a>
                        </li>
                        
                    </ul>

                    <!-- Additional Book Now Button (Desktop header end) -->
                    <div class="nav-btn">
                        <a href="booking.php" class="default-btn btn-bg-three">
                            Book Now
                        </a>
                    </div>
                    
                </div>
            </nav>
        </div>
    </div>
</div>
