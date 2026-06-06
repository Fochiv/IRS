<?php
require_once __DIR__ . '/includes/functions.php';
$page_title = 'Accueil';
include __DIR__ . '/includes/header.php';
?>

<!-- HERO SECTION -->
<section class="hero-section" id="hero">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-7 hero-content">
                <div class="mb-3">
                    <span class="badge" style="background:rgba(46,134,222,0.2);color:var(--irs-blue);font-size:0.8rem;padding:0.4rem 0.9rem;border-radius:20px;border:1px solid rgba(46,134,222,0.3);">
                        <i class="bi bi-shield-fill-check me-1"></i> Registre International Sécurisé
                    </span>
                </div>
                <h1>International <span class="brand-highlight">Registration</span> Server (IRS)</h1>
                <p class="hero-subtitle"><?= t('tagline') ?></p>
                <p class="hero-text"><?= t('hero_text') ?></p>

                <div class="hero-verify-box" id="verify-section">
                    <h6 style="color:white;font-weight:600;margin-bottom:1rem;">
                        <i class="bi bi-search me-2" style="color:var(--irs-blue);"></i>
                        Vérification de Document
                    </h6>
                    <form id="verifyForm">
                        <div class="d-flex gap-2 flex-wrap">
                            <input type="text" id="docNumber" class="form-control flex-grow-1" placeholder="<?= t('enter_doc_number') ?>" autocomplete="off" required>
                            <button type="submit" class="btn-verify btn">
                                <i class="bi bi-search me-2"></i><?= t('verify_now') ?>
                            </button>
                        </div>
                    </form>
                    <p class="hero-note"><i class="bi bi-info-circle me-1"></i><?= t('no_account_needed') ?></p>
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-flex justify-content-center">
                <div class="hero-image-wrapper">
                    <div class="hero-shield">
                        <i class="bi bi-shield-fill-check"></i>
                    </div>
                    <div style="position:absolute;top:20px;right:-20px;background:rgba(40,167,69,0.15);border:1px solid rgba(40,167,69,0.3);border-radius:10px;padding:0.6rem 1rem;color:#5dd879;font-size:0.8rem;font-weight:600;">
                        <i class="bi bi-patch-check-fill me-1"></i> Document Vérifié
                    </div>
                    <div style="position:absolute;bottom:30px;left:-30px;background:rgba(46,134,222,0.15);border:1px solid rgba(46,134,222,0.3);border-radius:10px;padding:0.6rem 1rem;color:#7bc8ff;font-size:0.8rem;font-weight:600;">
                        <i class="bi bi-cpu me-1"></i> Analyse IA Active
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- VERIFY RESULT -->
<section class="py-4" id="verify-result-section" style="display:none;">
    <div class="container">
        <div id="verifyResult"></div>
    </div>
</section>

<!-- ABOUT SECTION -->
<section class="py-5" id="about-section">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-5">
                <div style="background:linear-gradient(135deg,#0A2342,#1a4a8a);border-radius:20px;padding:3rem;text-align:center;color:white;">
                    <i class="bi bi-award-fill" style="font-size:5rem;color:var(--irs-blue);display:block;margin-bottom:1.5rem;"></i>
                    <h4 style="font-weight:700;">Certifié International</h4>
                    <p style="color:rgba(255,255,255,0.7);font-size:0.9rem;">Plateforme de référence mondiale pour la certification documentaire</p>
                    <div class="d-flex justify-content-around mt-3">
                        <div><i class="bi bi-shield-check" style="font-size:1.5rem;color:var(--irs-green);"></i><br><small>Sécurisé</small></div>
                        <div><i class="bi bi-globe" style="font-size:1.5rem;color:var(--irs-blue);"></i><br><small>International</small></div>
                        <div><i class="bi bi-cpu" style="font-size:1.5rem;color:#ffd700;"></i><br><small>IA Avancée</small></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="section-divider" style="margin:0 0 1rem 0;"></div>
                <h2 class="section-title"><?= t('about_title') ?></h2>
                <p style="color:var(--irs-text-muted);line-height:1.8;margin-bottom:1.5rem;"><?= t('about_text') ?></p>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div style="display:flex;align-items:center;gap:0.75rem;padding:0.75rem;background:var(--irs-gray);border-radius:10px;">
                            <i class="bi bi-check-circle-fill text-success" style="font-size:1.2rem;"></i>
                            <span style="font-size:0.9rem;font-weight:500;color:var(--irs-text);">Vérification instantanée</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div style="display:flex;align-items:center;gap:0.75rem;padding:0.75rem;background:var(--irs-gray);border-radius:10px;">
                            <i class="bi bi-check-circle-fill text-success" style="font-size:1.2rem;"></i>
                            <span style="font-size:0.9rem;font-weight:500;color:var(--irs-text);">Experts qualifiés</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div style="display:flex;align-items:center;gap:0.75rem;padding:0.75rem;background:var(--irs-gray);border-radius:10px;">
                            <i class="bi bi-check-circle-fill text-success" style="font-size:1.2rem;"></i>
                            <span style="font-size:0.9rem;font-weight:500;color:var(--irs-text);">Intelligence Artificielle</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div style="display:flex;align-items:center;gap:0.75rem;padding:0.75rem;background:var(--irs-gray);border-radius:10px;">
                            <i class="bi bi-check-circle-fill text-success" style="font-size:1.2rem;"></i>
                            <span style="font-size:0.9rem;font-weight:500;color:var(--irs-text);">Couverture internationale</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- PROCESS SECTION -->
<section class="process-section py-5">
    <div class="container">
        <div class="text-center mb-5">
            <div class="section-divider"></div>
            <h2 class="section-title"><?= t('process_title') ?></h2>
            <p class="section-subtitle">Un processus rigoureux en plusieurs étapes pour garantir l'authenticité.</p>
        </div>
        <div class="row g-4">
            <div class="col-md-6">
                <?php
                $steps = [
                    ['bi-upload', 'Étape 1', 'Soumission du numéro du document', "L'utilisateur saisit simplement le numéro unique du document à vérifier."],
                    ['bi-database-check', 'Étape 2', 'Recherche dans le registre sécurisé IRS', 'Notre système recherche instantanément le document dans la base de données officielle.'],
                    ['bi-patch-check', 'Étape 3', "Validation de l'authenticité", 'Si le document est enregistré, ses informations officielles sont affichées immédiatement.'],
                ];
                foreach ($steps as $s): ?>
                <div class="process-step">
                    <div class="step-icon"><i class="bi <?= $s[0] ?>"></i></div>
                    <div>
                        <div class="step-number"><?= $s[1] ?></div>
                        <div class="step-title"><?= $s[2] ?></div>
                        <p class="step-desc"><?= $s[3] ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="col-md-6">
                <?php
                $steps2 = [
                    ['bi-cpu', 'Étape 4', 'Analyse approfondie', "Si le document n'est pas trouvé, l'utilisateur peut demander une vérification avancée réalisée par notre IA et nos experts."],
                    ['bi-bell-fill', 'Étape 5', 'Notification des résultats', 'Le résultat final est communiqué directement sur la plateforme et par email.'],
                ];
                foreach ($steps2 as $s): ?>
                <div class="process-step">
                    <div class="step-icon"><i class="bi <?= $s[0] ?>"></i></div>
                    <div>
                        <div class="step-number"><?= $s[1] ?></div>
                        <div class="step-title"><?= $s[2] ?></div>
                        <p class="step-desc"><?= $s[3] ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- TRUST SECTION -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <div class="section-divider"></div>
            <h2 class="section-title"><?= t('trust_title') ?></h2>
            <p class="section-subtitle">6 raisons de faire confiance à IRS pour vos vérifications documentaires.</p>
        </div>
        <div class="row g-4">
            <?php
            $cards = [
                ['bi-shield-fill-check', 'Vérification Sécurisée', 'Protection avancée des données et des documents.', 'blue'],
                ['bi-cpu-fill', 'Intelligence Artificielle', 'Analyse automatisée des incohérences et anomalies.', 'purple'],
                ['bi-people-fill', 'Experts Qualifiés', 'Contrôle humain pour les cas complexes.', 'green'],
                ['bi-bell-fill', 'Notifications en Temps Réel', "Suivi complet de l'évolution des vérifications.", 'orange'],
                ['bi-globe2', 'Registre International', 'Base documentaire centralisée et sécurisée.', 'blue'],
                ['bi-award-fill', 'Certification Numérique', 'Documents validés accompagnés d\'un certificat officiel.', 'green'],
            ];
            foreach ($cards as $c): ?>
            <div class="col-md-6 col-lg-4">
                <div class="trust-card">
                    <div class="trust-icon"><i class="bi <?= $c[0] ?>"></i></div>
                    <h5><?= $c[1] ?></h5>
                    <p><?= $c[2] ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- STATS SECTION -->
<section class="stats-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 style="color:white;font-size:2rem;font-weight:700;"><?= t('stats_title') ?></h2>
        </div>
        <div class="row g-4">
            <div class="col-6 col-md-3">
                <div class="stat-item">
                    <span class="stat-number" data-counter="152847">0</span>
                    <span class="stat-label"><i class="bi bi-file-earmark-text me-1"></i>Documents enregistrés</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-item">
                    <span class="stat-number" data-counter="489213">0</span>
                    <span class="stat-label"><i class="bi bi-search me-1"></i>Vérifications effectuées</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-item">
                    <span class="stat-number" data-counter="99" data-suffix="%">0</span>
                    <span class="stat-label"><i class="bi bi-graph-up me-1"></i>Taux de réussite</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-item">
                    <span class="stat-number" data-counter="195">0</span>
                    <span class="stat-label"><i class="bi bi-globe me-1"></i>Pays couverts</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ SECTION -->
<section class="faq-section py-5" id="faq-section">
    <div class="container">
        <div class="text-center mb-5">
            <div class="section-divider"></div>
            <h2 class="section-title"><?= t('faq_title') ?></h2>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="faqAccordion">
                    <?php
                    $faqs = [
                        ['Une vérification nécessite-t-elle un compte ?', 'Non. Toute personne peut vérifier un document sans créer de compte. La vérification est gratuite et accessible à tous.'],
                        ['Quand dois-je créer un compte ?', 'Uniquement si vous souhaitez soumettre un document non vérifié pour une analyse approfondie par nos experts et notre IA.'],
                        ['Combien de temps dure une vérification approfondie ?', 'Selon la complexité du dossier, le délai peut varier de quelques heures à plusieurs jours ouvrables.'],
                        ['Vais-je recevoir une notification ?', 'Oui. Les notifications sont disponibles dans votre espace personnel et envoyées par email à chaque étape.'],
                        ['Mes données sont-elles protégées ?', 'Oui. Toutes les données sont traitées conformément aux standards modernes de sécurité informatique et de protection des données.'],
                    ];
                    foreach ($faqs as $i => $faq): ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button <?= $i > 0 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#faq<?= $i ?>">
                                <i class="bi bi-question-circle me-2 text-irs-blue"></i>
                                <?= $faq[0] ?>
                            </button>
                        </h2>
                        <div id="faq<?= $i ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <i class="bi bi-check-circle-fill text-success me-2"></i><?= $faq[1] ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA SECTION -->
<section class="cta-section">
    <div class="container">
        <h2><?= t('cta_title') ?></h2>
        <p><?= t('cta_text') ?></p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="#verify-section" class="btn-cta-primary">
                <i class="bi bi-search"></i><?= t('start_verify') ?>
            </a>
            <a href="/register.php" class="btn-cta-secondary">
                <i class="bi bi-upload"></i><?= t('submit_doc') ?>
            </a>
        </div>
    </div>
</section>

<script>
// Show verify result section when result appears
const origVerify = document.getElementById('verifyResult');
if (origVerify) {
    const observer = new MutationObserver(() => {
        if (origVerify.innerHTML.trim() !== '') {
            document.getElementById('verify-result-section').style.display = 'block';
        }
    });
    observer.observe(origVerify, { childList: true });
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
