<?php
session_start();

require("../../../app/vendor/autoload.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/config.inc.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/db.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/select.php");

$select = new \Database\Select($__db);

$request = (object) [
    "ban_username"   => @$_POST['ban_username'],
    "ban_reason"     => @$_POST['ban_reason'],
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

if(!isset($_POST['ban_expire'])) {
    $request->error->type    = 1;
    $request->error->message = "You have not set the expiration date.";
}

if(!$select->user_is_admin($_SESSION['youtube'])) {
    $request->error->type    = 1;
    $request->error->message = "You are not an admin.";
}

if($request->error->message == "") {
    if(isset($_POST['remove_items']) && $_POST['remove_items'] == "y") {
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
        $stmt = $__db->prepare("DELETE FROM videos WHERE video_author = ?");
        $stmt->execute([$request->ban_username]);
        $stmt = $__db->prepare("DELETE FROM votes WHERE vote_from = ?");
        $stmt->execute([$request->ban_username]);
    }
    $stmt = $__db->prepare(
        "INSERT INTO bans 
            (ban_reason, ban_to, ban_by, ban_expire) 
         VALUES 
            (?, ?, ?, ?)"
    );
    $stmt->execute([
        $request->ban_reason,
        $request->ban_username,
        $_SESSION['youtube'],
        $request->ban_expire,
    ]);
    $stmt = null;
        
    header("Location: /admin/ban");
} else {
    $_SESSION['alert'] = (object) [
        "message" => $request->error->message,
        "type" => $request->error->type,
    ];
    header("Location: /admin/ban");
}