<?php
  //Check & set inspector's name
  if (isset($_COOKIE['name'])) {
    $inspname = $_COOKIE['name'];
  }
  else {
    $inspname = $name;
  }
  if (isset($_COOKIE['disc'])) {
    $discipline=$_COOKIE['disc'];
  }
  //Delete Priority Condition
  if (isset($_GET['deletePC'])) {
    $sql = "SELECT DISTINCT image FROM inspforms WHERE id='{$_GET['deletePC']}'";
    $rs = mysql_query($sql);
    while($row = mysql_fetch_array($rs)) {
      //Gives permission to delete
      chmod($row['image'], 0777);
      //Deleted previous file
      unlink($row['image']);
    }
    $query = mysql_query("DELETE FROM inspforms WHERE id='{$_GET['deletePC']}'")or die('Error : ' . mysql_error());
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
  }
  //New/Edit PC Submit
  if (isset($_POST['pcsubmit']) || isset($_POST['pcedit'])) {
    function escape_data ($data) {
      global $dbc;
      if (ini_get('magic_quotes_gpc')) {
        $data = stripslashes($data);
      }
      return mysql_real_escape_string (trim($data), $dbc);
    }
    $errors = array();
    // Check for asset ID.
    $assetid = escape_data($_POST['assetid']);
    //Check if asset begins with either 'K' or 'Q'
    if (substr($assetid, 0, 1) === "K" || substr($assetid, 0, 1) === "Q" || substr($assetid, 0, 1) === "k" || substr($assetid, 0, 1) === "q") {
      //Check to see if last 3 characters are numbers
      if ((is_numeric (substr($assetid, 1)))) {
        //Capitalize Asset ID
        $assetid = strtoupper($assetid);
      }
      else {
        $errors[] = "Incorrect Asset ID. Please make sure the ID number is correct.";
      }
    }
    else {
      $errors[] = "Incorrect Asset ID. Please make sure the ID begins with either a 'K' or 'Q'.";
    }
    // Check for instance.
    if (empty($_POST['instance'])) {
      $errors[] = 'You forgot to select the instance number.';
    }
    elseif (isset($_POST['pcsubmit'])) {
      $instance = escape_data($_POST['instance']);
      $sql="SELECT inspectorname, disc, assetid, instance FROM inspforms LEFT JOIN inspectors ON inspforms.inspectorname=inspectors.name WHERE assetid='".$assetid."' AND instance=".$instance." AND disc='".$discipline."' AND formstatus!='Discard'";
      $rs=mysql_query($sql);
      $result=mysql_fetch_array($rs);
      if($result) { //Check if instance # has already been submitted
        $errors[]="The instance number ".$instance." has already been used for ".$assetid. " ".$discipline;
      }
    }
		else {
			$instance = escape_data($_POST['instance']);
		}
    // Check for PO status.
    if (empty($_POST['po18addit'])) {
      $errors[] = 'You forgot to enter the PO Status.';
    } else {
      $postatus = escape_data($_POST['po18addit']);
    }	
    // Check for number.
    $ponumber = escape_data($_POST['filed_num']);
    // Check for repeatPC.
    if (empty($_POST['repeatPC'])) {
      $repeatPC=0;
    }
    else {
      $repeatPC = escape_data($_POST['repeatPC']);
    }	
    // Check for notified.
    if (empty($_POST['notified'])) {
      $errors[] = 'You forgot to enter who was notified.';
    } else {
      $notified = escape_data($_POST['notified']);
    }
    // Check for position.
    if (empty($_POST['position'])) {
      $errors[] = 'You forgot to enter the position of who was notified.';
    }
    else {
      $position = escape_data($_POST['position']);
    }
    // Check for cond location.
    if (empty($_POST['condlocation'])) {
      $errors[] = 'You forgot to enter the condition location.';
    }
    else {
      $condlocation = escape_data($_POST['condlocation']);
    }
    // Check for priority type
    if (empty($_POST['priorityconditions'])) {
      $errors[] = 'You forgot to select a Priority Type.';
    }
    else {
      $priorityconditions = escape_data($_POST['priorityconditions']);
    }
    // Check for component affected
    if (empty($_POST['priorityconditions2'])) {
      $errors[] = 'You forgot to select a Component Affected.';
    }
    else {
      $priority_two = escape_data($_POST['priorityconditions2']);
      if ($priority_two=="Other (Text)") {
        $priority_two = escape_data($_POST['othercomponent']);
        if ($priority_two=="") $errors[]= "You forgot to enter Other Text for Component Affected.";
      }
    }
    // Check for PC id
	  $idEdit = escape_data($_POST['idEdit']);
    // Check for inspection date.
    $date = escape_data($_POST['datepick']);
    // Sperates the month, day & year
    list($y, $m, $d) = explode('-', $date);
    // Checks to see if the date entered is correct
    if (!checkdate($m, $d, $y)) {
      $errors[] = "You've entered an incorrect date. Please make sure the format is: <b>YYYY-MM-DD</b>.";
    }
    // Check for image.
    $tmpName  = $_FILES['userfile']['tmp_name'];
    $fileSize = $_FILES['userfile']['size'];
    $fileType = $_FILES['userfile']['type'];
    // Stores image name into a variable
    $content = $_FILES['userfile']['name'];
    $content = addslashes($content);
    //Check if image was uploaded
    if (!empty($content)) {
      // Directory where image will be stored
      if (isset($discipline)) {
        $target = "images/disc/".$discipline."/".$assetid."/".$instance.$content;
        $path = "images/disc/".$discipline."/".$assetid;
      }
      else {
        $target = "images/disc/".$_COOKIE['disc']."/".$assetid."/".$instance.$content; 
        $path = "images/disc/".$_COOKIE['disc']."/".$assetid;
      }
      // Check to see if directory already exists
      $exist = is_dir($path);
      // If directory doesn't exist, create directory
      if (!$exist) {
        mkdir("$path");
        chmod("$path", 0755);
      }
      //Writes the photo to the server  
      if (move_uploaded_file($tmpName, $target) && ($fileSize < 20000000) && (file_exists($target))) {   
      }  
      else {   
        //Gives and error if its not  
        $errors[] = "Sorry, there was a problem uploading your file.";
      }
    }
    echo "<div class='wrapper'>";
    if (empty($errors)) {
      $podate = escape_data($_POST['filed_date']);
      $formtype = $_POST['formtype'];
      $comments = $_POST['comments'];
      $formstatus = $_POST['formstatus'];
      // Make query or update
      if (isset($_POST['pcsubmit'])) {
        $query = "INSERT INTO inspforms (inspectorname, ponumber, condlocation, description, notified, position, postatus, assetid, image, podate, inspectiondate, formtype, instance, comments, formstatus, repeatPC, affected) 
                VALUES ('$inspname', '$ponumber', '$condlocation', '$priorityconditions', '$notified', '$position', '$postatus', '$assetid', '$target', '$podate', '$date', '$formtype', $instance, '$comments', '$formstatus', '$repeatPC', '$priority_two')";
        $msg = 'Thank you for submitting the Priority Condition Form!';
      }
      elseif (isset($_POST['pcedit'])) {
        $query = "UPDATE inspforms SET inspectorname='$inspname', ponumber='$ponumber', condlocation='$condlocation', description='$priorityconditions', notified='$notified', position='$position', postatus='$postatus', assetid='$assetid', podate='$podate', inspectiondate='$date', instance=$instance, comments='$comments', repeatPC='$repeatPC', affected='$priority_two' WHERE id='".$idEdit."'";
        $msg = 'Thank you for updating your Priority Condition Form!';
      }
      $result = @mysql_query ($query); //Run the query.
      if ($result) { // If it ran OK.
        echo "<label for='thankyou'>".$msg."</label>
        Please contact your discipline lead with any questions<br />";
      }
      else {
        echo '<h1 id="mainhead">System Error</h1>
        <p class="error">Report was not submitted due to a system error. We apologize for any inconvenience.</p>'; // Public message.
        echo '<p>' . mysql_error() . '<br /><br />Query: ' . $query . '</p>'; // Debugging message.
        exit();
      }
    }
    else { // Report the errors.
      echo '<h3 id="mainhead">Error!</h3>
      <p class="error">The following error(s) occurred:<br />';
      foreach ($errors as $msg) { // Print each error.
        echo " - $msg<br />\n";
      }
      echo '</p><p>Please try again.</p>';
    } // End of if (empty($errors)) IF.
    echo "</div><hr>";
  }
?>