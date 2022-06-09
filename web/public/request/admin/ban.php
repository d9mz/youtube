<?php
session_start();

require("../../../app/vendor/autoload.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/config.inc.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/db.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/select.php");

$select = new \Database\Select($__db);

$request = (object) [
    "ban_username"   => @$_POST['ban_username'],
    "ban_reason"     => @$_POST['ban_username'],
    "ban_expire"     => @$_POST['ban_expire'],
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
    $stmt = $__db->prepare("DELETE FROM comment WHERE comment_author = ?");
    $stmt->execute([$request->ban_username]);
    $stmt = $__db->prepare("DELETE FROM comment_votes WHERE vote_from = ?");
    $stmt->execute([$request->ban_username]);
    $stmt = $__db->prepare("DELETE FROM history WHERE video_author = ?");
    $stmt->execute([$request->ban_username]);
    $stmt = $__db->prepare("DELETE FROM inbox WHERE inbox_author = ?");
    $stmt->execute([$request->ban_username]);
    $stmt = $__db->prepare("DELETE FROM referrals WHERE key_from = ?");
    $stmt->execute([$request->ban_username]);
    $stmt = $__db->prepare("DELETE FROM subscribers WHERE subscription_to = ?");
    $stmt->execute([$request->ban_username]);
    $stmt = $__db->prepare("DELETE FROM subscribers WHERE subscription_from = ?");
    $stmt->execute([$request->ban_username]);
    $stmt = $__db->prepare("DELETE FROM users WHERE youtube_username = ?");
    $stmt->execute([$request->ban_username]);
    $stmt = $__db->prepare("DELETE FROM videos WHERE video_author = ?");
    $stmt->execute([$request->ban_username]);
    $stmt = $__db->prepare("DELETE FROM votes WHERE vote_from = ?");
    $stmt->execute([$request->ban_username]);
    
    header("Location: /admin/ban");
} else {
    $_SESSION['alert'] = (object) [
        "message" => $request->error->message,
        "type" => $request->error->type,
    ];
    header("Location: /admin/ban");
}