<?php
session_start();

require("../../app/vendor/autoload.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/config.inc.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/db.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/select.php");

$select = new \Database\Select($__db);

$request = (object) [
    "comment_text"   => @$_POST['comment'],
    "comment_author" => @$_SESSION['youtube'],
    "comment_target" => @$_GET['v'],
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

if(strlen(trim($_POST['comment'])) > 500) {
    $request->error->type    = 1;
    $request->error->message = "Your comment is too long.";
}

if(!isset($_POST['comment']) || empty(trim($_POST['comment']))) {
    $request->error->type    = 1;
    $request->error->message = "Your comment is empty.";
}

if(!$select->video_exists($_GET['v'])) {
    $request->error->type    = 1;
    $request->error->message = "This video does not exist.";
}

if($request->error->message == "") {
    $stmt = $__db->prepare(
        "INSERT INTO comment 
            (comment_target, comment_text, comment_author) 
         VALUES 
            (?, ?, ?)"
    );
    $stmt->execute([
        $request->comment_target,
        $request->comment_text,
        $_SESSION['youtube'],
    ]);
    $stmt = null;

    // echo "<pre>" . print_r($request, true) . "</pre>";
    header("Location: /watch?v=" . $request->comment_target);
} else {
    $_SESSION['alert'] = (object) [
        "message" => $request->error->message,
        "type" => $request->error->type,
    ];
    header("Location: /watch?v=" . $request->comment_target);
}