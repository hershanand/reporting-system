<div class="col-md-4">
	<ul class="nav nav-pills pull-right" >
		<div class ="wrapper">
			<?php
			//TRIMS NAME TO ONLY FIRST NAME
			if (isset($name)) {
				$arr = explode(' ',trim($name));
			}
			else {
			$arr = explode(' ',trim($_COOKIE['name']));
			}
			//DISPLAYS NAME
			echo 'Welcome <b>'.$arr[0].'</b>';	
			?>
		</div>
	</ul>
</div>
<div class="col-md-4">
	<ul class="nav nav-pills pull-right" style="padding:8px;">
		<?php 
			$url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
		?>
		<li <?php 
			if (false !== strpos($url,'index')) {
				echo 'class="active"';
			} ?>
		><a href="index.php">Home</a></li>
		<li <?php 
			if (false !== strpos($url,'report')) {
				echo 'class="active"';
			} ?>
		><a href="report.php">Report</a></li>
		<li><a href="./includes/password_protect.inc.php?logout=1">Logout</a></li>
	</ul>
</div>