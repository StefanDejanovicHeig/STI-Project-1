<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] === false){
    header("location: login.php");
    exit;
}

require_once("includes/util.inc.php");
require_once "connection.php";

$destination = $subject = $message = "";

// Si un id de message est donné en url, on va répondre au message et donc préparer un corps spécial réponse
if(isset($_GET['id'])){
    try{
        $sql = "SELECT Utilisateur.login, Message.date, Message.sujet, Message.corps FROM Message INNER JOIN Utilisateur
            ON Message.expediteur = Utilisateur.id_login WHERE Message.id_message = ? AND Message.recepteur = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([test_input($_GET["id"]), $_SESSION["id"]]);
        $user = $stmt->fetch(PDO::FETCH_OBJ);
        if (empty($user)) {
            throw new PDOException("Pas de message à afficher");
        }
        $destination = $user->login;
        $subject = "Re: " . $user->sujet;
        $message = "\r\n\r\n\r\n---------------------------->Réponse au mail ci-dessous\r\n\r\nEnvoyé le: " . $user->date
            . " \r\nSujet: " . $user->sujet . " \r\n\r\n" . $user->corps;

    } catch (PDOException $e) {
        header("Location: 404.php");
    }
}


$destination_err = $subject_err = $message_err = "";

// Traite le formulaire
if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    // Vérification token anti-csrf
    if($_SESSION["token"] != $_POST["token"]){
        $session = false;
    } else {
        $session = true;
    }

    if(empty(test_input($_POST["destination"]))){
        $destination_err = "Entrez un destinataire";
    } else{
        $destination = test_input($_POST["destination"]);
    }
    if(empty(test_input($_POST["subject"]))){
        $subject_err = "Entrez un subject";
    } else{
        $subject = test_input($_POST["subject"]);
    }
    if(empty(test_input($_POST["message"]))){
        $message_err = "Entrez un message";
    } else{
        $message = test_input($_POST["message"]);
    }

    if($session == true && empty($destination_err) && empty($subject_err) && empty($message_err)) {
        
        // Récupère les users de la bdd
        try{
            $sql = "SELECT id_login, login, supprimer FROM Utilisateur";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $tabUser = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            header("Location: 404.php");
        }

        $founded = 0;
        // Check si on trouve l'expéditeur parmis les users de la bdd
        foreach($tabUser as $user){
            if($user['login'] === $destination){
                $founded = 1;
                $idLogin = $user['id_login'];
                $deleted = $user['supprimer'];
                break;
            }
        }

        // Si le user est trouvé, on va envoyer le message donc insérer dans la bdd
        if ($founded && !$deleted) {
            try{
                $sql = "INSERT INTO Message (sujet, corps, date, expediteur, recepteur) VALUES (?,?,?,?,?)";
                $stmt= $pdo->prepare($sql);
                date_default_timezone_set('Europe/Zurich');
                $stmt->execute([$subject, $message, date('d-m-Y H:i:s'), $_SESSION['id'], $idLogin]);
            } catch (PDOException $e) {
                header("Location: 404.php");
            }

            header("location: index.php");
            
            

        } else {
            $destination_err = "Pas de compte trouvé avec ce destinataire ";
        }
    }
}

include_once('includes/header.inc.php');

?>


<div class="container-fluid">
    <div class="card o-hidden border-0 shadow-lg my-5">
        <div class="card-body p-0">
            <!-- Nested Row within Card Body -->
                    <div class="p-5">
                        <div class="text-center">
                            <h1 class="h4 text-gray-900 mb-4">Formulaire d'envoi</h1>
                        </div>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="form-group <?php echo (!empty($destination_err)) ? 'has-error' : ''; ?>">
                                <div class="form-group <?php echo (!empty($destination_err)) ? 'has-error' : ''; ?>">
                                    <label>Destinataire</label>
                                    <input type="text" name="destination" class="form-control" value="<?php echo $destination; ?>">
                                    <span class="help-block"><?php echo $destination_err; ?></span>
                                </div>
                                <div class="form-group <?php echo (!empty($subject_err)) ? 'has-error' : ''; ?>">
                                    <label>Sujet</label>
                                    <input type="text" name="subject" class="form-control" value="<?php echo $subject; ?>">
                                    <span class="help-block"><?php echo $subject_err; ?></span>
                                </div>
                                <div class="form-group <?php echo (!empty($message_err)) ? 'has-error' : ''; ?>">
                                    <label>Message</label>
                                    <pre><textarea type="text" name="message" rows="15" class="form-control"><?php echo $message; ?></textarea></pre>
                                    <span class="help-block"><?php echo $message_err; ?></span>
                                </div>
                                <div class="form-group">
                                    <input type="hidden" name="token" id="token" value="<?php echo $_SESSION['token']; ?>" />
                                    <input type="submit" class="btn btn-primary btn-user btn-block" value="Envoyer">
                                </div>
                            </div>
                        </form>

                    </div>
        </div>
    </div>
</div>

</div>
<!-- End of Main Content -->

<?php
include_once('includes/footer.inc.php');
?>
