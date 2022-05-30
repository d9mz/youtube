<?php
session_start();

require("../../../app/vendor/autoload.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/config.inc.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/db.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/select.php");

$select = new \Database\Select($__db);
$video = $select->fetch_table_singlerow($_SESSION['youtube'], "users", "youtube_username");

$request = (object) [
    "user_target"    => @$_SESSION['youtube'],
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
$ffmpeg = FFMpeg\FFMpeg::create();
$ffprobe = FFMpeg\FFProbe::create();
$ff = ["jpg", "png", "jpeg"];
$f = pathinfo($_FILES['profile_picture']["name"], PATHINFO_EXTENSION);

function random_string($length) {
    $key = '';
    $keys = array_merge(range(0, 9), range('a', 'z'));

    for ($i = 0; $i < $length; $i++) {
        $key .= $keys[array_rand($keys)];
    }

    return $key;
}

print_r($_FILES);
if(!isset($_FILES['profile_picture'])) {
    $request->error->type    = 1;
    $request->error->message = "Profile picture parameter is empty";
}

if(!isset($_SESSION['youtube'])) {
    $request->error->type    = 1;
    $request->error->message = "You are not logged in!";
}

if(!in_array($f, $ff)) {
    $request->error->type    = 1;
    $request->error->message = "This picture does not have a valid file format.";
}

if($request->error->message == "") {
    if(!exif_imagetype($_FILES['profile_picture']["tmp_name"])) {
        $request->error->type    = 1;
        $request->error->message = "This picture is invalid.";
        die(false);
    }

    if ($_FILES["profile_picture"]["size"] > 5000000) {
        $request->error->type    = 1;
        $request->error->message = "This picture is too big.";
        die(false);
    }

    $fend = random_string(15) . "." . $f;
    move_uploaded_file($_FILES['profile_picture']["tmp_name"], "../../v/p/" . $fend);
    
    $stmt = $__db->prepare("UPDATE users SET youtube_picture = ? WHERE youtube_username = ?");
    $stmt->execute([
        $fend,
        $request->user_target,
    ]);
    echo true;
} else {
    $_SESSION['alert'] = (object) [
        "message" => $request->error->message,
        "type" => $request->error->type,
    ];

    echo false;
}