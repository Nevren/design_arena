<?php
	ob_start(); //NO IDEA WHAT THIS DOES, apparently it's helpful...
	session_start();
	require_once 'dbconnect.php';
	
	if( !isset($_SESSION['user']) ) {
		header("Location: index.php");//Redirect
		exit;
	}
	$userRow=mysql_fetch_array(mysql_query("SELECT * FROM users WHERE userId=".$_SESSION['user']));

	if( $userRow[4] != 200) {//Check if user status (role) if it's not 200, account is not admin
    	header("Location: index.php");//Redirect
    	exit;  
  	}

  	//Add quest to database
	if( isset($_POST['addQuest']) 
		&& isset($_POST['quest_name']) 
		&& isset($_POST['quest_description']) 
		&& isset($_POST['quest_xp_main']) 
		&& isset($_POST['quest_xp_bonus'])
		&& isset($_POST['quest_hour_multiplier'])) {

		$_POST['quest_description'] = mysql_real_escape_string($_POST['quest_description']);
		$_POST['quest_name'] = mysql_real_escape_string($_POST['quest_name']);
		$query = "INSERT INTO quest(quest_name,quest_description,quest_xp_main,quest_xp_bonus,quest_hour_multiplier) VALUES('".$_POST['quest_name']."','".$_POST['quest_description']."','".$_POST['quest_xp_main']."','".$_POST['quest_xp_bonus']."','".$_POST['quest_hour_multiplier']."');";
		$addQuest = mysql_query($query);
		header("Location: settings.php");//Redirect - Prevents page reload sending quest complete
    	exit;
	}

	if( isset($_POST['updateAnnouncement'])) {
		$_POST['announcement_string'] = mysql_real_escape_string($_POST['announcement_string']);
		// echo $_POST['announcement_string'];
		$query = "UPDATE announcement SET announcement='".$_POST['announcement_string']."' WHERE announcementId=0;";
		$updateAnnouncement = mysql_query($query);
	}

	//Empty out the completed quests table
	if( isset($_POST['resetQuests'])){
		$dailyReset=mysql_query("TRUNCATE TABLE quest_complete;");
  	}
?>
<!DOCTYPE html>
<html>
<title>Design Arena - Manage</title>
  <link rel="stylesheet" href="./css/dashing.css" type="text/css" />
  <link rel="stylesheet" href="./css/custom.css" type="text/css" />
</head>

<body>
	<nav class="app-menu app-title--orange">
		<div class="app-context">
			<div class="app-title">Design Arena</div>
		</div>
		<ul class="app-navigation align-right">
			<li><a href="home.php"><i class="dashing-icon dashing-icon--checkmark-filled"></i>Return to Game</a></li>
			<li><a href="logout.php?logout"><i class="dashing-icon dashing-icon--close"></i>Sign Out</a></li>
		</ul>
	</nav>
	<div class="app-content">
		  <div class="card card--login center-align">
		    <div class="card--header">
		      <h3 class=""><?php echo $userRow[1]; ?> - Game Management</h3>
		      <small class="align-right"><a href="logout.php?logout">Sign Out</a></small>
		    </div>
		    <div class="card--content">
		      <p class="remove-margin--top"></p>
		    </div>
		  </div>

		<div class="card card--login center-align">
	      <h3 class="card--header has-border">Make a Quest!</h3>
	      <div class="card--content">
	      <small>Input the quest details!</small>
	        <form method="post">
	          <fieldset class="column column--full">

	              <label for="quest_name">Quest Name (45 characters)</label>
	              <input type="text" name="quest_name" id="quest_name" required>
	              <label for="quest_description">Quest Description (255 characters)</label>
	              <input type="text" name="quest_description" id="quest_description" required>
	              <label for="quest_xp_main">Quest XP Main</label>
	              <input type="number" name="quest_xp_main" id="quest_xp_main" required>
	              <label for="quest_xp_bonus">Quest XP Bonus (If Hourly, Mark 0)</label>
	              <input type="number" name="quest_xp_bonus" id="quest_xp_bonus" required>
	              <label for="quest_hour_multiplier">Quest Hour Multiplier (Power of 10. If none, enter 0)</label>
	              <input type="number" name="quest_hour_multiplier" id="quest_hour_multiplier" required>

	          </fieldset>
	          <input class="button button--smooth button--blue" type="submit" name="addQuest" value="Add Quest">
	        </form>
	      </div>
	    </div>

	    <div class="card card--login center-align">
		    <div class="card--content">
			    <form method="post">
		          <fieldset class="column column--full">

		              <h1>Reset Daily Quests</h1>
		              <input type="hidden" name="quest_reset" id="quest_reset" required>

		          </fieldset>
		          <input class="button button--smooth button--orange" type="submit" name="resetQuests" value="Reset Quest">
		        </form>
		    </div>
	    </div>

	    <div class="card card--login center-align">
	      <h3 class="card--header has-border">Global Announcement</h3>
	      <div class="card--content">
	      <small>What would you like to say?</small>
	        <form method="post">
	          <fieldset class="column column--full">

	              <label for="announcement_string">Global Announcement (255 Characters)</label>
	              <input type="text" name="announcement_string" id="announcement_string" required>

	          </fieldset>
	          <input class="button button--smooth button--blue" type="submit" name="updateAnnouncement" value="Update">
	        </form>
	      </div>
	    </div>

	</div>
</body>
</html>
<?php ob_end_flush(); ?>