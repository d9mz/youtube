<?php
session_start();

require("../../app/vendor/autoload.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/config.inc.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/db.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/queue.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/id.php");

$uID = new VideoIDGeneration();

$configuration = (object) [
    "ffmpeg" => (object) [
        "ffmpeg_bin"        => "ffmpeg",
        "ffmpeg_threads"    => 1,
        "ffmpeg_filetype"   => ".mp4",
        "ffmpeg_output_res" => "1280x720",
        "ffmpeg_debug"      => true,
    ],

    "request" => (object) [
        "video_author"      => @$_SESSION['youtube'],
        "video_id"          => $uID->GenerateVideoID(),
        "video_description" => $_POST['youtube-description'],
        "video_title"       => $_POST['youtube-title'],
        "video_category"    => $_POST['youtube-category'],
        "video_tags"        => $_POST['youtube-title'],
    ],

    "debug" => (object) [
        "stacktrace"        => "",
    ],
];

$queue = new VideoQueue\QueueBase($__db, $configuration);

try {
    echo "<pre>" . print_r($configuration, true) . "</pre>";
} catch (Exception $e) {
    echo $configuration->debug->stacktrace = $e;
}

/*
    UPLOAD VIDEO BACKEND -- ONLY EDIT IF YOU KNOW WHAT YOU'RE DOING
    $configuration - (object), holds configuration for ffmpeg & the queue
    $queue - (class), handles everything basically
    $__db - (object), PDO connection
*/

