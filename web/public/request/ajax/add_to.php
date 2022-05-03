<?php 
session_start();

if(!isset($_SESSION['youtube'])) { ?>
    <span class="bg-warning icon"></span> 
    <span class="bold black" style="margin-left: 10px;">
        <a href="/sign_in">Sign In</a> or <a href="/sign_up">Sign Up</a> now!
    </span>
<?php } else { ?>
    <span class="bold black">
        This feature is not implemented yet.
    </span>
<?php } ?>