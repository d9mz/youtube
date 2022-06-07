<?php
/* id generation 
   
*/

class VideoIDGeneration {
    function GenerateVideoID() {
        $bytes = random_bytes(8);
        $base64 = base64_encode($bytes);
        return rtrim(strtr($base64, '+/', '-_'), '=');
    }

    function GenerateVflD() {
        $bytes = random_bytes(6);
        $base64 = base64_encode($bytes);
        return "vfl" . rtrim(strtr($base64, '+/', '_'), '=');
    }

    function GenerateRefID() {
        $bytes = random_bytes(20);
        $base64 = base64_encode($bytes);
        return "utue_" . rtrim(strtr($base64, '+/', '_'), '=');
    }
}
