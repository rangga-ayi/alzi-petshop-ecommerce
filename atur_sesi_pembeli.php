<?php
session_start();
$_SESSION['level'] = 'pembeli';
header('Location: index.php');
exit;
?>