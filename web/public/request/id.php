<?php
require($_SERVER['DOCUMENT_ROOT'] . "/protected/id.php");

$id = new VideoIDGeneration();
echo $id->GenerateRefID();