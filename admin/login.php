<?php 
include('includes/init.php');

if(isset($_GET['out'])) {
	unset($_SESSION['formsAdmin']);
	header("Location: login.php");
	exit;
}

if(isset($_POST['username'])) {
	if($_POST['username'] == $config['admin_user'] && $_POST['password'] == $config['admin_pass']) {
		
		$_SESSION['formsAdmin'] = $_POST['username'];
		
		foreach($config['forms'] as $key=>$value) {
			
			$result = mysql_query("SELECT * FROM ".$value['db']." WHERE DATEDIFF(NOW(), submit_timestamp) > 90");
			if($result) {
				add_column_if_not_exist($value['db'], '_archived', "TINYINT( 4 ) NOT NULL" );
				while($row = mysql_fetch_object($result)) {
					foreach($row as $key2=>$value2) {
						if(preg_match('/^FILE:(.*)$/i',$value2,$file)) {
							@unlink('../'.$file[1]);
						}
					}
				}
				mysql_query("UPDATE ".$value['db']." SET _archived=1 WHERE DATEDIFF(NOW(), submit_timestamp) > 90");
			}
		}
		
		
		
		header("Location: index.php");
		exit;
		
	} else {
		$_SESSION['error'] = "There was an error with your username or password.";
		header("Location: login.php");
		exit;
	}
}

include('includes/header.php'); ?>
<title>Please Login</title>
<?php include('includes/subhead.php'); ?>
	<h2>Please Login</h2>
	<?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
	<form action="" method="post">
		Username:
			<input type="text" name="username" /><br><br>
		Password:
			<input type="password" name="password" /><br><br>
			
		<input type="submit" value="Login" />
	</form>

<?php include('includes/footer.php'); ?>
