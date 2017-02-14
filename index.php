<?php
	// Reference: http://www.codingcage.com/2015/01/user-registration-and-login-script-using-php-mysql.html
	ob_start();
	session_start();
	require_once 'dbconnect.php';
	
	// it will never let you open index(login) page if session is set
	if ( isset($_SESSION['user'])!="" ) {
		header("Location: home.php");
		exit;
	}
	
	$error = false;
	
	if( isset($_POST['btn-login']) ) {	
		
		// prevent sql injections/ clear user invalid inputs
		$email = trim($_POST['email']);
		$email = strip_tags($email);
		$email = htmlspecialchars($email);
		
		$pass = trim($_POST['pass']);
		$pass = strip_tags($pass);
		$pass = htmlspecialchars($pass);
		// prevent sql injections / clear user invalid inputs
		
		if(empty($email)){
			$error = true;
			$emailError = "Please enter your email address.";
		} else if ( !filter_var($email,FILTER_VALIDATE_EMAIL) ) {
			$error = true;
			$emailError = "Please enter valid email address.";
		}
		
		if(empty($pass)){
			$error = true;
			$passError = "Please enter your password.";
		}
		
		// if there's no error, continue to login
		if (!$error) {
			
			$password = hash('sha256', $pass); // password hashing using SHA256
		
			$res=mysql_query("SELECT userId, userName, userPass FROM users WHERE userEmail='$email'");
			$row=mysql_fetch_array($res);
			$count = mysql_num_rows($res); // if uname/pass correct it returns must be 1 row
			
			if( $count == 1 && $row['userPass']==$password ) {
				$_SESSION['user'] = $row['userId'];
				header("Location: home.php");
			} else {
				$errMSG = "Incorrect Credentials, Try again...";
			}
				
		}
		
	}
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Design Arena</title>
	<link rel="stylesheet" href="./css/dashing.css" type="text/css" />
	<link rel="stylesheet" href="./css/custom.css" type="text/css" />
</head>
<body>

	<div class="card card--login center-align">
	  	<div class="card--header">
	    	<h2>Design Arena - Login</h2>
	  	</div>

	    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" autocomplete="off">

            <fieldset class="card--content">
            	<?php
				if ( isset($errMSG) ) {
					echo $errMSG;
				}
				?>

		      <div class="row row--nested">
		        <div class="column column--full column--nested">
		        	<label>Email</label>
          			<input type="email" name="email" class="form-control" placeholder="Your Email" value="<?php echo $email; ?>" maxlength="40" />
            		<span><?php echo $emailError; ?></span>
		        </div>
		       	<div class="column column--full column--nested">
	          		<label>Password</label>
	          		<input type="password" name="pass" placeholder="Your Password" maxlength="15" />
	            	<span><?php echo $passError; ?></span>
		        </div>
		      </div>

		    </fieldset>

	       	<div class="card--footer">
		       	<button type="submit" name="btn-login">Sign In</button>        
		      	<a href="register.php">Sign Up ↗</a>
		    </div>		
		</form>

	</div>

</body>
</html>
<?php ob_end_flush(); ?>