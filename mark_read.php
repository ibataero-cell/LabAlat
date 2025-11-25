<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) { header("Location: dashboard.php"); exit; }

try {
    $stmt = $pdo->prepare("SELECT link FROM tbl_notifikasi WHERE notif_id = ? AND user_id = ?");
    $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
    $notif = $stmt->fetch();

    if ($notif) {
        $pdo->prepare("UPDATE tbl_notifikasi SET is_read = 1 WHERE notif_id = ?")->execute([$_GET['id']]);
        header("Location: " . $notif['link']);
        exit;
    }
} catch (Exception $e) {}
header("Location: dashboard.php");
?>