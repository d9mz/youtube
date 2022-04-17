<?php
session_start();

require("../app/vendor/autoload.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/config.inc.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/db.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/select.php");

$router = new \Bramus\Router\Router();
$loader = new \Twig\Loader\FilesystemLoader('twig/templates');
$twig = new \Twig\Environment($loader);
$select = new \Database\Select($__db);

$router->get('/', function() use ($twig, $__db) { 
    echo $twig->render('index.twig', array());
});

$router->get('/watch', function() use ($twig, $__db) { 
    echo $twig->render('watch.twig', array());
});

$router->get('/sign_in', function() use ($twig, $__db) { 
    echo $twig->render('sign_in.twig', array());
});

$router->get('/sign_up', function() use ($twig, $__db) { 
    echo $twig->render('sign_up.twig', array());
});

$router->get('/upload_video', function() use ($twig, $__db) { 
    echo $twig->render('upload_video.twig', array());
});

$router->set404(function() use ($twig) {
    echo "404";
});

$twig->addGlobal('config',   $config);
$twig->addGlobal('session',  $_SESSION);
$twig->addGlobal('args',     @$_GET);
$twig->addGlobal('referrer', @$_SERVER['HTTP_REFERER']);
$router->run();

unset($_SESSION['alert']);