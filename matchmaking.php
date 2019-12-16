<?php
/**
 * Created by PhpStorm.
 * User: Andrea
 * Date: 30/03/2019
 * Time: 15:12
 */


// retourne les données de la partie actuelle du joueur

session_start();
$retour = new stdClass();
$retour->success = false;
$retour->message = "Erreur dans la création de la partie";

if (isset($_SESSION['account']))
{
    try
    {
        if (isset($_GET['nick']) && strlen($_GET['nick']) == 0)
            unset($_GET['nick']);

        $bd = new PDO('mysql:host=<dbhostname>;dbname=<dbname>', '<dblogin>', '<dbpass>');
        $bd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        /* TODO: verifier si le client est en mode attente (variable post), si une partie a été trouvée pour lui, lui renvoyer les données de la partie */
        if (isset($_GET['waiting']))
        {
            $stmt = $bd->prepare('SELECT id, whiteid, blackid, pieces FROM games WHERE whiteid = :playerid OR blackid = :playerid;');
            $stmt->bindValue(':playerid', $_SESSION['account']->id);

            if ($stmt->execute())
            {
                if ($stmt->rowCount() != 0)
                {
                    $stmt->setFetchMode(PDO::FETCH_OBJ);
                    if ($result = $stmt->fetch())
                    {
                        $_SESSION['account']->gameid = $result->id;

                        $retour->success = true;
                        $retour->gameid = $result->id;
                        $retour->iswhite = $result->whiteid === $_SESSION['account']->id;
                        $retour->opponent = $result->iswhite ? $result->blackid : $result->whiteid;
                        $retour->pieces = json_decode($result->pieces);
                    }
                }
                else
                    $retour->waiting = true;
            }
        }
        else
        {
            $sql = isset($_GET['nick']) ? 'SELECT playerid FROM lobby WHERE nick = :nick AND playerid <> :playerid;' : 'SELECT playerid FROM lobby WHERE target IS NULL OR target = :nick AND playerid <> :playerid'; // essaie de trouver le joueur demandé, ou prend le premier adversaire dans la file d'attente
            $stmt = $bd->prepare($sql);

            $stmt->bindValue(':nick', isset($_GET['nick']) ? $_GET['nick'] : $_SESSION['account']->nick);
            $stmt->bindValue(':playerid', $_SESSION['account']->id);

            if ($stmt->execute() && $stmt->rowCount() != 0)
            {
                $stmt->setFetchMode(PDO::FETCH_OBJ);
                if ($result = $stmt->fetch())
                {
                    $stmt = $bd->query('SELECT max(id) AS gameid FROM games');
                    $stmt->setFetchMode(PDO::FETCH_OBJ);

                    if ($gameidresult = $stmt->fetch())
                    {
                        $gameid = 1 + $gameidresult->gameid;

                        $stmt = $bd->prepare('INSERT INTO games(id, whiteid, blackid, pieces) VALUES(:id, :whiteid, :blackid, :pieces);');
                        $stmt->bindValue(':id', $gameid);
                        $player_iswhite = (rand(0,10) < 5);
                        $stmt->bindValue(':whiteid', $player_iswhite ? $_SESSION['account']->id : $result->playerid);
                        $stmt->bindValue(':blackid', ($player_iswhite === false) ? $_SESSION['account']->id : $result->playerid);

                        $pieces = array(
                            array('x' => 0, 'y' => 0, 'type' => 'tour', 'white' => true), array('x' => 1, 'y' => 0, 'type' => 'cavalier', 'white' => true), array('x' => 2,'y' => 0, 'type' => 'fou', 'white' => true), array('x' => 3, 'y' => 0, 'type' => 'reine', 'white' => true), array('x' => 4, 'y' => 0, 'type' => 'roi', 'white' => true), array('x' => 5, 'y' => 0, 'type' => 'fou', 'white' => true), array('x' => 6, 'y' => 0, 'type' => 'cavalier', 'white' => true), array('x' => 7, 'y' => 0, 'type' => 'tour', 'white' => true),
                            array('x' => 0, 'y' => 1, 'type' => 'pion', 'white' => true), array('x' => 1, 'y' => 1, 'type' => 'pion', 'white' => true), array('x' => 2, 'y' => 1, 'type' => 'pion', 'white' => true), array('x' => 3, 'y' => 1, 'type' => 'pion', 'white' => true), array('x' => 4, 'y' => 1, 'type' => 'pion', 'white' => true), array('x' => 5, 'y' => 1, 'type' => 'pion', 'white' => true), array('x' => 6, 'y' => 1, 'type' => 'pion', 'white' => true), array('x' => 7, 'y' => 1, 'type' => 'pion', 'white' => true),

                            array('x' => 0, 'y' => 6, 'type' => 'pion', 'white' => false), array('x' => 1, 'y' => 6, 'type' => 'pion', 'white' => false), array('x' => 2, 'y' => 6, 'type' => 'pion', 'white' => false), array('x' => 3, 'y' => 6, 'type' => 'pion', 'white' => false), array('x' => 4, 'y' => 6, 'type' => 'pion', 'white' => false), array('x' => 5, 'y' => 6, 'type' => 'pion', 'white' => false), array('x' => 6, 'y' => 6, 'type' => 'pion', 'white' => false), array('x' => 7, 'y' => 6, 'type' => 'pion', 'white' => false),
                            array('x' => 0, 'y' => 7, 'type' => 'tour', 'white' => false), array('x' => 1, 'y' => 7, 'type' => 'cavalier', 'white' => false), array('x' => 2,'y' => 7, 'type' => 'fou', 'white' => false), array('x' => 3, 'y' => 7, 'type' => 'roi', 'white' => false), array('x' => 4, 'y' => 7, 'type' => 'reine', 'white' => false), array('x' => 5, 'y' => 7, 'type' => 'fou', 'white' => false), array('x' => 6, 'y' => 7, 'type' => 'cavalier', 'white' => false), array('x' => 7, 'y' => 7, 'type' => 'tour', 'white' => false)
                        );

                        $stmt->bindValue(':pieces', json_encode($pieces));

                        if ($stmt->execute())
                        {
                            unset($retour->message);

                            $retour->success = true;
                            $retour->gameid = $gameid;
                            $retour->iswhite = $player_iswhite;
                            $retour->opponent = $result->playerid;
                            $retour->pieces = $pieces;

                            $_SESSION['account']->gameid = $gameid;

                            $stmt = $bd->prepare('DELETE FROM lobby WHERE playerid = :playerid;');
                            $stmt->bindValue(':playerid', $result->playerid);
                            $stmt->execute();
                        }
                    }
                }
            } else {
                // aucun joueur n'a été trouvé
                // on se place alors dans la file d'attente

                $sql = isset($_GET['nick']) ? 'INSERT INTO lobby(playerid, nick, target) VALUES(:playerid, :mynick, :nick);' : 'INSERT INTO lobby(playerid, nick, target) VALUES(:playerid, :mynick, NULL);'; // mynick est le nom du client, nick est l'adversaire demandé par le client
                $stmt = $bd->prepare($sql);

                $stmt->bindValue(':playerid', $_SESSION['account']->id);
                $stmt->bindValue(':mynick', $_SESSION['account']->nick);

                if (isset($_GET['nick']))
                    $stmt->bindValue(':nick', $_GET['nick']);

                if ($stmt->execute())
                {
                    unset($retour->message);
                    $retour->waiting = true;
                }


            }
        }

    } catch (PDOException $ex)
    {
        //$retour->phperr = $ex;
        echo $ex;
    }
}
else
    $retour->message = "Vous n'êtes pas connecté";

header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');

echo json_encode($retour);