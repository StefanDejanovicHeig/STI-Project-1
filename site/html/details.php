<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] === false){
    header("location: login.php");
    exit;
}

require_once("includes/util.inc.php");
require_once "connection.php";

// Récupère les informations liées au message séléctionné
try {
    $sql = "SELECT Message.date, Utilisateur.login, Message.sujet, Message.corps FROM Message INNER JOIN Utilisateur
            ON Message.expediteur = Utilisateur.id_login WHERE Message.id_message = ? AND Message.recepteur = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([test_input($_GET["id"]), $_SESSION["id"]]);
    $result = $stmt->fetch(PDO::FETCH_OBJ);
    if (empty($result)) {
        throw new PDOException("Pas de message à afficher");
    }
} catch (PDOException $e) {
    header("Location: 404.php");
}

include_once('includes/header.inc.php');
echo '<div class="container-fluid" >';

if(!empty($result)) {
    $rowArray = array("Date de réception:","Expéditeur:","Sujet:","Message:");
    $sqlRow = array("date", "login", "sujet", "corps");

    $valueArray = array();
    $cpt = 0;
    // Ajoute les informations de chaque colonne du message dans un tableau
    for ($i = 0; $i < count($sqlRow); $i++){
        array_push($valueArray, $result->$sqlRow[$cpt++]);
    }

    // Chaque info du message est affichée en formatant l'affichage
    for ($i = 0; $i < count($rowArray); $i++) {
        $sizeOfBloc = $i > 1 ? 9 : 3;
                echo'<div class="col-lg-' . $sizeOfBloc .'">
                    <div class="card shadow mb-4" >
                        <div class="card-header py-3" >
                            <h6 class="m-0 font-weight-bold text-primary" >' . $rowArray[$i] . '</h6 >
                        </div >
                    <div class="card-body" >' . nl2br($valueArray[$i]) . '</div >
                </div>
              </div >';
    }
}
else{
            echo'<div class="col-lg-3" >
                <div class="card shadow mb-4" >
                    <div class="card-header py-3" >
                        <h6 class="m-0 font-weight-bold text-primary" > Erreur:</h6 >
                    </div >
                    <div class="card-body" >Aucun mail à afficher</div >
                </div >
            </div >';
}

echo "<a href='sendMail.php?id=" . test_input($_GET["id"]) . "' class='btn btn-primary btn-user btn-block btn-dark'>répondre</a>";
echo "<a href='deleteMail.php?id=" . test_input($_GET["id"]) . "' class='btn btn-primary btn-user btn-block btn-google'>supprimer</a>";
echo '<a href="index.php" class="btn btn-primary btn-user btn-block">Retour</a>';
echo '</div >';

include_once('includes/footer.inc.php');
?>