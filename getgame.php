<?php
/**
 * Created by PhpStorm.
 * User: Andrea
 * Date: 30/03/2019
 * Time: 15:13
 */


// retourne les données de la partie actuelle du joueur

session_start();
$retour = new stdClass();
$retour->success = false;
$retour->message = "Impossible de récupérer la partie";

if (isset($_GET['gameid']))
{
    try
    {
        $bd = new PDO('mysql:host=<dbhostname>;dbname=<dbname>', '<dblogin>', '<dbpass>');
        $bd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = 'SELECT wonid, whiteid, blackid, pieces FROM games WHERE id = :gameid;';
        $stmt = $bd->prepare($sql);
        $stmt->bindValue(':gameid', $_GET['gameid']);

        if ($stmt->execute())
        {
            if ($stmt->rowCount() != 0)
            {
                $stmt->setFetchMode(PDO::FETCH_OBJ);
                if ($result = $stmt->fetch())
                {
                    $retour->success = true;
                    unset($retour->message);

                    $isplayer = (isset($_GET['play']) && isset($_SESSION['account']) && isset($_SESSION['account']->gameid) && $_SESSION['account']->gameid == $_GET['gameid']);

                    if ($isplayer)
                    {
                        $iswhite = $result->whiteid == $_SESSION['account']->id;
                        $retour->iswhite =  $iswhite;
                        $retour->opponent = $iswhite ? $result->blackid : $result->whiteid; // si le client est le joueur blanc, son adversaire est le joueur noir, sinon c'est le joueur blanc
                    }
                    else
                    {
                        $retour->iswhite = true;
                        $retour->whiteid = $result->whiteid;
                        $retour->blackid = $result->blackid;
                    }

                    $retour->wonid = $result->wonid;
                    $retour->pieces = json_decode($result->pieces); // le tableau des pièces est stocké en texte JSON dans la BD
                }
                else
                    $retour->message = "Partie introuvable";
            }
            else
                $retour->message = "Partie introuvable";
        }

    } catch (PDOException $ex)
    {
        //$retour->phperr = $ex;
        echo $ex;
    }
}

header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');

echo json_encode($retour);