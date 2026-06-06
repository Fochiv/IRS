<?php
require_once __DIR__ . '/includes/functions.php';
$page_title = "Conditions d'utilisation";
include __DIR__ . '/includes/header.php';
?>
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="irs-card">
                    <div class="irs-card-body">
                        <h1 style="font-size:1.8rem;font-weight:700;color:var(--irs-dark-blue);margin-bottom:0.5rem;">
                            <i class="bi bi-file-text me-2 text-irs-blue"></i>Conditions d'Utilisation
                        </h1>
                        <p style="color:var(--irs-text-muted);font-size:0.9rem;margin-bottom:2rem;">Dernière mise à jour : Juin 2024</p>

                        <h5><i class="bi bi-check-circle me-2 text-irs-blue"></i>1. Acceptation des conditions</h5>
                        <p style="color:var(--irs-text-muted);">En utilisant la plateforme IRS, vous acceptez les présentes conditions d'utilisation. Si vous n'acceptez pas ces conditions, veuillez ne pas utiliser le service.</p>

                        <h5 class="mt-4"><i class="bi bi-tools me-2 text-irs-blue"></i>2. Description du service</h5>
                        <p style="color:var(--irs-text-muted);">IRS est une plateforme de vérification et de certification documentaire internationale. Le service permet de vérifier l'authenticité de documents enregistrés dans notre registre sécurisé.</p>

                        <h5 class="mt-4"><i class="bi bi-person-badge me-2 text-irs-blue"></i>3. Conditions d'utilisation du compte</h5>
                        <p style="color:var(--irs-text-muted);">Vous êtes responsable de la confidentialité de vos identifiants de connexion. Toute activité effectuée depuis votre compte est de votre responsabilité. Il est interdit de partager votre compte avec des tiers.</p>

                        <h5 class="mt-4"><i class="bi bi-exclamation-triangle me-2 text-irs-blue"></i>4. Utilisation prohibée</h5>
                        <p style="color:var(--irs-text-muted);">Il est strictement interdit de soumettre des documents falsifiés, d'usurper l'identité d'une autre personne, ou d'utiliser le service à des fins frauduleuses. Toute violation entraînera la suspension immédiate du compte et des poursuites judiciaires.</p>

                        <h5 class="mt-4"><i class="bi bi-shield-x me-2 text-irs-blue"></i>5. Limitation de responsabilité</h5>
                        <p style="color:var(--irs-text-muted);">IRS fournit le service "en l'état" et ne garantit pas l'exactitude des informations. La responsabilité d'IRS est limitée aux cas de faute grave ou intentionnelle.</p>

                        <div class="mt-4"><a href="/index.php" class="btn btn-sm" style="background:var(--irs-blue);color:white;border-radius:8px;"><i class="bi bi-arrow-left me-1"></i>Retour à l'accueil</a></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
