<?php
session_start();

require("../../app/vendor/autoload.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/config.inc.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/db.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/select.php");

$select = new \Database\Select($__db);

$request = (object) [
    "friend_from"    => @$_SESSION['youtube'],
    "friend_to"      => @$_GET['u'],
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

$friend = $select->get_friend_request($_SESSION['youtube'], $_GET['u'], "u");
if(!isset($friend['id'])) {
    $friend = $select->get_friend_request($_SESSION['youtube'], $_GET['u'], "a");
}
if(!isset($_GET['u'])) {
    $request->error->type    = 1;
    $request->error->message = "User Parameter Not Set";
}

if(!isset($_SESSION['youtube'])) {
    $request->error->type    = 1;
    $request->error->message = "You are not logged in.";
}

if(!$select->user_exists($_GET['u'])) {
    $request->error->type    = 1;
    $request->error->message = "This user does not exist.";
}

if($_SESSION['youtube'] != $friend['friend_to']) {
    $request->error->type    = 1;
    $request->error->message = "You do not own this friend request.";
}

if($request->error->message == "") {
    $stmt = $__db->prepare("UPDATE friends SET friend_status = 'd' WHERE friend_to = ? AND friend_by = ?");
    $stmt->execute(
        [
            $request->friend_from,
            $request->friend_to,
        ]
    );
    
    header("Location: /my/friends");
} else {
    $_SESSION['alert'] = (object) [
        "message" => $request->error->message,
        "type" => $request->error->type,
    ];
    header("Location: /my/friends");
}