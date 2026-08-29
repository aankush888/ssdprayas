<?php
/** Gated resume download — the uploads folder itself is closed to the web. */
require_once __DIR__ . '/_auth.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT full_name, resume_path FROM job_applications WHERE id = ?");
$stmt->execute([$id]);
$app = $stmt->fetch();

if (!$app || !$app['resume_path']) {
    http_response_code(404);
    exit('Resume not found.');
}

$base = realpath(UPLOAD_RESUMES);
$file = realpath(__DIR__ . '/../' . $app['resume_path']);

// Never serve anything outside the resumes folder.
if ($base === false || $file === false || strncmp($file, $base, strlen($base)) !== 0 || !is_file($file)) {
    http_response_code(404);
    exit('Resume file is missing on the server.');
}

$ext  = strtolower(pathinfo($file, PATHINFO_EXTENSION));
$mime = ['pdf' => 'application/pdf',
         'doc' => 'application/msword',
         'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'][$ext] ?? 'application/octet-stream';

$safe_name = preg_replace('/[^A-Za-z0-9._-]/', '_', $app['full_name']) . '-resume.' . $ext;

header('Content-Type: ' . $mime);
header('Content-Disposition: inline; filename="' . $safe_name . '"');
header('Content-Length: ' . filesize($file));
header('X-Content-Type-Options: nosniff');
readfile($file);
