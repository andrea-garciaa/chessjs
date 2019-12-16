<?php
/**
 * Created by PhpStorm.
 * User: g17006433
 * Date: 06/03/19
 * Time: 15:06
 */

session_start();

$retour = new stdClass();
$retour->success = false;
$retour->message = "Erreur interne. L'enregistrement du compte a échoué.";

if (isset($_POST['nick']) && isset($_POST['email']) && isset($_POST['pass'])) {
    $nick = $_POST['nick'];
    $email = $_POST['email'];
    $pass = $_POST['pass'];

    if (strchr($email, '@'))
    {
        try
        {
            $bd = new PDO('mysql:host=<dbhostname>;dbname=<dbname>', '<dblogin>', '<dbpass>');
            $bd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = 'INSERT INTO accounts(nick, email, pass) VALUES (:nick, :email, :pass);';
            $stmt = $bd->prepare($sql);
            $stmt->bindValue(':nick', $nick);
            $stmt->bindValue(':email', $email);
            $stmt->bindValue(':pass', $pass);

            if ($stmt->execute() == true)
                $retour->success = true;
            else
                $retour->message = "L'enregistrement du compte a échoué. Vos identifiants sont peut-être déjà pris.";

        } catch (PDOException $ex)
        {
            echo $ex;
            //$retour->phperr = $ex;
        }
    }
    else
        $retour->message = 'Adresse e-mail incorrecte';
}


header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');

echo json_encode($retour);