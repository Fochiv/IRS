<?php
$lang = getLang(); $theme = getTheme();
function getSettingFooter($conn, $key, $default = '') {
    static $cache = [];
    if (isset($cache[$key])) return $cache[$key];
    $k = $conn->real_escape_string($key);
    $r = $conn->query("SELECT setting_value FROM settings WHERE setting_key='$k' LIMIT 1");
    $cache[$key] = ($r && $r->num_rows > 0) ? $r->fetch_assoc()['setting_value'] : $default;
    return $cache[$key];
}
$contact_email = getSettingFooter($conn, 'site_email', 'internationalregistrationserve@gmail.com');
?>

<footer class="irs-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="footer-brand d-flex align-items-center gap-2 mb-3">
                    <img src="/logo.png" alt="IRS">
                    <div>
                        <div class="brand-name">IRS</div>
                        <div class="brand-sub">International Registration Server</div>
                    </div>
                </div>
                <p class="footer-desc" data-i18n="about_text"><?= t('about_text') ?></p>
            </div>
            <div class="col-lg-2 col-md-6">
                <h6 class="footer-heading" data-i18n="home"><?= t('home') ?></h6>
                <ul class="footer-links">
                    <li><a href="/index.php#about-section"><i class="bi bi-chevron-right me-1" style="font-size:0.7rem;"></i><span data-i18n="about"><?= t('about') ?></span></a></li>
                    <li><a href="/index.php#verify-section"><i class="bi bi-chevron-right me-1" style="font-size:0.7rem;"></i><span data-i18n="verify"><?= t('verify') ?></span></a></li>
                    <li><a href="/index.php#faq-section"><i class="bi bi-chevron-right me-1" style="font-size:0.7rem;"></i><span data-i18n="faq"><?= t('faq') ?></span></a></li>
                    <li><a href="/index.php#contact-section"><i class="bi bi-chevron-right me-1" style="font-size:0.7rem;"></i><span data-i18n="contact"><?= t('contact') ?></span></a></li>
                </ul>
            </div>
            <div class="col-lg-3 col-md-6">
                <h6 class="footer-heading">Services</h6>
                <ul class="footer-links">
                    <li><a href="/index.php#verify-section"><i class="bi bi-chevron-right me-1" style="font-size:0.7rem;"></i><span data-i18n="verify_doc_link"><?= $lang === 'fr' ? 'Vérifier un document' : 'Verify a document' ?></span></a></li>
                    <li><a href="/register.php"><i class="bi bi-chevron-right me-1" style="font-size:0.7rem;"></i><span data-i18n="submit_doc"><?= t('submit_doc') ?></span></a></li>
                    <li><a href="/login.php"><i class="bi bi-chevron-right me-1" style="font-size:0.7rem;"></i><span data-i18n="login"><?= t('login') ?></span></a></li>
                    <li><a href="/register.php"><i class="bi bi-chevron-right me-1" style="font-size:0.7rem;"></i><span data-i18n="register"><?= t('register') ?></span></a></li>
                </ul>
            </div>
            <div class="col-lg-3 col-md-6">
                <h6 class="footer-heading" data-i18n="legal"><?= $lang === 'fr' ? 'Légal' : 'Legal' ?></h6>
                <ul class="footer-links">
                    <li><a href="/privacy.php"><i class="bi bi-chevron-right me-1" style="font-size:0.7rem;"></i><span data-i18n="privacy"><?= t('privacy') ?></span></a></li>
                    <li><a href="/terms.php"><i class="bi bi-chevron-right me-1" style="font-size:0.7rem;"></i><span data-i18n="terms"><?= t('terms') ?></span></a></li>
                </ul>
                <div class="mt-3">
                    <div style="color:rgba(255,255,255,0.6);font-size:0.8rem;margin-bottom:0.5rem;">
                        <i class="bi bi-envelope me-1"></i> <?= htmlspecialchars($contact_email) ?>
                    </div>
                    <div style="color:rgba(255,255,255,0.6);font-size:0.8rem;">
                        <i class="bi bi-globe me-1"></i> www.irs-server.com
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <span class="copyright" data-i18n="rights"><?= t('rights') ?></span>
            <div class="footer-controls">
                <select class="lang-selector" aria-label="Langue">
                    <option value="fr" <?= $lang === 'fr' ? 'selected' : '' ?>>🇫🇷 Français</option>
                    <option value="en" <?= $lang === 'en' ? 'selected' : '' ?>>🇬🇧 English</option>
                </select>
                <button class="theme-toggle" aria-label="Thème">
                    <i class="bi <?= $theme === 'dark' ? 'bi-sun-fill' : 'bi-moon-fill' ?>"></i>
                    <span class="theme-label"><?= $theme === 'dark' ? 'Clair' : 'Sombre' ?></span>
                </button>
            </div>
        </div>
    </div>
</footer>

<!-- Bouton retour en haut -->
<button id="backToTop" title="<?= $lang === 'fr' ? 'Retour en haut' : 'Back to top' ?>">
    <i class="bi bi-arrow-up"></i>
</button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
<?php
$fr = require __DIR__ . '/../lang/fr.php';
$en = require __DIR__ . '/../lang/en.php';
?>
window.IRS_TRANSLATIONS = {
    fr: <?= json_encode($fr, JSON_UNESCAPED_UNICODE) ?>,
    en: <?= json_encode($en, JSON_UNESCAPED_UNICODE) ?>
};
window.IRS_LANG = window.IRS_TRANSLATIONS['<?= $lang ?>'] || window.IRS_TRANSLATIONS['fr'];
// Extra keys for JS verification UI
window.IRS_LANG.download_certificate = '<?= $lang === "fr" ? "Télécharger le certificat officiel" : "Download official certificate" ?>';
window.IRS_LANG.download_original = '<?= $lang === "fr" ? "Télécharger le document original" : "Download original document" ?>';
window.IRS_LANG.doc_preview = '<?= $lang === "fr" ? "Aperçu du document" : "Document preview" ?>';
window.IRS_LANG.verify_doc_link = '<?= $lang === "fr" ? "Vérifier un document" : "Verify a document" ?>';
window.IRS_LANG.legal = '<?= $lang === "fr" ? "Légal" : "Legal" ?>';
</script>
<script src="/assets/js/main.js"></script>
<?= isset($extra_js) ? $extra_js : '' ?>
</body>
</html>
