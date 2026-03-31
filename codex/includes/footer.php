<?php
if (!defined('SITE_NAME')) require_once __DIR__ . '/config.php';
$year = date('Y');
?>
<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">
      <!-- Brand -->
      <div class="footer-brand">
        <div class="nav-brand-icon" style="width:36px;height:36px;font-size:1.1rem">P</div>
        <span class="nav-brand-name"><?= htmlspecialchars(SITE_NAME) ?></span>
        <p class="footer-tagline">
          Find professionally managed rental units across premier locations. Simple, transparent, reliable.
        </p>
      </div>

      <!-- Browse -->
      <div>
        <p class="footer-heading">Browse</p>
        <ul class="footer-links">
          <li><a href="listings.php">All Listings</a></li>
          <li><a href="listings.php?rooms=0">Studios</a></li>
          <li><a href="listings.php?rooms=1">1 Bedroom</a></li>
          <li><a href="listings.php?rooms=2">2 Bedroom</a></li>
          <li><a href="listings.php?rooms=3">3+ Bedroom</a></li>
        </ul>
      </div>

      <!-- Company -->
      <div>
        <p class="footer-heading">Company</p>
        <ul class="footer-links">
          <li><a href="#">About</a></li>
          <li><a href="#">Contact</a></li>
          <li><a href="#">Privacy Policy</a></li>
          <li><a href="#">Terms of Service</a></li>
        </ul>
      </div>

      <!-- For Owners -->
      <div>
        <p class="footer-heading">For Owners</p>
        <ul class="footer-links">
          <li><a href="<?= APP_URL ?>" target="_blank" rel="noopener">Owner Dashboard</a></li>
          <li><a href="<?= APP_URL ?>/login.php" target="_blank" rel="noopener">Sign In</a></li>
        </ul>
        <div style="margin-top:20px;padding:16px;background:rgba(200,146,42,.1);border-radius:8px;border:1px solid rgba(200,146,42,.2)">
          <p style="font-size:.8rem;color:rgba(255,255,255,.6);line-height:1.6">
            Are you a property manager?<br>
            <a href="<?= APP_URL ?>" target="_blank" rel="noopener" style="color:#c8922a;font-weight:600;">Manage your portfolio →</a>
          </p>
        </div>
      </div>
    </div>

    <div class="footer-bottom">
      <p class="footer-copy">&copy; <?= $year ?> <?= htmlspecialchars(SITE_NAME) ?>. All rights reserved.</p>
      <div class="footer-legal">
        <a href="#">Privacy</a>
        <a href="#">Terms</a>
        <a href="#">Cookies</a>
      </div>
    </div>
  </div>
</footer>

<!-- Shared scripts -->
<script src="assets/js/app.js"></script>
