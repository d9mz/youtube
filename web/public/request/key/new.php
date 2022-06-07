<?php
session_start();

require("../../../app/vendor/autoload.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/config.inc.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/db.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/select.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/insert.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/id.php");

$insert = new \Database\Insert($__db);
$select = new \Database\Select($__db);
$id = new \VideoIDGeneration();

$request = (object) [
    "key_code"       => $id->GenerateRefID(),
    "key_from"       => @$_SESSION['youtube'],
    "error"          => (object) [
        "message"    => "",
        "type"       => 0,
    ],
];

if(!isset($_SESSION['youtube'])) {
    $request->error->type    = 1;
    $request->error->message = "You are not logged in!";
}

if($request->error->message == "") {
    $stmt = $__db->prepare(
        "INSERT INTO referrals 
            (key_from, key_code) 
         VALUES 
            (?, ?)"
    );
    $stmt->execute([
        $request->key_from,
        $request->key_code,
    ]);
    $stmt = null;
    header("Location: /my/keys");
} else {
    $_SESSION['alert'] = (object) [
        "message" => $request->error->message,
        "type" => $request->error->type,
    ];
    header("Location: /my/keys");
}