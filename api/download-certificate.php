<?php
require_once __DIR__ . '/../includes/db.php';

$doc_number = trim($_GET['doc'] ?? '');
if (empty($doc_number)) {
    http_response_code(400);
    die('Document number required');
}

$safe_doc = $conn->real_escape_string($doc_number);
$result = $conn->query("SELECT * FROM documents WHERE document_number = '$safe_doc' AND status = 'verified' LIMIT 1");
if (!$result || $result->num_rows === 0) {
    http_response_code(404);
    die('Document non trouvé ou non vérifié.');
}
$doc = $result->fetch_assoc();

$issue_date = $doc['issue_date'] ? date('d/m/Y', strtotime($doc['issue_date'])) : '-';
$today = date('d/m/Y H:i');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificat de Vérification - IRS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #f0f4f8; display: flex; flex-direction: column; align-items: center; min-height: 100vh; padding: 2rem; }
        .print-btn {
            margin-bottom: 1.5rem;
            padding: 0.75rem 2rem;
            background: #2E86DE;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
        }
        .print-btn:hover { background: #1a70c8; }
        @media print {
            .print-btn { display: none; }
            body { background: white; padding: 0; }
        }
        .certificate {
            background: white;
            border: 2px solid #0A2342;
            border-radius: 12px;
            width: 100%;
            max-width: 800px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
        }
        .cert-header {
            background: linear-gradient(135deg, #0A2342, #1a4a8a);
            color: white;
            padding: 2.5rem;
            text-align: center;
        }
        .cert-header img { height: 60px; margin-bottom: 1rem; }
        .cert-header h1 { font-size: 1.6rem; font-weight: 800; margin-bottom: 0.25rem; }
        .cert-header p { font-size: 0.9rem; opacity: 0.75; }
        .cert-badge {
            background: #28A745;
            color: white;
            padding: 0.6rem 2rem;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 1.5rem auto 0;
        }
        .cert-body { padding: 2.5rem; }
        .cert-title {
            text-align: center;
            font-size: 1.3rem;
            font-weight: 700;
            color: #0A2342;
            border-bottom: 3px solid #2E86DE;
            padding-bottom: 1rem;
            margin-bottom: 2rem;
        }
        .cert-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem; }
        .cert-field label {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6c757d;
            display: block;
            margin-bottom: 0.3rem;
        }
        .cert-field .value {
            font-size: 0.95rem;
            font-weight: 600;
            color: #212529;
            padding: 0.6rem 0.9rem;
            background: #f8f9fa;
            border-radius: 6px;
            border-left: 3px solid #2E86DE;
        }
        .cert-doc-number {
            text-align: center;
            padding: 1.25rem;
            background: #f0f9ff;
            border: 2px dashed #2E86DE;
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }
        .cert-doc-number label {
            font-size: 0.8rem;
            font-weight: 700;
            color: #6c757d;
            text-transform: uppercase;
            display: block;
            margin-bottom: 0.25rem;
        }
        .cert-doc-number .doc-num {
            font-size: 1.3rem;
            font-weight: 800;
            color: #0A2342;
            font-family: monospace;
        }
        .cert-footer {
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
            padding: 1.25rem 2.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.8rem;
            color: #6c757d;
        }
        .cert-stamp {
            text-align: center;
            padding: 1.5rem;
            border: 3px solid #28A745;
            border-radius: 50%;
            width: 100px;
            height: 100px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: #28A745;
        }
        .cert-stamp .icon { font-size: 2.5rem; line-height: 1; }
        .cert-stamp .text { font-size: 0.55rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        @media (max-width: 600px) { .cert-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<button class="print-btn" onclick="window.print()">🖨️ Imprimer / Télécharger en PDF</button>

<div class="certificate">
    <div class="cert-header">
        <img src="/logo.png" alt="IRS">
        <h1>International Registration Server</h1>
        <p>Registre International de Documents Officiels</p>
        <div class="cert-badge">✓ Document Vérifié &amp; Authentifié</div>
    </div>

    <div class="cert-body">
        <div class="cert-stamp">
            <div class="icon">✓</div>
            <div class="text">IRS Certified</div>
        </div>

        <div class="cert-title">Certificat de Vérification Officielle</div>

        <div class="cert-doc-number">
            <label>Numéro de document IRS</label>
            <div class="doc-num"><?= htmlspecialchars($doc['document_number']) ?></div>
        </div>

        <div class="cert-grid">
            <div class="cert-field">
                <label>Nom du titulaire</label>
                <div class="value"><?= htmlspecialchars($doc['holder_name']) ?></div>
            </div>
            <div class="cert-field">
                <label>Type de document</label>
                <div class="value"><?= htmlspecialchars($doc['document_type']) ?></div>
            </div>
            <div class="cert-field">
                <label>Organisme émetteur</label>
                <div class="value"><?= htmlspecialchars($doc['issuing_organization']) ?></div>
            </div>
            <div class="cert-field">
                <label>Pays d'origine</label>
                <div class="value"><?= htmlspecialchars($doc['country_of_origin'] ?? '-') ?></div>
            </div>
            <div class="cert-field">
                <label>Date d'émission</label>
                <div class="value"><?= htmlspecialchars($issue_date) ?></div>
            </div>
            <div class="cert-field">
                <label>Date de vérification</label>
                <div class="value"><?= $today ?></div>
            </div>
            <div class="cert-field">
                <label>Statut</label>
                <div class="value" style="color:#28A745;border-left-color:#28A745;">✓ VERIFIED — Authentifié par IRS</div>
            </div>
            <?php if (!empty($doc['description'])): ?>
            <div class="cert-field">
                <label>Description</label>
                <div class="value"><?= htmlspecialchars($doc['description']) ?></div>
            </div>
            <?php endif; ?>
        </div>

        <p style="font-size:0.82rem;color:#6c757d;text-align:center;line-height:1.6;">
            Ce certificat atteste que le document ci-dessus a été vérifié et authentifié par le système
            International Registration Server (IRS). Il confirme l'authenticité et la validité du document
            dans notre registre sécurisé international.
        </p>
    </div>

    <div class="cert-footer">
        <div>
            <strong>IRS</strong> — International Registration Server<br>
            internationalregistrationserve@gmail.com
        </div>
        <div style="text-align:right;">
            Certifié le : <?= $today ?><br>
            www.irs-server.com
        </div>
    </div>
</div>
</body>
</html>
