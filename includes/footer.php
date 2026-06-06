<?php
$lang = getLang(); $theme = getTheme();
// Lire l'email de contact depuis la DB (avec fallback)
function getSettingFooter($conn, $key, $default = '') {
    $key = $conn->real_escape_string($key);
    $r = $conn->query("SELECT setting_value FROM settings WHERE setting_key = '$key' LIMIT 1");
    if ($r && $r->num_rows > 0) return $r->fetch_assoc()['setting_value'];
    return $default;
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
                <p class="footer-desc"><?= t('about_text') ?></p>
            </div>
            <div class="col-lg-2 col-md-6">
                <h6 class="footer-heading"><?= t('home') ?></h6>
                <ul class="footer-links">
                    <li><a href="/index.php#about-section"><i class="bi bi-chevron-right me-1" style="font-size:0.7rem;"></i><?= t('about') ?></a></li>
                    <li><a href="/index.php#verify-section"><i class="bi bi-chevron-right me-1" style="font-size:0.7rem;"></i><?= t('verify') ?></a></li>
                    <li><a href="/index.php#faq-section"><i class="bi bi-chevron-right me-1" style="font-size:0.7rem;"></i><?= t('faq') ?></a></li>
                    <li><a href="/index.php#contact-section"><i class="bi bi-chevron-right me-1" style="font-size:0.7rem;"></i><?= t('contact') ?></a></li>
                </ul>
            </div>
            <div class="col-lg-3 col-md-6">
                <h6 class="footer-heading">Services</h6>
                <ul class="footer-links">
                    <li><a href="/index.php#verify-section"><i class="bi bi-chevron-right me-1" style="font-size:0.7rem;"></i><?= $lang === 'fr' ? 'Vérifier un document' : 'Verify a document' ?></a></li>
                    <li><a href="/register.php"><i class="bi bi-chevron-right me-1" style="font-size:0.7rem;"></i><?= $lang === 'fr' ? 'Soumettre un document' : 'Submit a document' ?></a></li>
                    <li><a href="/login.php"><i class="bi bi-chevron-right me-1" style="font-size:0.7rem;"></i><?= t('login') ?></a></li>
                    <li><a href="/register.php"><i class="bi bi-chevron-right me-1" style="font-size:0.7rem;"></i><?= t('register') ?></a></li>
                </ul>
            </div>
            <div class="col-lg-3 col-md-6">
                <h6 class="footer-heading"><?= $lang === 'fr' ? 'Légal' : 'Legal' ?></h6>
                <ul class="footer-links">
                    <li><a href="/privacy.php"><i class="bi bi-chevron-right me-1" style="font-size:0.7rem;"></i><?= t('privacy') ?></a></li>
                    <li><a href="/terms.php"><i class="bi bi-chevron-right me-1" style="font-size:0.7rem;"></i><?= t('terms') ?></a></li>
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
            <span class="copyright"><?= t('rights') ?></span>
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
<button id="backToTop" title="Retour en haut">
    <i class="bi bi-arrow-up"></i>
</button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
window.IRS_LANG = {
    verified_badge: '<?= addslashes(t("verified_badge")) ?>',
    not_verified_badge: '<?= addslashes(t("not_verified_badge")) ?>',
    doc_authenticated: '<?= addslashes(t("doc_authenticated")) ?>',
    doc_not_found: '<?= addslashes(t("doc_not_found")) ?>',
    submit_for_analysis: '<?= addslashes(t("submit_for_analysis")) ?>',
    download_doc: '<?= addslashes(t("download_doc")) ?>',
    doc_received: '<?= addslashes(t("doc_received")) ?>',
    analyzing_structure: '<?= addslashes(t("analyzing_structure")) ?>',
    analyzing_data: '<?= addslashes(t("analyzing_data")) ?>',
    ocr_analysis: '<?= addslashes(t("ocr_analysis")) ?>',
    security_analysis: '<?= addslashes(t("security_analysis")) ?>',
    ai_verification: '<?= addslashes(t("ai_verification")) ?>',
    expert_verification: '<?= addslashes(t("expert_verification")) ?>',
    final_validation: '<?= addslashes(t("final_validation")) ?>',
    download_certificate: '<?= $lang === "fr" ? "Télécharger le certificat officiel" : "Download official certificate" ?>',
    holder_name: '<?= addslashes(t("holder_name")) ?>',
    doc_type: '<?= addslashes(t("doc_type")) ?>',
    issuing_org: '<?= addslashes(t("issuing_org")) ?>',
    issue_date: '<?= addslashes(t("issue_date")) ?>',
    country_origin: '<?= addslashes(t("country_origin")) ?>',
    verify_date: '<?= addslashes(t("verify_date")) ?>',
};
</script>
<script src="/assets/js/main.js"></script>
<?= isset($extra_js) ? $extra_js : '' ?>
</body>
</html>
