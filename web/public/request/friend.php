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
    "friend_target"  => @$_GET['u'],
    "friend_from"    => @$_SESSION['youtube'],
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
    $stmt = $__db->prepare("SELECT friend_by, friend_to FROM friends WHERE friend_to = :username AND friend_by = :by");
    $stmt->bindParam(":username", $_GET['u']);
    $stmt->bindParam(":by", $_SESSION['youtube']);
    $stmt->execute();
    if($stmt->rowCount() === 1) {
        $stmt = $__db->prepare("DELETE FROM friends WHERE friend_by = ? AND friend_to = ?");
        $stmt->execute(
            [
                $request->friend_from,
                $request->friend_target,
            ]
        );
        header("Location: /user/" . $_GET['u']);
        die();
    }

    $stmt = $__db->prepare(
        "INSERT INTO friends 
            (friend_to, friend_by) 
         VALUES 
            (?, ?)"
    );
    $stmt->execute([
        $request->friend_target,
        $request->friend_from,
    ]);
    $stmt = null;
    header("Location: /user/" . $_GET['u']);
} else {
    $_SESSION['alert'] = (object) [
        "message" => $request->error->message,
        "type" => $request->error->type,
    ];
    header("Location: /user/" . $_GET['u']);
}