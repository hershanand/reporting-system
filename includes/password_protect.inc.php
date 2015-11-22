<?php
###############################################################
# Page Password Protect 2.13
###############################################################
# Visit http://www.zubrag.com/scripts/ for updates
############################################################### 
#
# Usage:
# Set usernames / passwords below between SETTINGS START and SETTINGS END.
# Open it in browser with "help" parameter to get the code
# to add to all files being protected. 
#    Example: password_protect.php?help
# Include protection string which it gave you into every file that needs to be protected
#
# Add following HTML code to your page where you want to have logout link
# <a href="http://www.example.com/path/to/protected/page.php?logout=1">Logout</a>
#
###############################################################

/*
-------------------------------------------------------------------
SAMPLE if you only want to request login and password on login form.
Each row represents different user.

$LOGIN_INFORMATION = array(
  'zubrag' => 'root',
  'test' => 'testpass',
  'admin' => 'passwd'
);

--------------------------------------------------------------------
SAMPLE if you only want to request only password on login form.
Note: only passwords are listed

$LOGIN_INFORMATION = array(
  'root',
  'testpass',
  'passwd'
);

--------------------------------------------------------------------
*/

##################################################################
#  SETTINGS START
##################################################################

// Add login/password pairs below, like described above
// NOTE: all rows except last must have comma "," at the end of line		

$sql = "SELECT username, password, disc, name FROM inspectors ORDER BY username";
$rs = mysql_query($sql);
$username = array();
$password = array();
$disc = array();
$fullname = array();
while($row = mysql_fetch_array($rs))
	{
		array_push($username, $row['username']);
		array_push($password, $row['password']);
		array_push($disc, $row['disc']);
		array_push($fullname, $row['name']);
	}


// Combine arrays
$LOGIN_INFORMATION = array_combine($username, $password);
$login2 = array_combine($password, $disc);
$login3 = array_combine($password, $fullname);

// request login? true - show login and password boxes, false - password box only
define('USE_USERNAME', true);

// User will be redirected to this page after logout
define('LOGOUT_URL', '../index.php');

// time out after NN minutes of inactivity. Set to 0 to not timeout
define('TIMEOUT_MINUTES', 20);

// This parameter is only useful when TIMEOUT_MINUTES is not zero
// true - timeout time from last activity, false - timeout time from login
define('TIMEOUT_CHECK_ACTIVITY', true);

##################################################################
#  SETTINGS END
##################################################################


///////////////////////////////////////////////////////
// do not change code below
///////////////////////////////////////////////////////

// show usage example
if(isset($_GET['help'])) {
  die('Include following code into every page you would like to protect, at the very beginning (first line):<br>&lt;?php include("' . str_replace('\\','\\\\',__FILE__) . '"); ?&gt;');
}

// timeout in seconds
$timeout = (TIMEOUT_MINUTES == 0 ? 0 : time() + TIMEOUT_MINUTES * 60);

// logout?
if(isset($_GET['logout'])) {
  setcookie("verify", '', $timeout, '/');//clear password
  unset($discipline); //clear disc
  setcookie("disc", '', $timeout, '/'); //clear disc
  unset($name); //clear name
  setcookie("name", '', $timeout, '/'); //clear name
  header('Location: ' . LOGOUT_URL);
  exit();
}

if(!function_exists('showLoginPasswordProtect')) {

// show login form
function showLoginPasswordProtect($error_msg) {
?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
		<title>Please enter password to access this page | BCAS</title>  	
		<!-- STYLESHEETS AND GOOGLEAPI WEBFONTS -->
		<link href="css/bootstrap.min.css" rel="stylesheet">
		<link href='http://fonts.googleapis.com/css?family=Lustria' rel='stylesheet' type='text/css'>
		<link rel="stylesheet" href="css/jquery-ui-1.10.3.custom.min.css" />
		<link rel="stylesheet" href="css/main.css" />
		<!-- JS AND JQUERY EXT SCRIPTS -->
		<script src="js/jquery-1.9.1.js"></script>
		<script src="js/jquery-ui-1.10.3.custom.js"></script>	  
	</head>
	<body>
		<div class="container">
		<div class="header row" style="margin-top:20px;">
			<div class="col-md-8"><img src="img/logo.png" width="60%" /></div>
		</div><!--/header-->
		<hr>
		<div class="wrapper">
			<style>
				input { border: 1px solid black; }
			</style>
			<div style="margin-left:auto; margin-right:auto; text-align:center">
			<form method="post">
				<h3>Please enter password to access this page</h3>
				<font color="red"><?php echo $error_msg; ?></font><br />
			<?php if (USE_USERNAME) echo '<label for="username">Login:</label><br /><input placeholder="Enter Username" class="form-control" type="input" name="access_login" /><br /><label for="password">Password:</label><br />'; ?>
				<input type="password" placeholder="Enter Password" class="form-control" name="access_password" /><p></p><input style="background:#1563a3;border-radius:5px;padding:3px 10px;border:0;color:#fff;font-size:22px;" type="submit" name="Submit" value="Sign In" />
			</form>
			<br />
			<a style="font-size:9px; color: #B0B0B0; font-family: Verdana, Arial;" href="http://www.zubrag.com/scripts/password-protect.php" title="Download Password Protector">Powered by Password Protect</a>
			</div>
			</div> <!-- end wrapper -->
			</div> <!-- end container -->
	</body>
</html>
<?php
  // stop at this point
  die();
}
}

// user provided password
if (isset($_POST['access_password'])) {

  $login = isset($_POST['access_login']) ? $_POST['access_login'] : '';
  $pass = $_POST['access_password'];
  if (!USE_USERNAME && !in_array($pass, $LOGIN_INFORMATION)
  || (USE_USERNAME && ( !array_key_exists($login, $LOGIN_INFORMATION) || $LOGIN_INFORMATION[$login] != $pass ) ) 
  ) {
    showLoginPasswordProtect("Incorrect password.");
  }
  else {
    // set cookie if password was validated
    setcookie("verify", md5($login.'%'.$pass), $timeout, '/');
    foreach($login2 as $key=>$val) // Set the discipline
		{
			if($key==$pass)
				{
					$discipline = $val;
					setcookie("disc", $discipline, $timeout, '/');
				}
		}
    foreach($login3 as $key=>$val) // Set the inspector's name
		{
			if($key==$pass)
				{
					$name = $val;
					setcookie("name", $name, $timeout, '/');
				}
		}	  
    // Some programs (like Form1 Bilder) check $_POST array to see if parameters passed
    // So need to clear password protector variables
    unset($_POST['access_login']);
    unset($_POST['access_password']);
    unset($_POST['Submit']);
  }

}

else {

  // check if password cookie is set
  if (!isset($_COOKIE['verify']) || !isset($_COOKIE['disc'])) {
    showLoginPasswordProtect("");
  }

  // check if cookie is good
  $found = false;
  foreach($LOGIN_INFORMATION as $key=>$val) {
    $lp = (USE_USERNAME ? $key : '') .'%'.$val;
    if ($_COOKIE['verify'] == md5($lp)) {
      $found = true;
      // prolong timeout
      if (TIMEOUT_CHECK_ACTIVITY) {
        setcookie("verify", md5($lp), $timeout, '/');
		foreach($login2 as $key=>$val) // Set the discipline
			{
				if($key==$pass)
					{
						$discipline = $val;
						setcookie("disc", $discipline, $timeout, '/');
					}
			}
		foreach($login3 as $key=>$val) // Set the inspector's name
			{
				if($key==$pass)
					{
						$name = $val;
						setcookie("name", $name, $timeout, '/');
					}
			}
      }
      break;
    }
  }
  if (!$found) {
    showLoginPasswordProtect("");
  }
}
?>
