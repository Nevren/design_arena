<?php
	ob_start();
	session_start();
	if( isset($_SESSION['user'])!="" ){
		header("Location: home.php");
	}
	include_once 'dbconnect.php';

	$error = false;

	if ( isset($_POST['btn-signup']) ) {
		
		// clean user inputs to prevent sql injections
		$name = trim($_POST['name']);
		$name = strip_tags($name);
		$name = htmlspecialchars($name);
		
		$email = trim($_POST['email']);
		$email = strip_tags($email);
		$email = htmlspecialchars($email);
		
		$pass = trim($_POST['pass']);
		$pass = strip_tags($pass);
		$pass = htmlspecialchars($pass);

		// basic name validation
		if (empty($name)) {
			$error = true;
			$nameError = "Please enter your full name.";
		} else if (strlen($name) < 3) {
			$error = true;
			$nameError = "Name must have atleat 3 characters.";
		} else if (!preg_match("/^[a-zA-Z ]+$/",$name)) {
			$error = true;
			$nameError = "Name must contain alphabets and space.";
		}
		
		//basic email validation
		if ( !filter_var($email,FILTER_VALIDATE_EMAIL) ) {
			$error = true;
			$emailError = "Please enter valid email address.";
		} else {
			// check email exist or not
			$query = "SELECT userEmail FROM users WHERE userEmail='$email'";
			$result = mysql_query($query);
			$count = mysql_num_rows($result);
			if($count!=0){
				$error = true;
				$emailError = "Provided Email is already in use.";
			}
		}
		// password validation
		if (empty($pass)){
			$error = true;
			$passError = "Please enter password.";
		} else if(strlen($pass) < 6) {
			$error = true;
			$passError = "Password must have atleast 6 characters.";
		}
		
		// password encrypt using SHA256();
		$password = hash('sha256', $pass);
		
		// if there's no error, continue to signup
		if( !$error ) {
			
			$query = "INSERT INTO users(userName,userEmail,userPass) VALUES('$name','$email','$password')";
			$res = mysql_query($query);
				
			if ($res) {
				$errTyp = "success";
				$errMSG = "Successfully registered, you may login now";
				unset($name);
				unset($email);
				unset($pass);
			} else {
				$errTyp = "danger";
				$errMSG = "Something went wrong, try again later...";	
			}	
				
		}
		
		
	}
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Design Arena - Register</title>
	<link rel="stylesheet" href="./css/dashing.css" type="text/css" />
	<link rel="stylesheet" href="./css/custom.css" type="text/css" />
</head>
<body>
	<div class="app-content">
		<div class="card card--login center-align">
		  	<div class="card--header">
		    	<h2>Design Arena - Register</h2>
		  	</div>

		    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" autocomplete="off">
		            <fieldset class="card--content">
			            <?php
						if ( isset($errMSG) ) {
							
							?>

			            	<div class="alert alert-<?php echo ($errTyp=="success") ? "success" : $errTyp; ?>">
							<?php echo $errMSG; ?>
			                </div>
			                <?php
						}
						?>

						<div class="row row--nested">
					        <div class="column column--full column--nested">
					        	<label>Name</label>
			          			<input type="text" name="name" class="form-control" placeholder="Enter Name" maxlength="50" value="<?php echo $name ?>" />
			                	<span class="text-danger"><?php echo $nameError; ?></span>
					        </div>
					       	<div class="column column--full column--nested">
				          		<label>Email</label>
				          		<input type="email" name="email" class="form-control" placeholder="Enter Your Email" maxlength="40" value="<?php echo $email ?>" />
			                	<span class="text-danger"><?php echo $emailError; ?></span>
					        </div>
					        <div class="column column--full column--nested">
				          		<label>Password</label>
				          		<input type="password" name="pass" class="form-control" placeholder="Enter Password" maxlength="15" />
			                	<span class="text-danger"><?php echo $passError; ?></span>
					        </div>
				      	</div>

			        </fieldset>

			       	<div class="card--footer">
						<button type="submit" name="btn-signup">Sign Up</button>
			            <a href="index.php">Sign In</a>
			    	</div>	

		    </form>

	    </div>
	</div>
</body>
</html>
<?php ob_end_flush(); ?>