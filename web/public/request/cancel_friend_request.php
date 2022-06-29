<?php
session_start();

require("../../app/vendor/autoload.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/config.inc.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/db.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/select.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/insert.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/update.php");

$update = new \Database\Update($__db);
$insert = new \Database\Insert($__db);
$select = new \Database\Select($__db);

$request = (object) [
    "block_target"   => @$_GET['u'],
    "block_from"     => @$_SESSION['youtube'],
    "error"          => (object) [
        "message"    => "",
        "type"       => 0,
    ],
];

/* Create a form handler class... This is extremely ugly */

if(!isset($_GET['u'])) {
    $request->error->type    = 1;
    $request->error->message = "User Parameter Not Set";
}

if(!isset($_SESSION['youtube'])) {
    $request->error->type    = 1;
    $request->error->message = "You are not logged in!";
}

if(!$select->user_exists($_GET['u'])) {
    $request->error->type    = 1;
    $request->error->message = "This user does not exist.";
}

if($request->error->message == "") {
    $stmt = $__db->prepare("SELECT block_from, block_to FROM block WHERE block_to = :username");
    $stmt->bindParam(":username", $_GET['u']);
    $stmt->execute();
    if($stmt->rowCount() === 1) {
        $stmt = $__db->prepare("DELETE FROM block WHERE block_from = ? AND block_to = ?");
        $stmt->execute(
            [
                $request->block_from,
                $request->block_target,
            ]
        );
        header("Location: /user/" . $_GET['u']);
    } else {
        $stmt = $__db->prepare(
            "INSERT INTO block 
                (block_to, block_from) 
             VALUES 
                (?, ?)"
        );
        $stmt->execute([
            $request->block_target,
            $request->block_from,
        ]);
        $stmt = null;
        header("Location: /user/" . $_GET['u']);
    }
} else {
    $_SESSION['alert'] = (object) [
        "message" => $request->error->message,
        "type" => $request->error->type,
    ];
    header("Location: /user/" . $_GET['u']);
}