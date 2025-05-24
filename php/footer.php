<?php
/**
 * Footer template for Compare IT
 * Include this in your pages with: require_once 'footer.php';
 */
?>
<footer class="site-footer">
    <div class="footer-wave"></div>
    
    <div class="footer-content">
        <div class="footer-brand">
            <img src="../img/logo.jpg" alt="Compare IT Logo" class="footer-logo">
            <span class="footer-title">Compare IT</span>
            <p class="footer-tagline">Plug into the best deals</p>
        </div>
        
        <div class="footer-links">
            <div class="footer-column">
                <h3>Shop</h3>
                <ul>
                    <li><a href="products.php?category=keyboards">Keyboards</a></li>
                    <li><a href="products.php?category=mice">Mice</a></li>
                    <li><a href="products.php?category=headphones">Audio</a></li>
                    <li><a href="products.php?category=monitors">Gaming</a></li>
                </ul>
            </div>
            
           
            
            <div class="footer-column">
                <h3>Connect</h3>
                <div class="social-links">
                    <a href="#" class="social-icon" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-icon" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-icon" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-icon" aria-label="Reddit"><i class="fab fa-reddit-alien"></i></a>
                </div>
                <div class="newsletter">
                    <p>Get the best deals in your inbox:</p>
                    <form class="newsletter-form">
                        <input type="email" placeholder="Your email" required>
                        <button type="submit">Subscribe</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div class="footer-bottom">
        <p>&copy; <?php echo date('Y'); ?> Compare IT. All prices compared, all rights reserved.</p>
        <div class="footer-legal">
            <a href="#">Privacy Policy</a>
            <a href="#">Terms of Service</a>
            <a href="#">Cookie Policy</a>
        </div>
    </div>
    
    <div class="footer-scroll-top">
        <button id="scrollToTop" aria-label="Scroll to top">
            <i class="fas fa-chevron-up"></i>
        </button>
    </div>
</footer>

<link rel="stylesheet" href="../css/footer.css">
<script src="../js/footer.js"></script>