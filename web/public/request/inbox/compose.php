<?php
session_start();

require("../../../app/vendor/autoload.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/config.inc.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/db.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/select.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/insert.php");

$select = new \Database\Select($__db);
$insert = new \Database\Insert($__db);

$request = (object) [
    "pm_text"   => @$_POST['pm_message'],
	"pm_subject" => @$_POST['pm_subject'],
    "pm_author" => @$_SESSION['youtube'],
    "pm_to" => @$_POST['pm_to'],
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

if(!isset($_POST['pm_to'])) {
    $request->error->type    = 1;
    $request->error->message = "PM To Parameter Not Set";
}

if(!isset($_SESSION['youtube'])) {
    $request->error->type    = 1;
    $request->error->message = "You are not logged in!";
}

if(strlen(trim($_POST['pm_message'])) > 500) {
    $request->error->type    = 1;
    $request->error->message = "Your message is too long.";
}

if(!isset($_POST['pm_message']) || empty(trim($_POST['pm_message']))) {
    $request->error->type    = 1;
    $request->error->message = "Your message is empty.";
}

if(!$select->user_exists($request->pm_to)) {
	$request->error->type    = 1;
    $request->error->message = "This user you are trying to send your message to does not exist.";
}

if($request->error->message == "") {
    $insert->send_notification($request->pm_to, $request->pm_author, $request->pm_subject, $request->pm_text);
    header("Location: /inbox/");
} else {
    $_SESSION['alert'] = (object) [
        "message" => $request->error->message,
        "type" => $request->error->type,
    ];
    header("Location: /inbox/compose");
}