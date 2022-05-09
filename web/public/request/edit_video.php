<?php
session_start();

require("../../app/vendor/autoload.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/config.inc.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/db.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/select.php");

$select = new \Database\Select($__db);

$request = (object) [
    "video_target"      => @$_GET['v'],
    "video_description" => @$_POST['youtube_description'],
    "video_title"       => @$_POST['youtube_title'],
    "video_author"      => @$_SESSION['youtube'],
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

if(!isset($_GET['v'])) {
    $request->error->type    = 1;
    $request->error->message = "Video Parameter Not Set";
}

if(!isset($_SESSION['youtube'])) {
    $request->error->type    = 1;
    $request->error->message = "You are not logged in!";
}

if(strlen(trim($_POST['youtube_description'])) > 500) {
    $request->error->type    = 1;
    $request->error->message = "Your comment is too long.";
}

if(strlen(trim($_POST['youtube_title'])) > 50) {
    $request->error->type    = 1;
    $request->error->message = "Your title is too long.";
}

if(!isset($_POST['youtube_description']) || empty(trim($_POST['youtube_title']))) {
    $request->error->type    = 1;
    $request->error->message = "Your comment or title is empty.";
}

if(!$select->video_exists($_GET['v'])) {
    $request->error->type    = 1;
    $request->error->message = "This video does not exist.";
}

if($request->error->message == "") {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // print_r($request);

        $stmt = $__db->prepare("UPDATE videos SET video_title = ?, video_description = ? WHERE video_id = ?");
        $stmt->execute([
            $request->video_title,
            $request->video_description,
            $request->video_target,
        ]);
    } else if($_SERVER['REQUEST_METHOD'] === 'GET') {

    }

    header("Location: /my/edit_video?v=" . $request->video_target);
} else {
    $_SESSION['alert'] = (object) [
        "message" => $request->error->message,
        "type" => $request->error->type,
    ];
    header("Location: /my/edit_video?v=" . $request->video_target);
}