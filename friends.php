<?php
	// ob_start();
	// session_start();
	// require_once 'dbconnect.php';
	
	// if( !isset($_SESSION['user']) ) {
	// 	header("Location: index.php");//Redirect
	// 	exit;
	// }

	//1. For friends, connect to friends table 
	//2. Find the friends associated with userId
	//3. List those friends while also finding the XP total for the Id

	//Quick all look up

	function getAllUsers() {
		$allUsersQuery = mysql_query("SELECT userId FROM users GROUP BY userId LIMIT 0, 1000;");
	    $allUsersArray = array();
	    while ($row = mysql_fetch_array($allUsersQuery, MYSQL_ASSOC)) {
	       $allUsersArray[] =  $row['userId'];
	       //echo "Array Stuff TWOOOOO.";
	    }

	    //count is count of above, go through the array based on count selecting each user, and return their name, and xp amount. 

	    $sizeOfUserArray =  count($allUsersArray);//How big is our list?
	    $rangeOfUserArray = 96;//How many to show at a time - this may be more important in the future and would be the ground work for pagination
	    $arrayUserNumber =  0;

	    for ($i = 0; $i < $rangeOfUserArray; $i++) {
	        if ($arrayUserNumber >= $sizeOfUserArray) { return; }
	        $currentUserArray = mysql_fetch_array(mysql_query("SELECT * FROM user_stats WHERE userId = ".$allUsersArray[$arrayUserNumber]));
	        $currentUserNameArray = mysql_fetch_array(mysql_query("SELECT userName FROM users WHERE userId = ".$allUsersArray[$arrayUserNumber].";"));
	        $arrayUserNumber++;//advance the selection
	        echo "<p class='card'>Name: ".$currentUserNameArray[0]." XP: ".$currentUserArray[1]."</p>";
	    }
	}

?>