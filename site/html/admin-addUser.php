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
require_once("connection.php");
require_once("includes/util.inc.php");

$exist = 0;
//séléctionne tous les utilisateurs pour vérifier lors de l'ajout que le login n'est pas déjà pris
try {
    $strSQLRequest = "SELECT id_login, login FROM Utilisateur";
    $stmt = $pdo->prepare($strSQLRequest);
    $stmt->execute();
    $userExist = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

} catch (PDOException $e) {
    header("Location: 404.php");
}

//partie permettant la modification d'utilisateurs
if(isset($_POST['edit'])){
    
    // Vérification token anti-csrf
    if($_SESSION['token'] == $_POST['token']){
        $session = true;
    } else{
        $session = false;
    }
    
    //vérifie que l'utilisateur a un id
    if ($session == true && isset($_POST['id_login'])){
        $_GET['edit_id_login'] = $_POST['id_login'];
        //vérifie si l'utilisateur existe déjà
        foreach ($userExist as $user){
            if (test_input($_POST['login']) === $user['login'] && $_POST['id_login'] != $user['id_login']){
                $exist = 1;
            }
        }
        if ($exist === 0){
            try {
                //modification avec mdp ou sans
                if (isset($_POST['password']) && $_POST['password'] != "") {
                    $hashPassword = password_hash(test_input($_POST['password']), PASSWORD_DEFAULT);
                    $strSQLRequest = "UPDATE Utilisateur SET password = ?, valide = ?, id_role = ? WHERE id_login = ?";
                    $stmt = $pdo->prepare($strSQLRequest);
                    $stmt->execute([$hashPassword, $_POST['valide'], $_POST['Role'], $_POST['id_login']]);
                } else {
                    $strSQLRequest = "UPDATE Utilisateur SET valide = ?, id_role = ? WHERE id_login = ?";
                    $stmt = $pdo->prepare($strSQLRequest);
                    $stmt->execute([$_POST['valide'], $_POST['Role'], $_POST['id_login']]);
                }
            } catch (PDOException $e) {
                header("Location: 404.php");
            }

            if ($_POST['id_login'] == $_SESSION["id"]) {
                //gère le fait qu'un admin peut se changer ses droits ainsi que désactivé son compte et donc enlève l'accès
                // à la page d'administration et/ou renvoie à la page de login et deconnecte la session
                if($_POST['Role'] === '2') {
                    $_SESSION["isNotAdmin"] = 1;
                }

                if ($_POST['valide'] == 0) {
                    header("Location: logout.php");
                }
            }

            header("Location: admin.php");

        } else {
            $error = "Ce login est déjà pris. Veuillez en choisir un autre";
        }
    } else {
        header("Location: 404.php");
    }{

    }
}

//récupère les donnée de l'utilisateur qu'on veut modifier en cliquant sur modifier sur la page admin
if (isset($_GET['edit_id_login'])) {
    try{
        $idLoginToEdit = test_input($_GET['edit_id_login']);
        $strSQLRequest = "SELECT id_login, login, valide, nom_role, Utilisateur.id_role FROM Utilisateur
            INNER JOIN Role ON Utilisateur.id_role = Role.id_role
            WHERE id_login LIKE ?";
        $stmt = $pdo->prepare($strSQLRequest);
        $stmt->execute([$idLoginToEdit]);
        $userToEdit = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
    } catch (PDOException $e) {
        header("Location: 404.php");
    }
}

//partie permettant l'ajout d'utilisateurs
if(isset($_POST['add'])){
    
    // Vérification token anti-csrf
    if($_SESSION['token'] == $_POST['token']){
        $session = true;
    } else{
        $session = false;
    }

    //vérifie si l'utilisateur existe déjà
    foreach ($userExist as $user){
        if (test_input($_POST['login']) === $user['login']){
            $exist = 1;
        }
    }
    if ($exist === 0 && $session == true){
        $login = test_input($_POST['login']);
        //vérifie qu'il y ai un login est un mdp renseigné dans les champs prévus
        if (isset($login) && $login != "") {
            if (isset($_POST['password']) && $_POST['password'] != "") {
                try {
                    $hashPassword = password_hash(test_input($_POST['password']), PASSWORD_DEFAULT);
                    $strSQLRequest ="INSERT INTO Utilisateur (login, password, valide, supprimer, id_role) VALUES (?,?,?,?,?)";
                    $stmt= $pdo->prepare($strSQLRequest);
                    $stmt->execute([$login, $hashPassword, $_POST['valide'], 0, $_POST['Role']]);
                    //header("Location: admin.php");
                } catch (PDOException $e) {
                    header("Location: 404.php");
                }
            } else {
                $error = "Vous devez entrer un mot de passe !";
            }
        } else {
            $error = "Vous devez entrer un login !";
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

                        <div class='text-center'>
                        <h1 class='h4 text-gray-900 mb-4'><?php echo (isset($userToEdit['login'])) ? "Modification de l'utilisateur" : "Ajout d'un utilisateur"; ?></h1>
                        </div>
                        <form method='post' action='admin-addUser.php' class='user'>
                            <div class='form-group row'>
                                <div class='col-sm-6 mb-3 mb-sm-0'>
                                    <?php echo (isset($userToEdit['login'])) ? "<input type='hidden' name='id_login' value='".$userToEdit['id_login']."'>" : "";
                                    if (isset($userToEdit['login'])) {
                                        echo "<input type='text' class='form-control form-control-user' placeholder='Login' name='login' value='".$userToEdit['login']."' disabled >";
                                    } else {
                                        echo "<input type='text' class='form-control form-control-user' placeholder='Login' name='login' value='".$_POST['login']."'>";
                                    }


                                    ?>

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
                            <input type="hidden" name="token" id="token" value="<?php echo $_SESSION['token']; ?>" />                    
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



