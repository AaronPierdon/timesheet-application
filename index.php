<!doctype html>
<html lang="en">
<head>
	<link rel="stylesheet" type="text/css" href="stylesheets/main.css"/>
	<meta charset="utf-8">
	<title>results</title>
</head>

<body>



<?php



	
		// Connect to the database
	$conn = pg_connect("host=ec2-23-23-247-245.compute-1.amazonaws.com
		dbname=dckjboam20j6q8 user=ovagovpkdjiuze password=9abdf1301274c05c95f145c8571b24520346fb6b05bcb2e50e489e46b9b66256 ");
	
	// making sure rate has a value
	$rate;
	
	
	
	// Make sure there is a connection before continuing
	if($conn){
		
		// Check to see if the table needs to be dropped
		if((isset($_POST['dropTable'])) && (!empty($_POST['dropTable']))){
			pg_query($conn, "DROP TABLE timesheet");
			$_POST['dropTable'] = NULL;

		} 
		
		// Check to see if we need to input a previously added line item
		if((isset($_POST['addLineItem'])) && (!empty($_POST['addLineItem']))){
			addLineItem($_POST['rownumber'], $_POST['linedate'], $_POST['lineduration'], $conn);
			$_POST['addLineItem'] = NULL;

		} 
		
		// Check to see if there is a rate
		if((isset($_POST['setRate'])) && (!empty($_POST['setRate']))){
			$rate = $_POST['rate'];
			setcookie("ratecookie", $rate, time()+360000);

		} else if ((isset($_COOKIE['ratecookie'])) && (!empty($_COOKIE['ratecookie']))){
			$rate = $_COOKIE['ratecookie'];
		} else {
			$rate = 0.0;
		}
		
		$description = "Enter Description Here";
		// Set desc.
		if((isset($_POST['setDescription'])) && (!empty($_POST['setDescription']))){
			$description = $_POST['setDescription'];
			setcookie("description", $description, time()+360000);
			
		}else if((isset($_COOKIE['description'])) && (!empty($_COOKIE['description']))){
			$description = $_COOKIE['description'];
		}
		
		$result;
		// query the rows of the timesheet table if it exists
		$result = pg_query($conn, "SELECT * FROM timesheet");
		// check for existing timesheet table
		if(!$result){
			// Test Content
			pg_query($conn, "CREATE TABLE timesheet(LineItemID INT, LineDate DATE, Duration INT)");
			
			// query the rows of the timesheet table
			$result = pg_query($conn, "SELECT * FROM timesheet");

		}
		

		
		
		// Get the # of rows in the timesheet table
		if((isset($result)) && (!empty($result))){
			$rowCount = pg_num_rows($result);
		} else {
			$rowCount = 0;
		}
		

		
		// This variable keeps track of the next free index available for insertion. Automatic incrementing to come later
		$insertIndex = 0;

		// If records exist, offset the insertIndex by 1 more than the number existing, otherwise, it remains at 0
		if($rowCount > 0){
			$insertIndex = $rowCount + 1;
		}

		
		// Get the data from the timesheet table
		$result = pg_query($conn, "SELECT * FROM timesheet"); 
		
		// Make sure there is data
		if(!$result){
			echo "An error occurred.\n";
		} else{
			if((isset($result)) && (!empty($result))){
				generateIndexContent($result, $conn, $rate, $description);
			} else {
				print 'error';
			}
			
			
		}
	
	} else {
		// Notify user that there is no database connection and the application cannot continue.
		print "Sorry, but we cannot connect to our databases at the moment. Please try again later.";
	}
	
	function generateIndexContent($inResults, $conn, $rate, $description){
			
			
					
				
				// Set up the table and the form 
				print '<form action="index.php" method="post" class="form">';
				generateTable($inResults, $conn, $description);
				
				// Set up the form for calculations so that the rate text field is visible and potentially pre-filled
				print '<form action="index.php" method="post" class="form">';
				
				generateCalculations($conn, $rate, $description);
				//close the form
				print '</form>';
			
			
			// Add a button for clearing the table
			print '<form action="index.php" method="post" class"form">';
			print '<p><input type="submit" name="dropTable" value="Clear Table" class="Button"></p>';
			print '</form>';
			
	
	}
	// com
	// Generates the table from the $result variable that has been loaded with SQL row data
	function generateTable($inResults, $conn, $description){

			//Finish setting up the table
			print '<table name="timesheet_table" style="text-align: CENTER;">
				<tr>
					<th>Line ID</th>
					<th>Date</th>
					<th>Duration</th>
				</tr>
				';
				
			while($row = pg_fetch_row($inResults)){
				print '<tr>' .
					'<td>' . $row[0] . '</p>' . '</td>' .
					'<td>' . $row[1] . '</td>' .
					'<td>' . $row[2] . '</td>';
				print '</td>';
			}
		
		print '</table>';
		print '<p>Line Date</p>';
		print '<p><input name="linedate" type="date"></p>';
		print '<p>Line Duration</p>';
		print '<p><input name="lineduration" type="number"></p>';
		print '<p><input  name="rownumber" type="hidden" value="' . htmlspecialchars(pg_num_rows($inResults)) . '"></p>';
		print '<p><input type="submit" name="addLineItem" value="Add Line Item" class="Button"></p>';
	
		
		
		print '</form>'; // end of table and adding line item form

		


	}
	
	
	function addLineItem($row, $theDate, $theDuration, $conn){
		// increment the row index so it's unique
		$row++;
		pg_query($conn, "INSERT INTO timesheet(LineItemID, LineDate, Duration) VALUES ('$row', '$theDate', '$theDuration')");
	}
	
	function generateCalculations($conn, $rate, $description){
		$results = pg_query($conn, "SELECT * FROM timesheet");
		$duration = 0;
		$cost = 0;
		while($row = pg_fetch_row($results)){
			$newDuration = $row[2];
			$duration += $newDuration;			
		}
		
		
		
		$cost = $duration * $rate;
		

		print '<p>Total Duration: '.$duration.' minutes</p>';
		print '<p>Total Cost: $'.$cost.'</p>';
		print '</br></br>';
		print '<p>Rate</p>';
		print '<p><input name="rate" type="number" value="'.$rate.'"></p>';
		print '<p>Description</p>';
		print '<p><input name="setDescription" type="text" value="' . htmlspecialchars($description) . '"></p>';
		print '<p><input type="submit" name="setRate" value="Set Rate and Description" class="Button"></p>';
		print '</form>';
		
	}
	
	
?>

</body>
</html>