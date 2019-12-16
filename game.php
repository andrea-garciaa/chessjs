<?php
/**
 * Created by PhpStorm.
 * User: Andrea
 * Date: 31/03/2019
 * Time: 19:50
 */

session_start();
$retour = new stdClass();
$retour->success = false;

if (isset($_SESSION['account']) && isset($_SESSION['account']->gameid))
{
    try
    {
        $bd = new PDO('mysql:host=<dbhostname>;dbname=<dbname>', '<dblogin>', '<dbpass>');
        $bd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $bd->prepare('SELECT tour, pieces, whiteid, blackid, wonid FROM games WHERE id = :gameid');
        $stmt->bindValue(':gameid', $_SESSION['account']->gameid);

        if ($stmt->execute() && $stmt->rowCount() != 0)
        {
            $stmt->setFetchMode(PDO::FETCH_OBJ);
            if ($result = $stmt->fetch())
            {
                $opponent = $_SESSION['account']->id == $result->whiteid ? $result->blackid : $result->whiteid;
                $pieces = json_decode($result->pieces, true);
                $tour = $result->tour;

                if (($result->tour == 'white' && $_SESSION['account']->id == $result->blackid) || ($result->tour == 'black' && $_SESSION['account']->id == $result->whiteid))
                {
                    $retour->illegalmove = 'Ce n\'est pas encore votre tour de jeu.';
                    $retour->success = true;
                }
                else if ($result->wonid)
                {
                    $retour->success = true;
                    if ($result->wonid == $_SESSION['account']->id)
                    {
                        // c'est moi le gagnant
                        $retour->won = true;
                    }
                    else
                        $retour->lose = true;
                }
                else if ($_GET['move'] && $_GET['fx'] && $_GET['fy'] && $_GET['tx'] && $_GET['ty'])
                {
                    $foundf = -1;
                    $foundt = -1;

                    $taille = count($pieces);

                    for ($i = 0; $i < $taille; ++$i)
                    {
                        if ($foundf == -1 && $pieces[$i]['x'] == $_GET['fx'] && $pieces[$i]['y'] == $_GET['fy'])
                            $foundf = $i;
                        if ($foundt == -1 && $pieces[$i]['x'] == $_GET['tx'] && $pieces[$i]['y'] == $_GET['ty'])
                            $foundt = $i;

                        if ($foundf != -1 && $foundt != -1)
                            break;
                    }

                    if ($foundf != -1)
                    {
                        $ok = true; // elle va être modifiée dans les if mais utilisée en bas pour savoir si on doit mettre à jour les pièces dans la bd

                        // on vérifie que le joueur noir n'essaie pas de déplacer des pièces blanches et inversement

                        if ($pieces[$foundf]['white'] != ($_SESSION['account']->id == $result->whiteid))
                        {
                            $ok = false;
                            $retour->illegalmove = 'Touchez à vos pièces !';
                        }
                        else if ($foundt != -1 && $foundf != $foundt)
                        {
                            if ($pieces[$foundt]['white'] == $pieces[$foundf]['white'])
                            {
                                // on ne peut pas manger ses propres pièces
                                $retour->illegalmove = 'Vous ne pouvez pas manger vos propres pièces !';
                                $ok = false;
                            }

                            // c'est une élimination
                            else if ($pieces[$foundt]['type'] == 'roi')
                            {
                                // fin de la partie (victoire)

                                $stmt = $bd->prepare('UPDATE games SET wonid = :wonid WHERE id = :gameid');
                                $stmt->bindValue(':wonid', $_SESSION['account']->id);
                                $stmt->bindValue(':gameid', $_SESSION['account']->gameid);
                                $stmt->execute();

                                $stmt = $bd->prepare('SELECT wins FROM accounts WHERE id = :playerid');
                                $stmt->bindValue(':playerid', $_SESSION['account']->id);

                                if ($stmt->execute() && $stmt->rowCount() != 0)
                                {
                                    $stmt->setFetchMode(PDO::FETCH_OBJ);
                                    if ($result = $stmt->fetch())
                                    {
                                        $wins = $result->wins + 1;
                                        $stmt = $bd->prepare('UPDATE accounts SET wins = :wins WHERE id = :playerid');
                                        $stmt->bindValue(':wins', $wins);
                                        $stmt->bindValue(':playerid', $_SESSION['account']->id);

                                        if ($stmt->execute())
                                        {
                                            // fin de la partie (perte)
                                            $stmt = $bd->prepare('SELECT losses FROM accounts WHERE id = :playerid');
                                            $stmt->bindValue(':playerid', $_SESSION['account']->id);

                                            if ($stmt->execute() && $stmt->rowCount() != 0)
                                            {
                                                $stmt->setFetchMode(PDO::FETCH_OBJ);
                                                if ($result = $stmt->fetch())
                                                {
                                                    $losses = $result->losses + 1;
                                                    $stmt = $bd->prepare('UPDATE accounts SET losses = :losses WHERE id = :playerid');
                                                    $stmt->bindValue(':losses', $losses);
                                                    $stmt->bindValue(':playerid', $opponent);
                                                    $stmt->execute();
                                                }
                                            }

                                            $retour->won = true;
                                        }
                                    }
                                }
                            }

                            if ($ok)
                                unset($pieces[$foundt]);
                        }

                        if ($foundf != $foundt)
                        {
                            if ($ok)
                            {
                                $pieces[$foundf]['x'] = $_GET['tx'];
                                $pieces[$foundf]['y'] = $_GET['ty'];

                                $stmt = $bd->prepare('UPDATE games SET pieces = :pieces, tour = :tour WHERE id = :gameid');
                                $stmt->bindValue(':pieces', json_encode($pieces));
                                $stmt->bindValue(':gameid', $_SESSION['account']->gameid);
                                $stmt->bindValue(':tour', $tour == 'white' ? 'black' : 'white');

                                if (!$stmt->execute())
                                    $retour->success = false;
                                else
                                    $retour->pieces = $pieces;
                            }
                        }
                        else
                            $retour->illegalmove = 'Vous ne pouvez pas vous déplacer sur la même case !';

                        $retour->success = true;
                    }
                }
                else if ($_GET['update'])
                {
                    $retour->success = true;
                    $retour->pieces =  $pieces;
                    $retour->tour = $tour;
                }
            }
        }
    }
    catch (PDOException $ex)
    {
        echo $ex;
    }
}


header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');


echo json_encode($retour);