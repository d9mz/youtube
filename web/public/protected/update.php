<?php
namespace Database;

class Update {
    public $__db;
	public function __construct($conn){
        $this->__db = $conn;
	}

    function update_cooldown($user, $type) {
        $stmt = $this->__db->prepare("UPDATE users SET " . $type . " = NOW() WHERE youtube_username = ?");
        $res = $stmt->execute([ $user ]);
        return $res;
    }
}