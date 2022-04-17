<?php
try
{
    $__db = new PDO("mysql:host=" . $config->db_config->db_host . ";dbname=" . $config->db_config->db_name . ";charset=utf8mb4",
        $config->db_config->db_username,
        $config->db_config->db_password,
        [
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    );
    $config->db_config->db_connected = true;
}
catch(PDOException $e)
{
    die("An error occured connecting to the database: " . $e->getMessage());
}
