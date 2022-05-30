<?php
session_start();

require("../../../app/vendor/autoload.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/config.inc.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/db.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/select.php");

$select = new \Database\Select($__db);
$video = $select->fetch_table_singlerow($_SESSION['youtube'], "users", "youtube_username");

$request = (object) [
    "user_tag"       => @$_POST['tags'],
    "user_target"    => @$_SESSION['youtube'],
    "error"          => (object) [
        "message"    => "",
        "type"       => 0,
        /*
            0 - success
            1 - error
            2 - warning
        */
    ],
];

/* Create a form handler class... This is extremely ugly */

if(!isset($_POST['tags'])) {
    $request->error->type    = 1;
    $request->error->message = "Tags parameter is empty";
}

if(!isset($_SESSION['youtube'])) {
    $request->error->type    = 1;
    $request->error->message = "You are not logged in!";
}

if($request->error->message == "") {
    $stmt = $__db->prepare("UPDATE users SET youtube_tag = ? WHERE youtube_username = ?");
    $stmt->execute([
        $request->user_tag,
        $request->user_target,
    ]);

    echo true;
} else {
    $_SESSION['alert'] = (object) [
        "message" => $request->error->message,
        "type" => $request->error->type,
    ];

    echo false;
}