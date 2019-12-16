<?php
/**
 * Created by PhpStorm.
 * User: g17006433
 * Date: 01/04/19
 * Time: 12:36
 */

session_start();
$retour = new stdClass();
$retour->success = false;

try
{
    $bd = new PDO('mysql:host=<dbhostname>;dbname=<dbname>', '<dblogin>', '<dbpass>');
    $bd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $valid = false;

    if (isset($_GET['nick']))
    {
        $valid = true;
        $stmt = $bd->prepare('SELECT nick, id, wins, losses FROM accounts WHERE nick LIKE %:nick%');
        $stmt->bindValue(':nick', $_GET['nick']);
    }
    else if (isset($_GET['id']))
    {
        $valid = true;
        $stmt = $bd->prepare('SELECT nick, id, wins, losses FROM accounts WHERE id = :id');
        $stmt->bindValue(':id', $_GET['id']);
    }

    if ($valid && $stmt->execute() && $stmt->rowCount() != 0) {
        $stmt->setFetchMode(PDO::FETCH_OBJ);
        if ($result = $stmt->fetch())
        {
            $retour->success = true;
            $retour->result = $result;
        }
    }


}
catch (PDOException $ex)
{
    echo $ex;
}


header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');

echo json_encode($retour);
