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

$video = $select->fetch_table_singlerow($_GET['v'], "videos", "video_id");
$user = $select->fetch_table_singlerow($_SESSION['youtube'], "users", "youtube_username");

$request = (object) [
    "comment_text"   => @$_POST['comment'],
    "comment_author" => @$_SESSION['youtube'],
    "comment_target" => @$_GET['v'],
    "error"          => (object) [
        "message"    => "",
        "type"       => 0,
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

if($select->comment_cooldown($_SESSION['youtube'])) {
    $request->error->type    = 1;
    $request->error->message = "You are under a cooldown!";
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

	$request->notification_body = "I commented on your video \"" . $video['video_title'] . "\"!\n\"" . $request->comment_text . "\"";
    $insert->send_notification( 
		$video['video_author'], 
		$_SESSION['youtube'], 
		"New Video Comment",
		$request->notification_body,
        "",
        "Notification"
	);

    $update->update_cooldown($_SESSION['youtube'], "last_comment");
} else {
    echo $request->error->message;
}