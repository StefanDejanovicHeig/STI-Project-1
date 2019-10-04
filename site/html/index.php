
<?php
    session_start();

    if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
        header("location: login.php");
        exit;
    }


    require_once "connection.php";

    $sql = "SELECT Message.id_Message, Message.date, Utilisateur.login, Message.sujet FROM Message INNER JOIN Utilisateur
            ON Message.expediteur = Utilisateur.id_login WHERE Message.recepteur = " . $_SESSION["id"] .
            " ORDER BY Message.date ASC";

    $stmt = $pdo->query($sql);
    $tabMessages = $stmt->fetchAll(PDO::FETCH_ASSOC);


    include_once('includes/header.inc.php');
?>
        <!-- Begin Page Content -->
        <div class="container-fluid">

          <!-- DataTales Example -->
          <div class="card shadow mb-4">
            <div class="card-header py-3">
              <h6 class="m-0 font-weight-bold text-primary">Messages reçus</h6>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                  <thead>
                  <tr>
                    <th>Date de réception</th>
                    <th>Expéditeur</th>
                    <th>Sujet</th>
                    <th>Réponse</th>
                    <th>Suppression</th>
                    <th>Plus d'informations</th>
                  </tr>
                  </thead>
                  <tfoot>
                  <tr>
                      <th>Date de réception</th>
                      <th>Expéditeur</th>
                      <th>Sujet</th>
                      <th>Réponse</th>
                      <th>Suppression</th>
                      <th>Plus d'informations</th>
                  </tr>
                  </tfoot>
                  <tbody>
                  <?php
                      foreach($tabMessages as $mess){
                          echo "<tr><td>" . $mess['date'] . "</td><td>"
                              . $mess['login'] . "</td><td>" . $mess['sujet'] . "</td>
                              <td><a href='sendMail.php'>répondre</a></td>
                              <td><a href='deleteMail.php'>supprimer</a></td>
                              <td><a href='details.php?id=" . $mess['id_Message'] . "'>détails</a></td></tr>";
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