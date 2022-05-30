<?php
session_start();
require($_SERVER['DOCUMENT_ROOT'] . "/protected/config.inc.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/db.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/select.php");
error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
$select = new \Database\Select($__db);
$video = $select->fetch_table_singlerow($_GET['v'], "videos", "video_id");

// Make a class for this. Please
$request = (object) [
    "report_from" => @$_SESSION['youtube'],
    "report_content" => @$_GET['v'],
    "message"    => "",
];

if(!isset($_SESSION['youtube'])) { 
    $request->message = "You are not logged in.";
    die(json_encode($request)); 
}

$webhookurl = "https://discord.com/api/webhooks/974521922257838080/Jp3ZJUywbZZf3uon0fCxsuHcjJf3v6umu1p22w7Vs1MMB_WqM20vvB-vwYKPOJzwbsQx";
$timestamp = date("c", strtotime("now"));

$json_data = json_encode([
    "content" => "```md
# Report (Video)
- Reporter: 
    - Username: " . $_SESSION['youtube'] . "
    - Title: " . $video['video_title'] . "
    - Author: " . $video['video_author'] . "
    - Video ID: https://utue.net/watch?v=" . $video['video_id'] . "```",
    "username" => $_SESSION['youtube'],
    "tts" => false,
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

$ch = curl_init( $webhookurl );
curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
curl_setopt( $ch, CURLOPT_POST, 1);
curl_setopt( $ch, CURLOPT_POSTFIELDS, $json_data);
curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt( $ch, CURLOPT_HEADER, 0);
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

$response = curl_exec( $ch );
curl_close( $ch );

$request->message = "Successfully reported video!\nThis video will be monitored by our administrators.";
die($request->message);