<?php
namespace Database;

class Insert {
    public $__db;
	public function __construct($conn){
        $this->__db = $conn;
	}

    function send_notification(string $to_user, string $from, string $subject, string $message, string $video = "", string $category = "Normal") {
		$stmt = $this->__db->prepare(
			"INSERT INTO inbox 
				(inbox_message, inbox_author, inbox_subject, inbox_to, inbox_meta, inbox_category) 
			 VALUES 
				(?, ?, ?, ?, ?, ?)"
		);
		$return = $stmt->execute([
			$message,
			$from,
			$subject,
			$to_user,
			$video,
			$category,
		]);
		
		return $return;
    }
}