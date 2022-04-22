<?php
namespace VideoQueue;

class QueueBase {
    public $__db;
    public $__configuration;

	public function __construct(object $conn, object $cfg){
        $this->__db = $conn;
        $this->__configuration = $cfg;
	}


}