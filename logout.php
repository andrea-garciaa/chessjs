<?php
/**
 * Created by PhpStorm.
 * User: g17006433
 * Date: 06/03/19
 * Time: 15:06
 */

session_start();
$_SESSION = array();
session_destroy();

header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');