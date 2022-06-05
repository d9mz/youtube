<?php
namespace Database;

class Insert {
    public $__db;
	public function __construct($conn){
        $this->__db = $conn;
	}

    function send_notification(string $to_user, string $from, string $subject, string $message, string $video = "") {
		$stmt = $this->__db->prepare(
			"INSERT INTO inbox 
				(inbox_message, inbox_author, inbox_subject, inbox_to, inbox_meta) 
			 VALUES 
				(?, ?, ?, ?, ?)"
		);
		$return = $stmt->execute([
			$message,
			$from,
			$subject,
			$message,
			$video,
		]);
		
		return $return;
    }
}