<?php
session_start();

require("../../app/vendor/autoload.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/config.inc.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/db.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/select.php");

$select = new \Database\Select($__db);
$video = $select->fetch_table_singlerow($_GET['v'], "videos", "video_id");

$request = (object) [
    "video_target"      => @$_GET['v'],
    "video_description" => @$_POST['youtube_description'],
    "video_title"       => @$_POST['youtube_title'],
    "video_author"      => @$_SESSION['youtube'],
    "error"          => (object) [
        "message"    => "",
        "type"       => 0,
    ],
];

/* Create a form handler class... This is extremely ugly */

$ff = ["jpg", "png", "jpeg"];
$f = pathinfo($_FILES['youtube_thumbnail']["name"], PATHINFO_EXTENSION);

function random_string($length) {
    $key = '';
    $keys = array_merge(range(0, 9), range('a', 'z'));

    for ($i = 0; $i < $length; $i++) {
        $key .= $keys[array_rand($keys)];
    }

    return $key;
}

if(!isset($_GET['v'])) {
    $request->error->type    = 1;
    $request->error->message = "Video Parameter Not Set";
}

if(!isset($_SESSION['youtube'])) {
    $request->error->type    = 1;
    $request->error->message = "You are not logged in!";
}

if(@$_SESSION['youtube'] != $video['video_author']) {
    $request->error->type    = 1;
    $request->error->message = "You do not own this video!";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_FILES['youtube_thumbnail']) && $_FILES['youtube_thumbnail']['tmp_name'] != "") {
        if(!in_array($f, $ff)) {
            $request->error->type    = 1;
            $request->error->message = "This picture does not have a valid file format.";
            header("Location: /my/edit_video?v=" . $request->video_target);
            print_r($_FILES);
            // die($_FILES);
        }

        if(!exif_imagetype($_FILES['youtube_thumbnail']["tmp_name"])) {
            $request->error->type    = 1;
            $request->error->message = "This picture is invalid.";
            header("Location: /my/edit_video?v=" . $request->video_target);
            // die("a");
        }

        if ($_FILES["youtube_thumbnail"]["size"] > 5000000) {
            $request->error->type    = 1;
            $request->error->message = "This picture is too big.";
            header("Location: /my/edit_video?v=" . $request->video_target);
            // die("c");
        }

        $fend = random_string(15) . "." . $f;
        move_uploaded_file($_FILES['youtube_thumbnail']["tmp_name"], "../v/thumb/" . $request->video_target . "/" . $fend);
        $fn = $request->video_target . "/" . $fend;

        $stmt = $__db->prepare("UPDATE videos SET video_thumbnail = ? WHERE video_id = ?");
        $stmt->execute([
            $fn,
            $request->video_target,
        ]);
    }

    if(strlen(trim($_POST['youtube_description'])) > 500) {
        $request->error->type    = 1;
        $request->error->message = "Your comment is too long.";
    }

    if(strlen(trim($_POST['youtube_title'])) > 50) {
        $request->error->type    = 1;
        $request->error->message = "Your title is too long.";
    }

    if(!isset($_POST['youtube_description']) || empty(trim($_POST['youtube_title']))) {
        $request->error->type    = 1;
        $request->error->message = "Your comment or title is empty.";
    }

    if(!$select->video_exists($_GET['v'])) {
        $request->error->type    = 1;
        $request->error->message = "This video does not exist.";
    }
} else {
    if(!$select->video_exists($_GET['v'])) {
        $request->error->type    = 1;
        $request->error->message = "This video does not exist.";
    }
}

if($request->error->message == "") {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // print_r($request);

        $stmt = $__db->prepare("UPDATE videos SET video_title = ?, video_description = ? WHERE video_id = ?");
        $stmt->execute([
            $request->video_title,
            $request->video_description,
            $request->video_target,
        ]);

        
        if(str_contains($_SERVER['HTTP_REFERER'], "watch?v=")) {
            header("Location: /watch?v=" . $request->video_target);
        } else {
            header("Location: /my/edit_video?v=" . $request->video_target);
        }
    } else if($_SERVER['REQUEST_METHOD'] === 'GET') {
        $thumbnail = $video['video_id'] . "/" . $_GET['t'] . ".jpg";
        $stmt = $__db->prepare("UPDATE videos SET video_thumbnail = ? WHERE video_id = ?");
        $stmt->execute([
            $thumbnail,
            $request->video_target,
        ]);

        echo "Successfully updated your thumbnail.";
    }
} else {
    $_SESSION['alert'] = (object) [
        "message" => $request->error->message,
        "type" => $request->error->type,
    ];
    header("Location: /my/edit_video?v=" . $request->video_target);
}