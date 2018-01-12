<?php
	// environment variables the operator needs to set
	// keep your values within quotes
	$serverAddress = "IP address or host name";
	$serverPort = "1433";
		// 1433 is the default MSSQL port
	$databaseName = "Name of DB to query";
	$databaseUser = "Username for DB connection";
	$databasePass = "Password for DB connection";
	
	$sqlSelect = "*";
	$sqlFrom = "$databaseName.tableName";
		// you can use the $databaseName variable here if you want
	$sqlWhere = "columnName";
		// these three sql variables define the SQL query that will be performed
		// they will be used like so:
			// SELECT $sqlSelect FROM $sqlFrom WHERE $sqlWhere = SearchTermFromUser
			// as an example: SELECT * FROM msdb.dbo.Staff WHERE FirstName = John

	// uncomment the $binVarsSet flag to TRUE once you have provided your environment variables above
	$binVarsSet = FALSE;

	// THAT'S EVERYTHING - NO NEED TO EDIT FURTHER

		// initialise empty $lookup var
		$lookup = "";
		
		// integrity check $lookup data
		function test_input($data) {
			$data = trim($data);
			$data = stripslashes($data);
			$data = htmlspecialchars($data);
			return $data;
		}
	
		function OpenConnection($serverAddress, $serverPort, $databaseName, $databaseUser, $databasePass)  
		{  
			try  
			{  
				$serverName = "tcp:$serverAddress,$serverPort";  
				$connectionOptions = array("Database"=>"$databaseName",  
					"Uid"=>"$databaseUser", "PWD"=>"$databasePass"); 
				$conn = sqlsrv_connect($serverName, $connectionOptions);  
				if($conn == false)  {
					echo "Database connection failed.<br>";
					echo FormatErrors(sqlsrv_errors());  
					return "error";
				} else {
					return $conn;
				}
			}  
			catch(Exception $e)  
			{  
				echo("Error!");  
			}
		}

	function ReadData($searchTerm, $serverAddress, $serverPort, $databaseName, $databaseUser, $databasePass, $sqlSelect, $sqlFrom, $sqlWhere)  
		{  
			try  
			{  
				// to allow multiple items to be searched at once, we replace the <space> character in $searchTerm with the appropriate SQL delimiter.
				//	that is, replace <space> with apostrophe-comma-<space>, eg. "', "
				
				$arrSearchTerm = array();
				$arrSearchTerm = explode(" ", $searchTerm);
				
				$arrResults = array();
				$arrRows = array();
				$intResults = 0;
				$a = 0;
				$y = 0;
				
				$arrNoResults = array();
				
				$getResults = array();
				
				$binTableHeadersDone = FALSE;
				$binNonResults = FALSE;
				
				// for each term in the search string, we do a search of the db
				// this was done as a dirty solution for identifying which search terms return no result - there's probably a better way...
				for ($a = 0; $a < count($arrSearchTerm); $a++) 
				{
					$conn = OpenConnection($serverAddress, $serverPort, $databaseName, $databaseUser, $databasePass);  
					if ($conn === "error")
						return;
				
					$tsql = "SELECT $sqlSelect FROM $sqlFrom WHERE $sqlWhere = '$arrSearchTerm[$a]'";
					
					$getResults[$a] = sqlsrv_query($conn, $tsql);  					

					// check if the query is valid
					// if not, die (exit) and print the error
					if ($getResults[$a] == FALSE)  
					{
						echo "SQL query lookup returned an error.<br>";
						echo "SQL query: " . $tsql . "<br>";
						echo FormatErrors(sqlsrv_errors());  
						return;
					}				
					
					// check if this is a 'non result', if so add it to arrNoResults, else read it into arrResults
					if (!sqlsrv_has_rows($getResults[$a])) 
					{
						$arrNoResults[] = $arrSearchTerm[$a];
					}	else {
						$arrResults[$intResults] = sqlsrv_fetch_array($getResults[$a], SQLSRV_FETCH_ASSOC);
						$intResults++;
					}
					
					// free up and close the sqlsrv query
					sqlsrv_free_stmt($getResults[$a]);  
					sqlsrv_close($conn);  
				}
				// uncomment one or both of the lines below to print the contents of the results array, for debug use
				//var_dump($arrResults[$intResults]);
				//print_r($arrResults);

				// note items for which nothing was found, if there were any
				if (count($arrNoResults) != 0)
					echo "No results found for: " . implode(", ", $arrNoResults) . ".<br>";

				// if there aren't any actual results, stop
				if (count($arrNoResults) == count($arrSearchTerm)) {
					{}
					echo "No results. <br>";
					return;
				}
				
					// start building the html table head using the keys of the first array's key-pairs
					if ($binTableHeadersDone != TRUE) 
					{						
						echo "<table>";
							echo "<tr>";
							
						foreach ($arrResults[0] as $key => $value)
						{
								echo "<th>" . $key . "</th>";
						}
						
							echo "</tr>";
						$binTableHeadersDone = TRUE;
					}
					
					// print out the table content by echoing the values of each key-value pair
					for ($i = 0; $i < $intResults; $i++)
					{  
						echo "<tr>";
						foreach ($arrResults[$i] as $key => $value)
						{
							echo "<td>" . $value . "</td>";
						}
						echo "</tr>";
					}
					
				// done listing arrResults, close off the html table
				echo "</table>";
			
			}
			// something for catching exception errors, I don't know
			catch(Exception $e)  
			{  
				echo("Error!");  
			}

		}
		
	function FormatErrors( $errors )
		{
			/* Display errors. */
			echo "Error information: <br>"; 

			foreach ( $errors as $error )
			{
				echo "SQLSTATE: ".$error['SQLSTATE']."<br>";
				echo "Code: ".$error['code']."<br>";
				echo "Message: ".$error['message']."<br>";
			}
		}	
		
	// check the $binVarsSet flag - if it is false, print a message indicating this
	if (!$binVarsSet) 
	{
		echo "<p>Environment variables are not set! This site will do nothing until those are configured. Check the searchCode.php file. </p>";
	}
	
	// if some input is provided to the form, receive it and pass on for integrity check
	// set $lookup to equal the result of the integrity check
	if ($_SERVER["REQUEST_METHOD"] == "POST" && $binVarsSet) {
		$lookup = test_input($_POST["searchTerm"]);
	}
	// continues...
	// now that $lookup has a value which has been checked, pass it on for the query
	if ($lookup != "")
		ReadData($lookup, $serverAddress, $serverPort, $databaseName, $databaseUser, $databasePass, $sqlSelect, $sqlFrom, $sqlWhere);	
	
	?>