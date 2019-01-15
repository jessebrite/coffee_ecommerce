<?php
require ('includes/config.inc.php');
if (isset($_GET['type']) && $_GET['type'] == 'goodies') {
	$page_title = 'Our Goodies, by Category';
	$sp_type = 'other';
	$type = 'goodies';
} else { // Default is coffee
	$page_title = 'Our Coffee Products';
	$type = $sp_type = 'coffee';
}

// Include the header file
include ('./includes/header.html');

// Require the Database connection
require ('./../connection.php');

// Invoke the stored procedure
$conn = dbConnect('exec');
$result = $conn->query("CALL select_categories('$sp_type')");
// Print any error that occurs
// Will be removed once we go live
if (!$result) echo $conn->error;

//If records were returned, include the view file
if ($result->num_rows > 0) {
	include ('./views/list_categories.html');
} else { // Include the error page
	include ('./views/error.html');
}

// Include the view file
include ('./includes/footer.html');
?>