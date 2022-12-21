<?php
$users = getAllUsers($databaseConnection);

if (isset($_POST['deleteAccount']['password'])) {
    $_SESSION['deleteAccount']['password'] = $_POST['deleteAccount']['password'];
    unset($_POST['deleteAccount']['password']);
}

if (isset($_POST['update'])) {
    $updated = $_POST['edit'];
//    print_r($updated);
    editUser($updated['firstname'], $updated['prefixName'], $updated['surname'], $updated['birthDate'], $updated['phone'], $updated['street'], $updated['housenumber'], $updated['postcode'], $updated['city'], $updated['id'], $databaseConnection);
    $users = getAllUsers($databaseConnection);
}

if (isset($_POST['deleteID']) && isset($_SESSION['deleteAccount']['password'])) {
    $deleteID = $_POST['deleteID'];
    $password = $_SESSION['deleteAccount']['password'];
    $hashedPassword = $_SESSION['account']['password'];

    if ($deleteID != $_SESSION['account']['id'] && password_verify($password, $hashedPassword)) {
        unset($_SESSION['deleteAccount']['password']);
//        deleteUser($deleteID, $databaseConnection);
        print("<div class='alert alert-success' role='alert'>
            Account is verwijderd
        </div>");
    } else {
        print("<div class='alert alert-danger' role='alert'>
            Er is iets misgegaan.
        </div>");
        unset($_SESSION['deleteAccount']['password']);
    }
}
?>

<div class="users">
    <div class="users__header">
        <h1>Account beheer</h1>
        <hr>
        <p>Zie hieronder alle geregistreerde gebruikers met de bijbehorende te kunnen bewerken informatie.</p>
        <?php if (isset($_POST['editID'])):
            $userID = $_POST['editID'];
            $userResult = getUser($userID, $databaseConnection); ?>
            <hr>
            <form action="" method="post" class="edit-data form__form">

                <input type="text" name="edit[id]" value="<?= @$userResult[0]['id'] ?>" style="display: none">

                <div class="form__form-row form__form-row--40">
                    <input class="input" type="text" name="edit[firstname]" value="<?= @$userResult[0]['voornaam'] ?>" placeholder="Voornaam">
                </div>

                <div class="form__form-row form__form-row--20">
                    <input class="input" type="text" name="edit[prefixName]" value="<?= @$userResult[0]['tussenvoegsel'] ?>" placeholder="Tussenvoegsel">
                </div>

                <div class="form__form-row form__form-row--40">
                    <input class="input" type="text" name="edit[surname]" value="<?= @$userResult[0]['achternaam'] ?>" placeholder="Achternaam">
                </div>

                <div class="form__form-row form__form-row--100">
                    <input class="input" type="date" name="edit[birthDate]" value="<?= @$userResult[0]['geboortedatum'] ?>" placeholder="Datum">
                </div>

                <div class="form__form-row form__form-row--50">
                    <input class="input" type="text" name="edit[phone]" value="<?= @$userResult[0]['telefoonnummer'] ?>" placeholder="Telefoonnummer">
                </div>

                <div class="form__form-row form__form-row--40">
                    <input class="input" type="text" name="edit[street]" value="<?= @$userResult[0]['straat'] ?>" placeholder="Straat">
                </div>

                <div class="form__form-row form__form-row--10">
                    <input class="input" type="text" name="edit[housenumber]" value="<?= @$userResult[0]['huisnummer'] ?>" placeholder="Huisnummer">
                </div>

                <div class="form__form-row form__form-row--50">
                    <input class="input" type="text" name="edit[postcode]" value="<?= @$userResult[0]['postcode'] ?>" placeholder="Postcode">
                </div>

                <div class="form__form-row form__form-row--50">
                    <input class="input" type="text" name="edit[city]" value="<?= @$userResult[0]['stad'] ?>" placeholder="Stad">
                </div>

                <div class="form__form-row form__form-row--100">
                    <input type="submit" name="update" value="Update" class="btn btn--primary">
                </div>

<!--                <input type="email" name="edit[email]" value="--><?php //= @$userResult[0]['email'] ?><!--" placeholder="Email">-->
            </form>
        <?php endif; ?>

        <form class="users__delete-form" action="" method="post" id="passwordForm" <?php if (!isset($_POST['deleteID']) || isset($_POST['deleteAccount']['password'])): ?> style="display: none" <?php endif; ?>>
            <input type="text" name="deleteID" value="<?= $_POST['deleteID'] ?>" style="display: none">
            <input class="input" type="password" name="deleteAccount[password]" placeholder="Wachtwoord">
            <input class="btn btn--primary" type="submit" name="checkPassword" value="Wachtwoord checken">
        </form>

    </div>
    <table class="table">
        <thead>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Naam</th>
                <th scope="col">E-mail</th>
                <th scope="col">Geboortedatum</th>
                <th scope="col">Telefoonnummer</th>
                <th scope="col">Adres</th>
                <th scope="col">Acties</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user):
                $name = $user['voornaam'] . ' ' . $user['achternaam'];
                $address = $user['straat'] . ' ' . $user['huisnummer'] . ', ' . $user['postcode'] . ' ' . $user['stad'];

                if (isset($user['tussenvoegsel'])):
                    $name = $user['voornaam'] . ' ' . $user['tussenvoegsel'] . ' ' . $user['achternaam'];
                endif;
                ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td><?= $name ?></td>
                    <td><?= $user['email'] ?></td>
                    <td><?= $user['geboortedatum'] ?></td>
                    <td><?= $user['telefoonnummer'] ?></td>
                    <td><?= $address ?></td>
                    <td>
                        <form action="" method="post">
                            <input type="text" name="editID" value="<?= $user['id'] ?>" style="display: none">
                            <input class="btn btn--small btn--primary" type="submit" value="Edit">
                        </form>
                        <?php if ($user['id'] != $_SESSION['account']['id']): ?>
                            <form action="" method="post">
                                <input type="text" name="deleteID" value="<?= $user['id'] ?>" style="display: none">
                                <input class="btn btn--small btn--primary" type="submit" value="Delete">
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>