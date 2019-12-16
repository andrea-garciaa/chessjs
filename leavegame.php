<?php
/**
 * Created by PhpStorm.
 * User: Andrea
 * Date: 30/03/2019
 * Time: 18:28
 */

session_start();

if (isset($_SESSION['account']) && isset($_SESSION['account']->gameid))
{
    $bd = new PDO('mysql:host=<dbhostname>;dbname=<dbname>', '<dblogin>', '<dbpass>');
    $sql = 'DELETE FROM games WHERE id = :gameid;';
    $stmt = $bd->prepare($sql);
    $stmt->bindValue(':gameid', $_SESSION['account']->gameid);
    $stmt->execute();
    unset($_SESSION['account']->gameid);
}

header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');