<?php
require_once ('./mysql_connect.php');
include ('./includes/pasword_protect.inc.php');
?>
<!DOCTYPE HTML>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
		<title>BCAS - Priority Conditions</title>
		<!-- STYLESHEETS AND GOOGLEAPI WEBFONTS -->
		<link href="css/bootstrap.min.css" rel="stylesheet">
		<link href='http://fonts.googleapis.com/css?family=Lustria' rel='stylesheet' type='text/css'>
		<link rel="stylesheet" href="css/jquery-ui-1.10.3.custom.min.css" />
		<link rel="stylesheet" href="css/main.css" />
		<!-- JS AND JQUERY EXT SCRIPTS -->
		<script src="js/jquery-1.9.1.js"></script>
		<script src="js/jquery-ui-1.10.3.custom.min.js"></script>
		<!-- CONFIRM PC DELETE -->
		<script language="JavaScript" type="text/javascript">
			function deletePC(pcID){
				if (confirm("Are you sure you want to delete?")) {
					window.location.href = 'report.php?deletePC=' + pcID; }
			}
		</script>
	</head>
	<body>
		<div class="container">
			<div class="header row" style="margin-top:20px;">
				<div class="col-md-8"><img src="img/logo.png" width="50%" /></div>
					<?php include ('./includes/links.php'); ?>
			</div><!--/header-->
			<hr>
			<?php include ('./functions.php'); ?>
			<div class="wrapper">
				<!--PC TABLE -->	
				<form action="index.php" id="pc" method="post">
				<TABLE class="table table-hover">
				<center><h4>Recently Submitted Priority Conditions</h4></center>
				<TR><TH class="hidden-xs"><center>Date</center></TH><TH><center>Asset</center></TH><TH><center>Priority Type</center></TH><TH class="hidden-xs"><center>Component Affected</center></TH>
				<TH class="hidden-xs"><center>Notified</center></TH><TH><center>Edit</center></TH><TH><center>Delete</center></TH></TR>
				<?php
					$sql = "SELECT * FROM inspforms WHERE inspectorname='".$inspname."' AND formstatus='Not Sent' AND formtype='PC' ORDER BY inspectiondate DESC";
					$rs = mysql_query($sql);
					$count = 0;
					while($row = mysql_fetch_array($rs)) {
						if ($row['formstatus'] == "Not Sent") {	
							echo "<TR><TD class='hidden-xs'><p style='font-size:10pt' align='center'>" .$row['inspectiondate']. "</p></TD><TD><p style='font-size:10pt' align='center'>" .$row['assetid']."</font></TD><TD><p style='font-size:10pt' align='center'>" .$row['description']. "</font></TD>
							<TD class='hidden-xs'><p style='font-size:10pt' align='center'>" .$row['affected']. "</font></TD><TD class='hidden-xs'><p style='font-size:10pt' align='center'>" .$row['notified']. "</font></TD>
							<td><center><button value='".$row['id']."' name='update' type='submit' style='background:#1563a3;border-radius:5px;padding:3px 10px;border:0;color:#fff;font-size:12px;'>Edit</button></center></td><td>
							<center><button onclick='deletePC(".$row['id'].")' value='".$row['id']."' name='delete' type='button' style='background:red;border-radius:5px;padding:3px 10px;border:0;color:#fff;font-size:12px;'>Delete</button></center></td></TR>\n  ";
					}
						$count ++;	
					}
					if ($count == 0) {
						echo '<h4><font color="red"><center>Sorry, there are no Priority Conditions for you to edit.</center></font></h4>';
					}
				?>
				</TABLE>
				</form>
			</div><!--contentwrapper-->
		</div><!--/container-->
	</body>
</html>