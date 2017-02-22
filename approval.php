<?php
  //NOTE, mamp stack requires <?php and can't process <?
	ob_start(); //NO IDEA WHAT THIS DOES, apparently it's helpful...
	session_start();
	require_once 'dbconnect.php';

	if( !isset($_SESSION['user']) ) {
		header("Location: index.php");//Redirect
		exit;
	}

?>

<!DOCTYPE html>
<html>
<head>
	<title>Waiting For Approval</title>
</head>
<body>
	<p>Your account is pending approval from an admin. Check back later.</p>
</body>
</html>