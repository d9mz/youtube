<?php
session_start();

require("../../../../app/vendor/autoload.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/config.inc.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/db.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/select.php");

$select = new \Database\Select($__db);
$user = $select->fetch_table_singlerow($_SESSION['youtube'], "users", "youtube_username");
$user["youtube_colors"] = json_decode($user["youtube_colors"]);

$request = (object) [
    "user_input"     => $_POST['comments'],
    "user_target"    => $_SESSION['youtube'],
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

if(!isset($_SESSION['youtube'])) {
    $request->error->type    = 1;
    $request->error->message = "You are not logged in!";
}

if(isset($_POST['comments'])) {
	echo "test";
}

print_r($_POST);

if($request->error->message == "") {
	$user["youtube_modules"] = json_decode($user["youtube_modules"]);
	if(isset($_POST['comments']) && $_POST['comments'] == "y") {
		$user["youtube_modules"]->comments_mod = true;
		$user["youtube_modules"] = json_encode($user["youtube_modules"]);
		$stmt = $__db->prepare("UPDATE users SET youtube_modules = ? WHERE youtube_username = ?");
		$stmt->execute([
			$user["youtube_modules"],
			$request->user_target,
		]);
		
		echo "LogicTrue";
	} else if(!isset($_POST['comments'])) {
		$user["youtube_modules"]->comments_mod = false;
		$user["youtube_modules"] = json_encode($user["youtube_modules"]);
		$stmt = $__db->prepare("UPDATE users SET youtube_modules = ? WHERE youtube_username = ?");
		$stmt->execute([
			$user["youtube_modules"],
			$request->user_target,
		]);
		
		echo "LogicFalse";
	}
} else {
    $_SESSION['alert'] = (object) [
        "message" => $request->error->message,
        "type" => $request->error->type,
    ];

    echo false;
}