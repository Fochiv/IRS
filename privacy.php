<?php
require_once __DIR__ . '/includes/functions.php';
$page_title = 'Politique de confidentialité';
include __DIR__ . '/includes/header.php';
?>
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="irs-card">
                    <div class="irs-card-body">
                        <h1 style="font-size:1.8rem;font-weight:700;color:var(--irs-dark-blue);margin-bottom:0.5rem;">
                            <i class="bi bi-shield-lock me-2 text-irs-blue"></i>Politique de Confidentialité
                        </h1>
                        <p style="color:var(--irs-text-muted);font-size:0.9rem;margin-bottom:2rem;">Dernière mise à jour : Juin 2024</p>

                        <h5><i class="bi bi-info-circle me-2 text-irs-blue"></i>1. Collecte des données</h5>
                        <p style="color:var(--irs-text-muted);">IRS collecte uniquement les données nécessaires au fonctionnement du service : nom, prénom, adresse email, numéro de téléphone, pays de résidence, et les documents soumis pour vérification.</p>

                        <h5 class="mt-4"><i class="bi bi-lock me-2 text-irs-blue"></i>2. Protection des données</h5>
                        <p style="color:var(--irs-text-muted);">Toutes les données sont traitées conformément aux standards modernes de sécurité. Nous utilisons des protocoles de chiffrement et des mesures de protection pour garantir la sécurité de vos informations.</p>

                        <h5 class="mt-4"><i class="bi bi-share me-2 text-irs-blue"></i>3. Partage des données</h5>
                        <p style="color:var(--irs-text-muted);">IRS ne vend, ne loue et ne partage pas vos données personnelles avec des tiers, sauf obligation légale ou avec votre consentement explicite.</p>

                        <h5 class="mt-4"><i class="bi bi-person-check me-2 text-irs-blue"></i>4. Vos droits</h5>
                        <p style="color:var(--irs-text-muted);">Vous disposez d'un droit d'accès, de rectification et de suppression de vos données. Pour exercer ces droits, contactez-nous à : contact@irs-server.com</p>

                        <h5 class="mt-4"><i class="bi bi-cookie me-2 text-irs-blue"></i>5. Cookies</h5>
                        <p style="color:var(--irs-text-muted);">Nous utilisons des cookies essentiels pour le fonctionnement du service (session, préférences de thème et de langue). Aucun cookie de tracking tiers n'est utilisé.</p>

                        <div class="mt-4"><a href="/index.php" class="btn btn-sm" style="background:var(--irs-blue);color:white;border-radius:8px;"><i class="bi bi-arrow-left me-1"></i>Retour à l'accueil</a></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
