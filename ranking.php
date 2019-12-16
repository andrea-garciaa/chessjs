<?php
/**
 * Created by PhpStorm.
 * User: Andrea
 * Date: 31/03/2019
 * Time: 22:35
 */

session_start();

$retour = new stdClass();
$retour->success = false;

try
{
    $bd = new PDO('mysql:host=<dbhostname>;dbname=<dbname>', '<dblogin>', '<dbpass>');
    $bd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $bd->query('SELECT nick, wins, losses FROM accounts ORDER BY wins DESC, losses;');

    if ($stmt->execute() && $stmt->rowCount() != 0)
    {
        $retour->results = array();
        $retour->success = true;

        $stmt->setFetchMode(PDO::FETCH_OBJ);
        while ($result = $stmt->fetch())
        {
            array_push($retour->results, $result);
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
