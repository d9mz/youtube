<?php
session_start();

require("../../app/vendor/autoload.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/config.inc.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/db.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/queue.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/id.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/update.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/select.php");

if(!isset($_SESSION['youtube'])) {
    header("Location: /sign_in");
}

$uID = new VideoIDGeneration();
$select = new \Database\Select($__db);
$update = new \Database\Update($__db);
$id = $uID->GenerateVideoID();

$configuration = (object) [
    "request" => (object) [
        "video_author"      => @$_SESSION['youtube'],
        "video_id"          => $id,
        "video_description" => $_POST['youtube-description'],
        "video_title"       => $_POST['youtube-title'],
        "video_category"    => $_POST['youtube-category'],
        "video_tags"        => $_POST['youtube-tags'],
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

    if(strlen(trim($configuration->request->video_title)) > 50) {
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

    /*
    if($select->upload_cooldown($_SESSION['youtube'])) {
        $configuration->debug->stacktrace = "You are under a cooldown. This lasts 10 minutes.";
        $queue->set($configuration);
    }
    */

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
        $configuration->request->video_meta->video_duration = round($ffprobe->format("../v/t/" . $configuration->request->video_id . $configuration->request->video_meta->video_file_type)->get('duration'));

        mkdir("../v/thumb/" . $configuration->request->video_id . "/");
        $interval = floor( $configuration->request->video_meta->video_duration / 3 );
        for($i = 0; $i < 3; $i++){
            $frame = $video->frame(FFMpeg\Coordinate\TimeCode::fromSeconds( $i * $interval ));
            $frame->save( '../v/thumb/' . $configuration->request->video_id . "/" . $i . '.jpg');
        }

        // default value - can change later
        $configuration->request->video_meta->video_thumbnail = $configuration->request->video_id . "/1.jpg";

        $video
            ->filters()
            ->resize(new FFMpeg\Coordinate\Dimension(480, 360))
            ->synchronize();

        $format = new FFMpeg\Format\Video\X264();
        $format->setAdditionalParameters(['-movflags', '+faststart']);
        
        $format->on('progress', function ($video, $format, $percentage) {
            // todo : progress bar
        });
        $format
            ->setKiloBitrate(1000)
            ->setAudioChannels(2)
            ->setAudioKiloBitrate(128);

        $video->save($format, "../v/" . $configuration->request->video_id . $configuration->request->video_meta->video_file_type);
        unlink("../v/t/" . $configuration->request->video_id . $configuration->request->video_meta->video_file_type);

        $stmt = $__db->prepare(
            "INSERT INTO videos 
                (video_id, video_title, video_description, video_author, video_tags, video_category, video_thumbnail, video_duration, video_ext) 
             VALUES 
                (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $configuration->request->video_id,
            $configuration->request->video_title,
            $configuration->request->video_description,
            $configuration->request->video_author,
            $configuration->request->video_tags,
            $configuration->request->video_category,
            $configuration->request->video_meta->video_thumbnail,
            $configuration->request->video_meta->video_duration,
			$configuration->request->video_meta->video_file_type,
        ]);
        $stmt = null;

        $_SESSION['alert'] = (object) [
            "message" => "Succesfully uploaded your video!",
            "type" => 0,
        ];
        $queue->set($configuration);
        $update->update_cooldown($_SESSION['youtube'], "last_upload");
        header("Location: /watch?v=" . $configuration->request->video_id);
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