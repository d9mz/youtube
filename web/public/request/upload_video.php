<?php
session_start();

require("../../app/vendor/autoload.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/config.inc.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/db.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/queue.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/id.php");

$uID = new VideoIDGeneration();
$id = $uID->GenerateVideoID();

$configuration = (object) [
    "request" => (object) [
        "video_author"      => @$_SESSION['youtube'],
        "video_id"          => $id,
        "video_description" => $_POST['youtube-description'],
        "video_title"       => $_POST['youtube-title'],
        "video_category"    => $_POST['youtube-category'],
        "video_tags"        => $_POST['youtube-title'],
    ],

    "debug" => (object) [
        "stacktrace"        => "",
    ],

    "json" => (object) [
        "videoId" => $id,
    ]
];

$queue = new VideoQueue\QueueBase($__db, $configuration);

echo "<pre>" . print_r($configuration, true) . "</pre>";

try {
    //echo "<pre>" . print_r($_FILES, true) . "</pre>";
    /* Create new queue object */
    $queue = new ZMQSocket(new ZMQContext(), ZMQ::SOCKET_REQ, "MySock1");

    /* Connect to an endpoint */
    $queue->connect("tcp://quetue:2424");

    /* send and receive */
    echo json_encode($configuration->json) . "\0" . readfile($_FILES['youtube-video']['tmp_name']);
    var_dump($queue->send(json_encode($configuration->json) . "\0" . file_get_contents($_FILES['youtube-video']['tmp_name']))->recv());
} catch(Exception $e) {
    die($e);
}

/*
    UPLOAD VIDEO BACKEND -- ONLY EDIT IF YOU KNOW WHAT YOU'RE DOING
    $configuration - (object), holds configuration for ffmpeg & the queue
    $queue - (class), handles everything basically
    $__db - (object), PDO connection
*/

