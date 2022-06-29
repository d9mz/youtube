<?php
session_start();

require("../../../app/vendor/autoload.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/config.inc.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/db.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/select.php");

$select = new \Database\Select($__db);
$user = $select->fetch_table_singlerow($_SESSION['youtube'], "users", "youtube_username");

$request = (object) [
    "user_target"    => $_SESSION['youtube'],
    "error"          => (object) [
        "message"    => "",
        "type"       => 0,
    ],
];
/* Create a form handler class... This is extremely ugly */
if(!isset($_SESSION['youtube'])) {
    $request->error->type    = 1;
    $request->error->message = "You are not logged in!";
}

if($request->error->message == "") {
    $stmt = $__db->prepare("UPDATE users SET youtube_sub_button = '' WHERE youtube_username = ?");
    $stmt->execute([
        $request->user_target,
    ]);

    header("Location: /account");
} else {
    $_SESSION['alert'] = (object) [
        "message" => $request->error->message,
        "type" => $request->error->type,
    ];

    echo false;
}