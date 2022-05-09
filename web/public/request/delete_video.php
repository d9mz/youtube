<?php
session_start();

require("../../app/vendor/autoload.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/config.inc.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/db.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/select.php");

$select = new \Database\Select($__db);

$request = (object) [
    "video_author"   => @$_SESSION['youtube'],
    "video_target" => @$_GET['v'],
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

$video = $select->fetch_table_singlerow($_GET['v'], "videos", "video_id");
if(!isset($_GET['v'])) {
    $request->error->type    = 1;
    $request->error->message = "Video Parameter Not Set";
}

if(!isset($_SESSION['youtube'])) {
    $request->error->type    = 1;
    $request->error->message = "You are not logged in.";
}

if(!$select->video_exists($_GET['v'])) {
    $request->error->type    = 1;
    $request->error->message = "This video does not exist.";
}

if($video['video_author'] != $_SESSION['youtube']) {
    $request->error->type    = 1;
    $request->error->message = "You do not own this video.";
}

if($request->error->message == "") {
    $stmt = $__db->prepare("DELETE FROM videos WHERE video_id = ? AND video_author = ?");
    $stmt->execute(
        [
            $request->video_target,
            $request->video_author,
        ]
    );
    
    header("Location: /my/video_manager");
} else {
    $_SESSION['alert'] = (object) [
        "message" => $request->error->message,
        "type" => $request->error->type,
    ];
    header("Location: /my/video_manager");
}