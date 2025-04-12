    </div><!-- /.container -->
    
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-info">
                    <h3>VetCare</h3>
                    <p>Connecting pet owners with qualified veterinarians</p>
                </div>
                <div class="footer-links">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="/vetcare/index.php">Home</a></li>
                        <li><a href="/vetcare/about.php">About Us</a></li>
                        <li><a href="/vetcare/services.php">Services</a></li>
                        <li><a href="/vetcare/contact.php">Contact Us</a></li>
                    </ul>
                </div>
            </div>
            <div class="copyright">
                &copy; <?php echo date('Y'); ?> VetCare. All rights reserved.
            </div>
        </div>
    </footer>
    
    <?php if (isset($extra_js)) echo $extra_js; ?>
</body>
</html> 