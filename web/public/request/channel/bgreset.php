<?php
session_start();

require("../../../app/vendor/autoload.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/config.inc.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/db.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/select.php");

$select = new \Database\Select($__db);
$user = $select->fetch_table_singlerow($_SESSION['youtube'], "users", "youtube_username");
$user["youtube_colors"] = json_decode($user["youtube_colors"]);

$request = (object) [
    "user_bg"     => "",
    "user_target"    => $_SESSION['youtube'],
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
if(!isset($_SESSION['youtube'])) {
    $request->error->type    = 1;
    $request->error->message = "You are not logged in!";
}

if($request->error->message == "") {
    $user["youtube_colors"]->background_image = $request->user_bg;
    $user["youtube_colors"] = json_encode($user["youtube_colors"]);
    $stmt = $__db->prepare("UPDATE users SET youtube_colors = ? WHERE youtube_username = ?");
    $stmt->execute([
        $user["youtube_colors"],
        $request->user_target,
    ]);

    header("Location: /user/" . $_SESSION['youtube']);
} else {
    $_SESSION['alert'] = (object) [
        "message" => $request->error->message,
        "type" => $request->error->type,
    ];

    echo false;
}