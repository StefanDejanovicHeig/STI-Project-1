<?php
    session_start();

    if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] === false){
        header("Location: login.php");
        exit;
    }
    if (isset($_SESSION["isNotAdmin"]) && $_SESSION["isNotAdmin"] === 1){
        header('Location: index.php');
        exit;
    }

    require_once("includes/util.inc.php");
    include_once('includes/header.inc.php');
    require_once("connection.php");

    //selectionne tous les utilisateurs non "supprimé" de la base de donnée
    try{
        $strSQLRequest = "SELECT id_login, login, valide, nom_role FROM Utilisateur
                INNER JOIN Role ON Utilisateur.id_role = Role.id_role WHERE supprimer = 0
                ORDER BY login";
        $stmt = $pdo->prepare($strSQLRequest);
        $stmt->execute();
        $tabUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
    } catch (PDOException $e) {
        header("Location: 404.php");
    }
?>

        <!-- Begin Page Content -->
        <div class="container-fluid">

          <!-- DataTales Example -->
          <div class="card shadow mb-4">
            <div class="card-header py-3">
              <h6 class="m-0 font-weight-bold text-primary">Gestion des comptes utilisateurs</h6>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                  <thead>
                    <tr>
                        <th>Login</th>
                        <th>Activé</th>
                        <th>Rôle</th>
                        <th>Modification</th>
                        <th>Suppression</th>
                    </tr>
                  </thead>
                  <tfoot>
                    <tr>
                        <th>Login</th>
                        <th>Activé</th>
                        <th>Rôle</th>
                        <th>Modification</th>
                        <th>Suppression</th>
                    </tr>
                  </tfoot>
                  <tbody>
                    <?php
                    foreach ($tabUsers as $user)
                    {
                        echo "<tr>";
                        echo "<td>".$user["login"]."</td>";
                        if ($user["valide"] == "1"){
                            echo "<td>oui</td>";
                        } else {
                            echo "<td>non</td>";
                        }
                        echo "<td>".$user["nom_role"]."</td>";
                        echo "<td><a href='admin-addUser.php?edit_id_login=".$user["id_login"]."'>modifier</a></td>";
                        echo "<td><a href='admin-deleteUser.php?delete_id_login=". $user["id_login"]."'>supprimer</a></td>";
                        echo "</tr>";
                    }
                    ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

        </div>
        <!-- /.container-fluid -->


      </div>
      <!-- End of Main Content -->

<?php
include_once('includes/footer.inc.php');
?>