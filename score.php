<?php

include_once('config.php');

if(isset($_GET['setscore'])){
	$maxscore = check_string($_GET['setscore']);

	mysqli_query($connection, "UPDATE users SET maxscore='" . $maxscore . "' WHERE username='" . $_SESSION['username'] ."'");

	echo $maxscore;
	echo mysqli_error($connection);
}

?>