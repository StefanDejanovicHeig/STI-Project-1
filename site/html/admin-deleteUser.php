<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] === false){
    header("location: login.php");
    exit;
}
if (isset($_SESSION["isNotAdmin"]) && $_SESSION["isNotAdmin"] === 1){
    header("location: index.php");
    exit;
}
require_once("includes/util.inc.php");

require_once("connection.php");

//vérifie qu'il y ait un user à supprimer
if (isset($_GET['delete_id_login'])) {
    try{
        //permet de garder l'utilisateur dans la base pour les messages, mais n'apparait plus sur le site
        $strSQLRequest = "UPDATE Utilisateur SET supprimer = '1' WHERE id_login = ?";
        $stmt = $pdo->prepare($strSQLRequest);
        $stmt->execute([test_input($_GET['delete_id_login'])]);
        //si l'utilisateur se supprime lui même il est redirigé sur la page de logout, puis login
        if (test_input($_GET['delete_id_login']) === $_SESSION['id']){
            header("Location: logout.php");
        } else {
            header('Location: admin.php');
        }
    } catch(PDOException $e){
        header("Location: 404.php");
    }
} else {
    header("Location: 404.php");
}
?>