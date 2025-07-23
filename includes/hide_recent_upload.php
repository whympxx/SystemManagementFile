<?php
session_start();
header('Content-Type: application/json');

if (!isset($_POST['file'])) {
    echo json_encode(['success' => false, 'error' => 'No file specified']);
    exit;
}

$file = $_POST['file'];
if (!isset($_SESSION['hidden_recent_uploads'])) {
    $_SESSION['hidden_recent_uploads'] = array();
}
if (!in_array($file, $_SESSION['hidden_recent_uploads'])) {
    $_SESSION['hidden_recent_uploads'][] = $file;
}
echo json_encode(['success' => true]); 