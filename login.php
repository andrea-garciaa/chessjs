<?php
/**
 * Created by PhpStorm.
 * User: g17006433
 * Date: 06/03/19
 * Time: 15:05
 */

session_start();

$retour = new stdClass();
$retour->success = false;
$retour->message = "Erreur inconnue. La connexion a échoué";

if (isset($_POST['nick_or_mail']) && isset($_POST['pass'])) {
    $ismail = false;
    $nick_or_mail = $_POST['nick_or_mail'];
    $pass = $_POST['pass'];

    if (strchr($nick_or_mail, '@'))
        $ismail = true;

    try
    {
        $bd = new PDO('mysql:host=<dbhostname>;dbname=<dbname>', '<dblogin>', '<dbpass>');
        $bd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $loginmethod = $ismail ? 'email' : 'nick';

        $sql = 'SELECT id, nick, email FROM accounts WHERE ' . $loginmethod . ' = :' . $loginmethod . ' AND pass = :pass;';
        $stmt = $bd->prepare($sql);
        $stmt->bindValue(':' . $loginmethod, $nick_or_mail);
        $stmt->bindValue(':pass', $pass);

        if ($stmt->execute())
        {
            if ($stmt->rowCount() != 0)
            {
                $stmt->setFetchMode(PDO::FETCH_OBJ);
                if ($result = $stmt->fetch())
                {
                    $_SESSION['account'] = $result;
                    $retour->success = true;
                    unset($retour->message);
                }
                else
                    $retour->message = $ismail ? 'E-mail ou mot de passe incorrect.' : 'Pseudo ou mot de passe incorrect.';
            }
            else
                $retour->message = $ismail ? 'E-mail ou mot de passe incorrect.' : 'Pseudo ou mot de passe incorrect.';
        }

    } catch (PDOException $ex)
    {
        echo $ex;
        //$retour->phperr = $ex;
    }
}


header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');

echo json_encode($retour);