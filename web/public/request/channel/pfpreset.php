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
    "user_pfp"     => "default.png",
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
    $stmt = $__db->prepare("UPDATE users SET youtube_picture = ? WHERE youtube_username = ?");
    $stmt->execute([
        $request->user_pfp,
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