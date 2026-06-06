<?php
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

$doc_number = trim($conn->real_escape_string($_POST['doc_number'] ?? ''));

if (empty($doc_number)) {
    echo json_encode(['found' => false, 'error' => 'Missing document number']);
    exit();
}

$result = $conn->query("SELECT * FROM documents WHERE document_number = '$doc_number' AND status = 'verified' LIMIT 1");

if ($result && $result->num_rows > 0) {
    $doc = $result->fetch_assoc();

    $ip = $conn->real_escape_string($_SERVER['REMOTE_ADDR'] ?? '');
    $conn->query("INSERT INTO verifications (document_number, ip_address, result) VALUES ('$doc_number', '$ip', 'verified')");

    $doc['issue_date'] = $doc['issue_date'] ? date('d/m/Y', strtotime($doc['issue_date'])) : '-';

    echo json_encode([
        'found' => true,
        'document' => [
            'document_number'     => $doc['document_number'],
            'holder_name'         => $doc['holder_name'],
            'document_type'       => $doc['document_type'],
            'issuing_organization'=> $doc['issuing_organization'],
            'issue_date'          => $doc['issue_date'],
            'country_of_origin'   => $doc['country_of_origin'],
            'status'              => $doc['status'],
            'file_path'           => $doc['file_path'] ?? '',
        ]
    ]);
} else {
    $ip = $conn->real_escape_string($_SERVER['REMOTE_ADDR'] ?? '');
    $conn->query("INSERT INTO verifications (document_number, ip_address, result) VALUES ('$doc_number', '$ip', 'not_verified')");

    echo json_encode(['found' => false]);
}
