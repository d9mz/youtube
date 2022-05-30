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
    "user_color"     => $_POST['greytextcolor'],
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

$request->user_color = str_replace("#", "", $request->user_color);
if(!ctype_xdigit($request->user_color) && strlen($request->user_color) != 6){
    $request->error->type    = 1;
    $request->error->message = "This color is not valid.";
}

$request->user_color = "#" . $request->user_color;

print_r($request);

if($request->error->message == "") {
    $user["youtube_colors"]->grey_text_color = $request->user_color;
    $user["youtube_colors"] = json_encode($user["youtube_colors"]);
    $stmt = $__db->prepare("UPDATE users SET youtube_colors = ? WHERE youtube_username = ?");
    $stmt->execute([
        $user["youtube_colors"],
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