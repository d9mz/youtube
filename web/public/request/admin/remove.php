<?php
session_start();

require("../../../app/vendor/autoload.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/config.inc.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/db.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/select.php");

$select = new \Database\Select($__db);

$request = (object) [
    "remove_target"  => @$_POST['target_item'],
    "error"          => (object) [
        "message"    => "",
        "type"       => 0,
    ],
];

if(!isset($_SESSION['youtube'])) {
    $request->error->type    = 1;
    $request->error->message = "You are not logged in.";
}

if(!$select->user_is_admin($_SESSION['youtube'])) {
    $request->error->type    = 1;
    $request->error->message = "You are not an admin.";
}

if($request->error->message == "") {
    if(isset($_GET['type']) && $_GET['type'] == "videos") {
        $stmt = $__db->prepare("DELETE FROM videos WHERE video_author = ?");
        $stmt->execute([$request->remove_target]);
    } else if (isset($_GET['type']) && $_GET['type'] == "video") {
        $stmt = $__db->prepare("DELETE FROM videos WHERE video_id = ?");
        $stmt->execute([$request->remove_target]);
    } else {
        $request->error->type    = 1;
        $request->error->message = "Type GET variable is not set or is invalid";
    }
    
    header("Location: /admin/delete");
} else {
    $_SESSION['alert'] = (object) [
        "message" => $request->error->message,
        "type" => $request->error->type,
    ];
    header("Location: /admin/delete");
}