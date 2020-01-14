<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] === false){
    header("location: login.php");
    exit;
}

require_once("includes/util.inc.php");
require_once "connection.php";

// Va supprimer le message de la base de données
try{
    $sql = "DELETE FROM Message WHERE Message.id_message = ? AND Message.recepteur = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([test_input($_GET["id"]), $_SESSION["id"]]);
} catch (PDOException $e) {
    header("Location: 404.php");
}

header("location: index.php");
?>