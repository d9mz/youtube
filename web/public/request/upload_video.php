<?php
session_start();

require("../../app/vendor/autoload.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/config.inc.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/db.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/queue.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/id.php");

if(!isset($_SESSION['youtube'])) {
    header("Location: /sign_in");
}

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
        "video_meta"        => (object) [
            "video"                 => $_FILES['youtube-video'],
            "video_processing_logs" => "",
            "video_file_type"       => "." . strtolower(pathinfo($_FILES["youtube-video"]["name"], PATHINFO_EXTENSION)),
            "video_tmp_name"        => md5_file($_FILES["youtube-video"]["tmp_name"]) . ".mp4",
            "video_duration"        => 0,
            "video_thumbnail"       => "default.jpg",
            "video_upload_ok"       => false,
        ],
    ],

    "hardcoded" => (object) [
        "categories" => [],
    ],

    "debug" => (object) [
        "stacktrace" => "",
    ],

    "json" => (object) [
        "videoId"    => $id,
    ]
];

$configuration->hardcoded->categories = [
    "None", 
    "Film & Animation", 
    "Autos & Vehicles", 
    "Music", 
    "Pets & Animals", 
    "Sports", 
    "Travel & Events", 
    "Gaming", 
    "People & Blogs", 
    "Comedy", 
    "Entertainment", 
    "News & Politics", 
    "Howto & Style", 
    "Education", 
    "Science & Technology", 
    "Nonprofits & Activism"
];

$configuration->hardcoded->allowed_vtype = [
    ".mp4",
    ".mov",
    ".wmv",
    ".avi",
    ".webm",
];

$queue = new VideoQueue\QueueBase($__db, $configuration);

try {
    $ffmpeg = FFMpeg\FFMpeg::create();
    $ffprobe = FFMpeg\FFProbe::create();

    if($_FILES['youtube-video']['error'] != 0) {
        $configuration->debug->stacktrace = "PHP FILE UPLOAD ERROR";
        $queue->set($configuration);
    }

    if(strlen(trim($configuration->request->video_description)) > 2000) {
        $configuration->debug->stacktrace = "Your video description is too long,";
        $queue->set($configuration);
    }

    if(strlen(trim($configuration->request->video_title)) > 30) {
        $configuration->debug->stacktrace = "Your video title is too long,";
        $queue->set($configuration);
    }

    if(!in_array($configuration->request->video_category, $configuration->hardcoded->categories)) {
        $configuration->debug->stacktrace = "Your category is invalid.";
        $queue->set($configuration);
    }

    if(!in_array($configuration->request->video_meta->video_file_type, $configuration->hardcoded->allowed_vtype)) {
        $configuration->debug->stacktrace = "Your video format is not allowed.";
        $queue->set($configuration);
    }

    if(!$ffprobe->isValid($configuration->request->video_meta->video['tmp_name'])) {
        $configuration->debug->stacktrace = "Your video is not valid. Is it corrupted?";
        $queue->set($configuration);
    }

    if($configuration->debug->stacktrace == "") {
        if(move_uploaded_file(
            $_FILES['youtube-video']['tmp_name'], 
            "../v/t/" . $configuration->request->video_id . $configuration->request->video_meta->video_file_type
        )) {
            $configuration->request->video_meta->full_path = "../v/t/" . $configuration->request->video_id . $configuration->request->video_meta->video_file_type;
            $configuration->request->video_meta->video_upload_ok = true;
            $queue->set($configuration);
        } else {
            trigger_error("File upload error - pottential IO / Permission issue?", E_USER_ERROR);
        }

        $video = $ffmpeg->open($configuration->request->video_meta->full_path);
        $configuration->request->video_meta->video_duration = round($ffprobe->format("../v/t/" . $configuration->request->video_id . ".mp4")->get('duration'));

        mkdir("../v/thumb/" . $configuration->request->video_id . "/");
        $interval = floor( $configuration->request->video_meta->video_duration / 3 );
        for($i = 0; $i < 3; $i++){
            $frame = $video->frame(FFMpeg\Coordinate\TimeCode::fromSeconds( $i * $interval ));
            $frame->save( '../v/thumb/' . $configuration->request->video_id . "/" . $i . '.jpg');
        }

        $video
            ->filters()
            ->resize(new FFMpeg\Coordinate\Dimension(640, 480))
            ->synchronize();

        $video
            ->filters()
            ->watermark("../v/thumb/watermark.png", array(
                'position' => 'relative',
                'bottom' => 5,
                'right' => 5,
            ));

        $format = new FFMpeg\Format\Video\X264();
        $format->on('progress', function ($video, $format, $percentage) {
            echo "$percentage% transcoded<br>";
        });

        $format
            ->setKiloBitrate(2000)
            ->setAudioChannels(2)
            ->setAudioKiloBitrate(128);
        
        $video->save($format, "../v/" . $configuration->request->video_id . ".mp4");
        unlink("../v/t/" . $configuration->request->video_id . ".mp4");

        echo "<a href='/v/" . $configuration->request->video_id . ".mp4'>" . $configuration->request->video_id . "</a>";
        $queue->set($configuration);
    } else {
        $_SESSION['alert'] = (object) [
            "message" => $configuration->debug->stacktrace,
            "type" => 1,
        ];
        header("Location: /upload_video");
    }
} catch(Exception $e) {
    echo "<h1>MAJOR FAIL - COPY & SEND TO DISCORD! </h1>";
    echo "<pre>" . $e . "</pre><hr>";
    echo "<pre>" . print_r($queue->returncfg(), true) . "</pre><hr>";
    echo "<pre>" . print_r($_FILES, true) . "</pre>";
    die();
}

/*
    UPLOAD VIDEO BACKEND -- ONLY EDIT IF YOU KNOW WHAT YOU'RE DOING
    $configuration - (object), holds configuration for ffmpeg & the queue
    $queue - (class), handles everything basically
    $__db - (object), PDO connection
*/