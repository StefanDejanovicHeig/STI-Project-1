<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] === false){
    header("location: login.php");
    exit;
}
require_once("includes/util.inc.php");
require_once ("connection.php");
//modifie le mot de passe si le champs est rempli seulement
if (isset($_POST['password']) && $_POST['password'] != "" && $_SESSION['token'] == $_POST['token']) {
    try {
        $hashPassword = password_hash(test_input($_POST['password']), PASSWORD_DEFAULT);
        $strSQLRequest = "UPDATE Utilisateur SET password = ?WHERE id_login = ?";
        $stmt = $pdo->prepare($strSQLRequest);
        $stmt->execute([$hashPassword, $_SESSION["id"]]);
    } catch (PDOException $e) {
        header("Location: 404.php");
    }
}
//après modification, redirige sur l'index après 5 secondes
if (isset($_POST['edit'])) {
    header( "refresh:5;url=index.php" );
}

include_once('includes/header.inc.php');
?>
        <!-- Begin Page Content -->
        <div class="container-fluid">

            <div class="form-group row">
                <div class="col-lg-6">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Login :</h6>
                        </div>
                        <div class="card-body"> <?php echo $_SESSION["login"]; ?> </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Rôle :</h6>
                        </div>
                        <div class="card-body"> <?php echo $_SESSION["role"]; ?> </div>
                    </div>
                </div>
            </div>
            <form class="user" method='post' action='profile.php'>
                <div class="form-group row">
                    <div class="col-lg-9">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Changer de mot de passe :</h6>
                            </div>
                            <div class="card-body">
                                <input type="password" id="password" name="password" class="form-control form-control-user" placeholder="Mot de passe" onfocus="checkEmpty()" onblur="checkEmpty()" onkeyup="checkEmpty()">
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="token" id="token" value="<?php echo $_SESSION['token']; ?>" />
                <input type='submit' id="profile" name='edit' class='btn btn-primary btn-user btn-block' value='Modifier' disabled>
                <?php
                    if (isset($_POST["edit"]) && isset($hashPassword)) {
                        echo "<br><div class=\"col-lg-12\">
                                <div class=\"card shadow mb-4\">
                                    <div class=\"card-header py-3\">
                                        <h6 class='m-0 font-weight-bold text-primary'>Votre mot de passe a été changé</h6>
                                    </div>
                                </div>
                            </div>";
                    }
                ?>
            </form>

        </div>
        <!-- /.container-fluid -->

    </div>
    <!-- End of Main Content -->
  <script>

      //désactive le boutton tant que le champs mdp n'est pas renseigné
      function checkEmpty(){
          if(document.getElementById('password').value != ""){
              document.getElementById('profile').disabled = "";
          }
          else{
              document.getElementById('profile').disabled = "disabled";
          }
      }

  </script>



<?php
include_once('includes/footer.inc.php');
?>