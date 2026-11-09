<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Invalid document ID.");
}

$document_id = (int)$_GET["id"];

$sql = "SELECT d.file_name, s.user_id
        FROM documents d
        INNER JOIN applications a ON d.application_id = a.application_id
        INNER JOIN students s ON a.student_id = s.student_id
        WHERE d.document_id = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $document_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$document = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$document) {
    die("Document not found.");
}

if ($_SESSION["role"] !== "admin" && (int)$document["user_id"] !== (int)$_SESSION["user_id"]) {
    die("You are not allowed to download this document.");
}

$file_name = basename($document["file_name"]);
$file_path = __DIR__ . "/uploads/" . $file_name;

if (!is_file($file_path)) {
    die("The document file does not exist.");
}

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"" . $file_name . "\"");
header("Content-Length: " . filesize($file_path));

readfile($file_path);
exit;
?>