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
		if (isset($_POST['update'])){
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
			} else {
				if( log ) alert(log);
			}

		});
});