<?php
require_once ('./mysql_connect.php');
include ('./includes/password_protect.inc.php');
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
		<script>
			$.widget( "custom.catcomplete", $.ui.autocomplete, {
				_renderMenu: function( ul, items ) {
					var that = this,
						currentCategory = "";
					$.each( items, function( index, item ) {
						if ( item.category != currentCategory ) {
							ul.append( "<li class='ui-autocomplete-category'>" + item.category + "</li>" );
							currentCategory = item.category;
						}
						that._renderItemData( ul, item );
					});
				}
			});
		</script>
		<script type="text/javascript">
			$(document).ready(function() {
				//SEARCH ASSET
				var data = [
						<?php
							$sql = "SELECT id, Boro FROM assets ORDER BY id";
							$rs = mysql_query($sql);
							while($row = mysql_fetch_array($rs))
								{
									echo  "{label: \"".$row['id']."\", category: \"".$row['Boro']."\" }, \n ";
								}
							echo "{label: \"X999\", category: \"X\" } \n ";
							mysql_free_result($rs);
						?>	
				];
				$('#otherpriority').hide();
				checkOther();
				$('#assetid').catcomplete({
					delay: 0,
					source: data
				});
				$('#doesexist').button();
				$('#datepick').datepicker({ maxDate: "+1Y", dateFormat: "yy-mm-dd"});
				$('#po18_filed').click(function() {
					$('#po18_isfiled').fadeIn(1000);
					$('#po18_isfiled').html('<label for="filed_date">File Date</label><input <?php 
					if (isset($_POST['update'])){
						$sql = "SELECT * FROM inspforms WHERE id='".$_POST['update']."' ORDER BY id";
						$rsEdit = mysql_query($sql);
						while($rowEdit = mysql_fetch_array($rsEdit)) {
						echo ' value="'.$rowEdit['podate'].'"';
						}
					}
					?> name="filed_date" id="filed_date" class="form-control" placeholder="Enter Filed Date" required /><br/><label for="asset">PO #</label><input <?php
					if (isset($_POST['update'])) {
						$sql = "SELECT * FROM inspforms WHERE id='".$_POST['update']."' ORDER BY id";
						$rsEdit = mysql_query($sql);
						while($rowEdit = mysql_fetch_array($rsEdit)) {
							echo ' value="'.$rowEdit['ponumber'].'"';
						}
					} ?> size="8" maxlength="8" name="filed_num" id="filed_num" class="form-control" placeholder="Enter PO Number" required /><br/>');
					$('#filed_date').datepicker({ maxDate: "+1Y", dateFormat: "yy-mm-dd"});
				});
				//NOT FILED - HIDE PO FORM
				$('#po18_notfiled').click(function(){
					$('#po18_isfiled').html('');
					$('#po18_isfiled').hide();
				});
				//FILED NO NUMBER - HIDE PO FORM	
				$('#po18_nonumber').click(function(){
					$('#po18_isfiled').html('');
					$('#po18_isfiled').hide();
				});
				<?php
				//EDIT PC: TRIGGER CLICK FOR PO18-FILED
				if (isset($_POST['update'])) {
					$sql = "SELECT * FROM inspforms WHERE id='".$_POST['update']."' ORDER BY id";
					$rsEdit = mysql_query($sql);
					//TRIGGER IF PO # EXIST & DATE IS SET
					while($rowEdit = mysql_fetch_array($rsEdit)) {
						if (isset($rowEdit['ponumber']) && $rowEdit['podate'] != "0000-00-00"){
							echo "$('#po18_filed').trigger(\"click\")";
						}
					}
				}
				?>
				$(document).on('change', '.btn-file :file', function() {
					var input = $(this),
						numFiles = input.get(0).files ? input.get(0).files.length : 1,
						label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
						input.trigger('fileselect', [numFiles, label]);
				});
				$('.btn-file :file').on('fileselect', function(event, numFiles, label) {
					var input = $(this).parents('.input-group').find(':text'),
					log = numFiles > 1 ? numFiles + ' files selected' : label;
					if( input.length ) {
						input.val(log);
					} 
					else {
						if( log ) alert(log);
					}
				});
				$("#priorityconditions").click(function(e){
					e.preventDefault();
					var pc = $(this).val();
					$.ajax({
						url: 'priority.php?q='+pc,
						cache: false,
						contentType: false,
						processData: false,
						success:function(data){
							console.log("success");
							document.getElementById("priority_two").innerHTML=data;
							checkOther();
						},
						error: function(data){
							console.log("error");
							console.log(data);
						}
					});
				});
			function checkOther(){
				if( $('#priorityconditions2').val()=="Other (Text)"){
					// show other priority text box
					$('#otherpriority').show();
				}
				else {
					//hide other priority text box and set it to blank
					$('#otherpriority').hide();
					$('#othercomponent').val("");
				}	
			}
			});
		</script>
	</head>
	<body>
		<div class="container">
			<div class="header row" style="margin-top:20px;">
				<div class="col-md-8"><img src="img/logo.png" width="50%" /></div>
				<?php include ('./includes/links.php'); ?>
			</div><!--/header-->
			<hr>
			<?php include ('./functions.php'); ?> <!-- Incude upload script -->
			<div class="wrapper">
				<?php
					//Edit PC report
					if (isset($_POST['update'])) {
						$sql = "SELECT * FROM inspforms WHERE id='".$_POST['update']."' ORDER BY id";
						$rsEdit = mysql_query($sql);
						$rowEdit = mysql_fetch_array($rsEdit);
					}
				?>
				<form <?php if (isset($_POST['update'])) { echo 'action="report.php"';} else {echo 'action="index.php"';} ?>  id="pc" method="post" enctype="multipart/form-data">
					<label for="assetid">Asset:</label>
					<input <?php echo 'value="'.$rowEdit['assetid'].'"'; ?> size="4" maxlength="4" name ="assetid" id="assetid" class="form-control" placeholder="Search Asset" required autofocus/><br/>
					<label for="instance">Instance:</label>
					<input <?php echo 'value="'.$rowEdit['instance'].'"'; ?> type="number" size="2" maxlength="2" name="instance" id="instance" class="form-control" placeholder="Enter Instance" required/><br/>
					<label for="date">Date (YYYY-MM-DD):</label>
					<input name="datepick" id="datepick" value="<?php if (isset($_POST['update'])) { echo $rowEdit['inspectiondate'];} else {echo date('Y-m-d');} ?>" class="form-control" required/><br/>
					<strong>PC Exist Last Year? </strong>
					<div class="radio" style="display:inline-block;margin:10px 3px;">
						<label>
						<input value="1" type="radio" id="repeatPC" name="repeatPC" <?php if ($rowEdit['repeatPC'] == "1" ) echo 'checked'; ?>>
						YES
						</label>
					</div>
					<div class="radio" style="display:inline-block;margin:10px 3px;">
						<label>
							<input value="0" type="radio" id="repeatPC" name="repeatPC" required <?php if ($rowEdit['repeatPC'] == "0" ) echo 'checked'; ?>>
							NO
						</label>
					</div></br>
					<strong>PO-18: </strong>
					<div name="po18addit" class="radio" style="display:inline-block;margin:10px 3px;">
						<label>
							<input value="Filed" type="radio" name="po18addit" id="po18_filed" required <?php if (isset($rowEdit['postatus']) && $rowEdit['postatus'] == "Filed") echo 'checked'; ?>>
							FILED
						</label>
					</div> 
					<div class="radio" style="display:inline-block;margin:10px 3px;">
						<label>
							<input value="Not Filed" type="radio" name="po18addit" id="po18_notfiled" required <?php if (isset($rowEdit['postatus']) && $rowEdit['postatus'] == "Not Filed") echo 'checked'; ?>>
							NOT FILED
						</label>
					</div>
					<div class="radio" style="display:inline-block;margin:10px 3px;">
						<label>
						<input value="Filed But No Number" type="radio" name="po18addit" id="po18_nonumber" required <?php if (isset($rowEdit['postatus']) && $rowEdit['postatus'] == "Filed But No Number") echo 'checked';?>>
						FILED (NO NUMBER)
						</label>
					</div>
					<div id="po18_isfiled"></div> <!--po18_isfiled container-->
					</br>
					<label for="priorityconditions">Priority Category:</label>
					<select name="priorityconditions" id="priorityconditions" class="form-control">
						<!-- QUERY FOR PRIORITY TYPE -->
						<?php
							$sql = "SELECT DISTINCT type FROM prioritytypes WHERE disc='".$discipline."' AND structural=' ' ORDER BY type";
							$rs = mysql_query($sql);
							while($row = mysql_fetch_array($rs)) {
								echo '<option value="'.$row['type'].'"';
								if ($rowEdit['description'] == $row['type']) echo 'selected="selected"';
								echo '/>'.$row['type'].'</option> \n';
							} ?>
					</select><br/>
					<div id="priority_two">
						<?php
							if (isset($_POST['update'])) {
							$sql = "SELECT disc, type, affected FROM prioritytypes WHERE type='".$rowEdit['description']."' AND disc='".$discipline."' AND structural=' ' ORDER BY type";
							$rs = mysql_query($sql);
							$priority2 = array();
							while($row = mysql_fetch_array($rs)) {
								array_push($priority2, $row['affected']);
							} ?>
						<label for='location'>Component Affected:</label>
						<select name='priorityconditions2' id='priorityconditions2' class='form-control' onclick='checkOther()'>
							<?php 
								$boolFindpc2=false;
								for ($i = 0; $i < sizeof($priority2); $i++) {
									echo '<option value="'.$priority2[$i].'"';
									if ($rowEdit['affected'] == $priority2[$i]){
										echo 'selected="selected"';	
										$boolFindpc2=true;
									} 
									// if nothing matched and last item 'Other (Text)', select 'Other (Text)'
									if(!$boolFindpc2 && $priority2[$i]=="Other (Text)") echo 'selected="selected"';
									echo '/>'.$priority2[$i].'</option> \n';
								} //end update ?>
						</select><br/>	
					<?php } ?></div><!--end priority2-->
					<div id="otherpriority">
						<label for="othercomponent">Other (Text):</label>
						<input name="othercomponent" id="othercomponent" class="form-control" placeholder="Enter Other Text"
						<?php if (!$boolFindpc2) echo 'value="'.$rowEdit['affected'].'"'; ?>><br/>
					</div><!--end otherpriority-->
					<label for="comments">Condition Description:</label>
						<textarea name="comments" id="comments" class="form-control" rows="3"><?php echo $rowEdit['comments']; ?></textarea><br/>
					<label for="location">Location:</label>
					<input required name="condlocation" id="condlocation" class="form-control" <?php echo 'value="'.$rowEdit['condlocation'].'"'; ?> placeholder="Enter Location"><br/>
					<label for="notified">Notified:</label>
					<input required name="notified" id="notified" class="form-control" <?php echo 'value="'.$rowEdit['notified'].'"'; ?> placeholder="Enter Who Was Notified" /><br/>
					<label for="position">Position:</label>
					<select name="position" id="priorityconditions2" class="form-control">
						<?php
							$sql2 = "SELECT id, title FROM position ORDER BY id";
							$rs2 = mysql_query($sql2);
							while($row2 = mysql_fetch_array($rs2)) {
								echo '<option value="'.$row2['title'].'"';
								if ($rowEdit['position'] == $row2['title']) echo 'selected="selected"';
								echo '/>'.$row2['title'].'</option> \n';
							} ?>
					</select><br/>
					<!-- CHECK TO SEE IF AA OR SA LOGIN FOR IMAGE -->
						<?php
							if (($discipline == "AA") || ($discipline == "SA")) { ?>
								<label for='image'>Image:</label>
								<div class='input-group'>
									<input type='hidden' name='MAX_FILE_SIZE' value='20000000'>
									<input type='hidden' name='storedfile' id='storedfile' <?php echo 'value="'.$rowEdit['image'].'"'; ?>>	
									<span class='input-group-btn'>
									<span class='btn btn-primary btn-file'> Browse
									<input type='file' name='userfile' id='userfile'></span></span>
									<input type='text' class='form-control' readonly>
								</div>
								<img src='<?php echo $rowEdit['image']; ?>' width='150' height='150'><br/><br/>
						<?php	} ?>
					<?php
						if (isset($_POST['update'])) {
							echo '<input name="idEdit" id="idEdit" value="'.$_POST['update'].'" type="hidden">
										<button name="pcedit" type="submit" style="background:#1563a3;border-radius:5px;padding:3px 10px;border:0;color:#fff;font-size:22px;">Update</button>
										<form action="report.php">
											<button name="cancel" type="submit" style="background:red;border-radius:5px;padding:3px 10px;border:0;color:#fff;font-size:22px;">Cancel</button>
										</form>';
						}
						else {
							echo '<button name="pcsubmit" type="submit" style="background:#1563a3;border-radius:5px;padding:3px 10px;border:0;color:#fff;font-size:22px;">Submit</button>'; } ?>
					<input type="hidden" name="formtype" value="PC">
					<input type="hidden" name="formstatus" value="Not Sent">
				</form>
			</div> <!-- contentwrapper -->
		</div> <!-- container -->
	</body>
</html>