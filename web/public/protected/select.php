<?php
namespace Database;

class Select {
    public $__db;
	public function __construct($conn){
        $this->__db = $conn;
	}

    function fetch_table_singlerow($find_specific_in_table, $table_name, $where_column_name) {
        $stmt = $this->__db->prepare("SELECT * FROM " . $table_name . " WHERE " . $where_column_name . " = :find");
        $stmt->bindParam(":find", $find_specific_in_table);
        $stmt->execute();

        return ($stmt->rowCount() === 0 ? 0 : $stmt->fetch(\PDO::FETCH_ASSOC));
    }

    function user_exists($user) {
        $stmt = $this->__db->prepare("SELECT username FROM users WHERE username = :username");
        $stmt->bindParam(":username", $user);
        $stmt->execute();

        return $stmt->rowCount() === 1;
    }

    function video_exists($id) {
        $stmt = $this->__db->prepare("SELECT video_id FROM videos WHERE video_id = :id");
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        return $stmt->rowCount() === 1;
    }
}