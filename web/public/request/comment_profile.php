<?php
session_start();

require("../../app/vendor/autoload.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/config.inc.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/db.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/select.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/insert.php");

$insert = new \Database\Insert($__db);
$select = new \Database\Select($__db);

$request = (object) [
    "comment_text"   => @$_POST['comment'],
    "comment_author" => @$_SESSION['youtube'],
    "comment_target" => @$_GET['u'],
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

if(!isset($_GET['u'])) {
    $request->error->type    = 1;
    $request->error->message = "User Parameter Not Set";
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

if(!$select->user_exists($_GET['u'])) {
    $request->error->type    = 1;
    $request->error->message = "This user does not exist.";
}

if($request->error->message == "") {
    $stmt = $__db->prepare(
        "INSERT INTO comment 
            (comment_target, comment_text, comment_author, comment_type) 
         VALUES 
            (?, ?, ?, 'u')"
    );
    $stmt->execute([
        $request->comment_target,
        $request->comment_text,
        $_SESSION['youtube'],
    ]);
    $stmt = null;
	
	$request->notification_body = "I commented on your profile!\n\"" . $request->comment_text . "\"";
    $insert->send_notification( 
		$request->comment_target, 
		$_SESSION['youtube'], 
		"New Profile Comment",
		$request->notification_body,
	);
    header("Location: /user/" . $request->comment_target);
} else {
    $_SESSION['alert'] = (object) [
        "message" => $request->error->message,
        "type" => $request->error->type,
    ];
    header("Location: /user/" . $request->comment_target);
}