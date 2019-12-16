<?php
/**
 * Created by PhpStorm.
 * User: g17006433
 * Date: 06/03/19
 * Time: 16:22
 */

session_start();

header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');

echo json_encode(isset($_SESSION['account']) ? $_SESSION['account'] : false);