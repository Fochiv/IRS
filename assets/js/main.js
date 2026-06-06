/* ===== IRS Main JavaScript ===== */

document.addEventListener('DOMContentLoaded', function () {

    // ===== THEME =====
    const savedTheme = getCookie('theme') || 'light';
    applyTheme(savedTheme);

    document.querySelectorAll('.theme-toggle').forEach(btn => {
        btn.addEventListener('click', function () {
            const current = document.documentElement.getAttribute('data-theme') || 'light';
            const next = current === 'light' ? 'dark' : 'light';
            applyTheme(next);
            setCookie('theme', next, 365);
        });
    });

    function applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        document.querySelectorAll('.theme-toggle').forEach(btn => {
            const icon = btn.querySelector('i');
            const label = btn.querySelector('.theme-label');
            if (icon) icon.className = theme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
            if (label) label.textContent = theme === 'dark' ? 'Clair' : 'Sombre';
        });
    }

    // ===== LANGUAGE =====
    document.querySelectorAll('.lang-selector').forEach(sel => {
        sel.addEventListener('change', function () {
            window.location.href = '/set-lang.php?lang=' + this.value + '&redirect=' + encodeURIComponent(window.location.pathname + window.location.search);
        });
    });

    // ===== SIDEBAR TOGGLE (mobile) =====
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function () {
            sidebar.classList.toggle('open');
            if (overlay) overlay.classList.toggle('show');
        });
    }

    if (overlay) {
        overlay.addEventListener('click', function () {
            sidebar.classList.remove('open');
            overlay.classList.remove('show');
        });
    }

    // ===== ANIMATED COUNTERS =====
    const counters = document.querySelectorAll('[data-counter]');
    if (counters.length > 0) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });
        counters.forEach(c => observer.observe(c));
    }

    function animateCounter(el) {
        const target = parseInt(el.getAttribute('data-counter'));
        const suffix = el.getAttribute('data-suffix') || '';
        const duration = 2000;
        const step = target / (duration / 16);
        let current = 0;
        const timer = setInterval(() => {
            current += step;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            el.textContent = Math.floor(current).toLocaleString() + suffix;
        }, 16);
    }

    // ===== DOCUMENT VERIFICATION =====
    const verifyForm = document.getElementById('verifyForm');
    if (verifyForm) {
        verifyForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const docNumber = document.getElementById('docNumber').value.trim();
            if (!docNumber) return;
            performVerification(docNumber);
        });
    }

    function performVerification(docNumber) {
        const resultContainer = document.getElementById('verifyResult');
        if (!resultContainer) return;

        resultContainer.innerHTML = buildTimeline();
        resultContainer.style.display = 'block';
        resultContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });

        runTimeline(docNumber);
    }

    function buildTimeline() {
        const steps = [
            { icon: 'bi-file-earmark-check', name: window.IRS_LANG.doc_received },
            { icon: 'bi-file-text', name: window.IRS_LANG.analyzing_structure },
            { icon: 'bi-database-check', name: window.IRS_LANG.analyzing_data },
            { icon: 'bi-eye', name: window.IRS_LANG.ocr_analysis },
            { icon: 'bi-shield-check', name: window.IRS_LANG.security_analysis },
            { icon: 'bi-cpu', name: window.IRS_LANG.ai_verification },
            { icon: 'bi-person-check', name: window.IRS_LANG.expert_verification },
            { icon: 'bi-patch-check', name: window.IRS_LANG.final_validation },
        ];

        let html = `<div class="verify-timeline fade-in-up">
            <h6 class="fw-bold mb-3"><i class="bi bi-activity me-2 text-irs-blue"></i>Analyse en cours...</h6>`;

        steps.forEach((s, i) => {
            html += `<div class="timeline-step" id="step-${i}">
                <div class="timeline-step-icon"><i class="bi ${s.icon}"></i></div>
                <div class="timeline-step-info">
                    <div class="timeline-step-name">${s.name}</div>
                    <div class="timeline-step-progress">
                        <div class="timeline-step-bar" id="bar-${i}"></div>
                    </div>
                </div>
                <div class="timeline-step-pct" id="pct-${i}">0%</div>
            </div>`;
        });

        html += `</div>`;
        return html;
    }

    function runTimeline(docNumber) {
        const steps = document.querySelectorAll('.timeline-step');
        const delays = [600, 900, 800, 1000, 900, 1100, 1200, 700];
        let total = 0;

        steps.forEach((step, i) => {
            setTimeout(() => {
                step.classList.add('active');
                animateBar(i, delays[i], () => {
                    step.classList.remove('active');
                    step.classList.add('done');
                    const icon = step.querySelector('.timeline-step-icon i');
                    if (icon) icon.className = 'bi bi-check-lg';
                });
            }, total);

            total += delays[i] + 200;
        });

        setTimeout(() => {
            fetchVerifyResult(docNumber);
        }, total + 300);
    }

    function animateBar(index, duration, callback) {
        const bar = document.getElementById('bar-' + index);
        const pct = document.getElementById('pct-' + index);
        if (!bar || !pct) return;

        let current = 0;
        const step = 100 / (duration / 30);
        const timer = setInterval(() => {
            current = Math.min(100, current + step);
            bar.style.width = current + '%';
            pct.textContent = Math.floor(current) + '%';
            if (current >= 100) {
                clearInterval(timer);
                if (callback) callback();
            }
        }, 30);
    }

    function fetchVerifyResult(docNumber) {
        fetch('/api/verify.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'doc_number=' + encodeURIComponent(docNumber)
        })
        .then(r => r.json())
        .then(data => {
            displayResult(data);
        })
        .catch(() => {
            displayError();
        });
    }

    function displayResult(data) {
        const resultContainer = document.getElementById('verifyResult');
        if (!resultContainer) return;

        if (data.found) {
            const doc = data.document;
            resultContainer.innerHTML = `
                <div class="verify-result-container fade-in-up">
                    <div class="text-center mb-3">
                        <img src="/doc_verifier.png" alt="Verified" style="height:120px;" class="mb-3">
                        <div class="verified-stamp mx-auto d-inline-flex">
                            <i class="bi bi-patch-check-fill"></i>
                            <div>
                                <div>${window.IRS_LANG.verified_badge}</div>
                                <div style="font-size:0.7rem;font-weight:400;letter-spacing:1px;">${window.IRS_LANG.doc_authenticated}</div>
                            </div>
                        </div>
                    </div>
                    <div class="verify-info-card">
                        <div class="info-header">
                            <i class="bi bi-file-earmark-text"></i>
                            <span>Informations du Document</span>
                        </div>
                        <div class="info-body">
                            <div class="info-row">
                                <span class="info-label"><i class="bi bi-hash"></i> Numéro</span>
                                <span class="info-value fw-bold">${escHtml(doc.document_number)}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label"><i class="bi bi-person"></i> Titulaire</span>
                                <span class="info-value">${escHtml(doc.holder_name)}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label"><i class="bi bi-file-earmark"></i> Type</span>
                                <span class="info-value">${escHtml(doc.document_type)}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label"><i class="bi bi-building"></i> Organisme</span>
                                <span class="info-value">${escHtml(doc.issuing_organization)}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label"><i class="bi bi-calendar3"></i> Date d'émission</span>
                                <span class="info-value">${escHtml(doc.issue_date)}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label"><i class="bi bi-geo-alt"></i> Pays</span>
                                <span class="info-value">${escHtml(doc.country_of_origin)}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label"><i class="bi bi-check-circle"></i> Statut</span>
                                <span class="info-value"><span class="badge bg-success"><i class="bi bi-patch-check-fill me-1"></i>Vérifié</span></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label"><i class="bi bi-clock-history"></i> Vérifié le</span>
                                <span class="info-value">${new Date().toLocaleDateString('fr-FR')}</span>
                            </div>
                        </div>
                    </div>
                    ${doc.file_path ? `<div class="mt-3 text-center">
                        <a href="/${escHtml(doc.file_path)}" class="btn btn-success" target="_blank">
                            <i class="bi bi-download me-2"></i>${window.IRS_LANG.download_doc}
                        </a>
                    </div>` : ''}
                </div>`;
        } else {
            resultContainer.innerHTML = `
                <div class="verify-result-container fade-in-up">
                    <div class="text-center mb-3">
                        <img src="/doc_non_verifier.png" alt="Not Verified" style="height:120px;" class="mb-3">
                        <div class="not-verified-stamp mx-auto d-inline-flex">
                            <i class="bi bi-x-circle-fill"></i>
                            <div>
                                <div>${window.IRS_LANG.not_verified_badge}</div>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-danger text-center" style="border-radius:12px;">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        ${window.IRS_LANG.doc_not_found}
                    </div>
                    <div class="text-center mt-3">
                        <a href="/register.php" class="btn btn-danger me-2">
                            <i class="bi bi-upload me-2"></i>${window.IRS_LANG.submit_for_analysis}
                        </a>
                    </div>
                </div>`;
        }
    }

    function displayError() {
        const resultContainer = document.getElementById('verifyResult');
        if (resultContainer) {
            resultContainer.innerHTML = `<div class="alert alert-danger"><i class="bi bi-wifi-off me-2"></i>Erreur de connexion. Veuillez réessayer.</div>`;
        }
    }

    // ===== CONFIRM DELETE =====
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', function (e) {
            const msg = this.getAttribute('data-confirm');
            if (!confirm(msg)) e.preventDefault();
        });
    });

    // ===== AUTO-HIDE ALERTS =====
    document.querySelectorAll('.alert-dismissible').forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) bsAlert.close();
        }, 5000);
    });

    // ===== UTILITIES =====
    function escHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function getCookie(name) {
        const v = document.cookie.match('(^|;)\\s*' + name + '\\s*=\\s*([^;]+)');
        return v ? v.pop() : null;
    }

    function setCookie(name, value, days) {
        const d = new Date();
        d.setTime(d.getTime() + days * 864e5);
        document.cookie = name + '=' + value + ';expires=' + d.toUTCString() + ';path=/';
    }

});

// Default IRS_LANG (overridden by page)
window.IRS_LANG = window.IRS_LANG || {
    verified_badge: 'VERIFIED',
    not_verified_badge: 'NOT VERIFIED',
    doc_authenticated: 'Document Authenticated',
    doc_not_found: 'This document does not exist in our registry.',
    submit_for_analysis: 'Submit for Analysis',
    download_doc: 'Download official document',
    doc_received: 'Document received',
    analyzing_structure: 'Structure analysis',
    analyzing_data: 'Data analysis',
    ocr_analysis: 'OCR analysis',
    security_analysis: 'Security analysis',
    ai_verification: 'AI Verification',
    expert_verification: 'Expert verification',
    final_validation: 'Final validation',
};
