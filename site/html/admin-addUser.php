<?php

session_start();

if(!isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === false){
    header("location: login.php");
    exit;
}
if (isset($_SESSION["isNotAdmin"]) && $_SESSION["isNotAdmin"] === 1){
    header("location: index.php");
    exit;
}
require_once("connection.php");

$type = "ajout";
$exist = 0;

if (isset($_GET['edit_id_login'])) {
    try{
        $idLoginToEdit = $_GET['edit_id_login'];
        $strSQLRequest = "SELECT id_login, login, valide, nom_role, Utilisateur.id_role FROM Utilisateur
            INNER JOIN Role ON Utilisateur.id_role = Role.id_role
            WHERE id_login LIKE '".$_GET['edit_id_login']."'";
        $stmt = $pdo->query($strSQLRequest);
        $userToEdit = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
    } catch (PDOException $e) {
        header("Location: 404.php");
        die("ERREUR: " . $e->getMessage());
    }
}

try {
    $strSQLRequest = "SELECT id_login, login FROM Utilisateur";
    $stmt = $pdo->query($strSQLRequest);
    $userExist = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

} catch (PDOException $e) {
    header("Location: 404.php");
    die("ERREUR: " . $e->getMessage());
}

if(isset($_POST['edit'])){
    if (isset($_POST['id_login'])){
        foreach ($userExist as $user){
            if ($_POST['login'] === $user['login'] && $_POST['id_login'] != $user['id_login']){
                $exist = 1;
            }
        }
        if ($exist === 0){
            try {
                if (isset($_POST['password'])) {
                    $hashPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $strSQLRequest = "UPDATE Utilisateur SET login = ?, password = ?, valide = ?, id_role = ? WHERE id_login = ?";
                    $stmt = $pdo->prepare($strSQLRequest);
                    $stmt->execute([$_POST['login'], $hashPassword, $_POST['valide'], $_POST['Role'], $_POST['id_login']]);
                } else {
                    $strSQLRequest = "UPDATE Utilisateur SET login = ?, valide = ?, id_role = ? WHERE id_login = ?";
                    $stmt = $pdo->prepare($strSQLRequest);
                    $stmt->execute([$_POST['login'], $_POST['valide'], $_POST['Role'], $_POST['id_login']]);
                }
            } catch (PDOException $e) {
                header("Location: 404.php");
                die("ERREUR: " . $e->getMessage());
            }

            header("Location: admin.php");
        } else {
            $error = "Ce login est déjà pris. Veuillez en choisir un autre";
        }
    } else {
        header("Location: 404.php");
    }
}

if(isset($_POST['add'])){
    foreach ($userExist as $user){
        if ($_POST['login'] === $user['login']){
            $exist = 1;
        }
    }
    if ($exist === 0){
        try {
            $hashPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $strSQLRequest ="INSERT INTO Utilisateur (login, password, valide, id_role) VALUES (?,?,?,?)";
            $stmt= $pdo->prepare($strSQLRequest);
            $stmt->execute([$_POST['login'], $hashPassword, $_POST['valide'], $_POST['Role']]);
            header("Location: admin.php");
        } catch (PDOException $e) {
            header("Location: 404.php");
            die("ERREUR: " . $e->getMessage());
        }
    } else {
        $error = "Ce login est déjà pris. Veuillez en choisir un autre";
    }
}

include_once('includes/header.inc.php');
?>

    <!-- Begin Page Content -->
    <div class="container-fluid">

        <div class="card o-hidden border-0 shadow-lg my-5">
            <div class="card-body p-0">
                <!-- Nested Row within Card Body -->
                <div class="p-5">


                <?php echo (isset($userToEdit['login'])) ? "" : ""; ?>

                        <div class='text-center'>
                        <h1 class='h4 text-gray-900 mb-4'><?php echo (isset($userToEdit['login'])) ? "Modification de l'utilisateur" : "Ajout d'un utilisateur"; ?></h1>
                        </div>
                        <form method='post' action='admin-addUser.php' class='user'>
                            <div class='form-group row'>
                                <div class='col-sm-6 mb-3 mb-sm-0'>
                                    <?php echo (isset($userToEdit['login'])) ? "<input type='hidden' name='id_login' value='".$userToEdit['id_login']."'>" : ""; ?>
                                    <input type='text' class='form-control form-control-user' placeholder='Login' name='login' <?php echo (isset($userToEdit['login'])) ? "value='".$userToEdit['login']."'" : ""; ?> >
                                </div>
                                <div class='col-sm-2'>
                                    <label class='text-lg'> choisir un rôle :</label>
                                </div>
                                <div class='col-sm-4'>
                                    <select name='Role' class='form-control'>
                                        <?php
                                        try{
                                            $strSQLRequest = "SELECT id_role, nom_role FROM Role ORDER BY nom_role";
                                            $stmt = $pdo->query($strSQLRequest);
                                            $tabRoles = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                            $stmt->closeCursor();
                                        } catch (PDOException $e) {
                                            header("Location: 404.php");
                                            die("ERREUR: " . $e->getMessage());
                                        }

                                        foreach ($tabRoles as $role){
                                            echo '<option value="'.$role['id_role'].'"';
                                            if (isset($userToEdit['login']) && $userToEdit['id_role'] == $role['id_role']){
                                                echo 'selected = "selected"';
                                            }
                                            echo '>'.$role['nom_role'].'</option>';
                                        }
                                    echo "</select>";
                                        ?>
                                </div>
                            </div>
                            <div class='form-group'>
                            <div class='col-sm-2'>
                                </div>
                                <div class='col-sm-4'>
                                    <select name='valide' class='form-control'>
                                    <option value='1' <?php
                                        if (isset($userToEdit['login']) && $userToEdit['valide'] === "1"){
                                            echo "selected = 'selected'";
                                        }
                                        echo "> Compte activé</option>
                                    <option value='0'";
                                        if (isset($userToEdit['login']) && $userToEdit['valide'] === "0"){
                                            echo "selected = 'selected'";
                                        }
                                        echo "> Compte désactivé</option>
                                    </select>"; ?>
                                </div>
                            </div>
                            <div class='form-group row'>
                                <div class='col-sm-12 mb-3 mb-sm-0'>
                                    <input type='password' class='form-control form-control-user' placeholder='<?php echo (isset($userToEdit['login'])) ? "Changer le mot de passe ?" :"Mot de passe"; ?>' name='password'>
                                </div>
                            </div>

                            <input type='submit' name='<?php echo (isset($userToEdit['login'])) ? "edit" : "add"; ?>' class='btn btn-primary btn-user btn-block' value='<?php echo (isset($userToEdit['login'])) ? "Modifier" : "Ajouter"; ?>'>
                            <?php echo (isset($error)) ? "<div class='col-sm-12'>
                                    <label class='text-lg'>".$error."</label>
                                </div>
                                </form>" : ""; ?>

                </div>
            </div>


        </div>
    <!-- /.container-fluid -->

</div>
<!-- End of Main Content -->

<?php
include_once('includes/footer.inc.php');
?>



