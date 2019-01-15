<?php
// Require the config file
require ('./includes/config.inc.php');

// Validate the required values
$type = $sp_type = $sp_cat = $category = false;
if (isset($_GET['type'], $_GET['category'], $_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT, array('min_range' => 1))) {
	$category = $_GET['category'];
	$sp_cat = $_GET['id'];

	// Validate the product type
	if ($_GET['type'] == 'goodies') {
		$sp_type = 'other';
		$type 	 = 'goodies'; 
	} elseif ($_GET['type'] == 'coffee') {
		$type = $sp_type = 'coffee';
	}
}

// If there is a problem, display the error page
if (!$type || !$sp_type || !$sp_cat || !$category){
	$page_title = 'Error!';
	include ('./includes/header.html');
	include ('./views/error.html');
	include ('./includes/footer.html');
	exit();
}
$page_title = ucfirst($type) . ' to Buy::' . $category;
include ('./includes/header.html');

// Require the Database connection
require ('./../connection.php');
$conn = dbConnect('exec');
$result = $conn->query("CALL select_products('$sp_type', $sp_cat)");

// For debugging purpses only
// Will be removed once we go live
if (!$result) echo $conn->error;

// If results were returned, include the view file
if ($result->num_rows > 0) {
	if ($type == 'goodies') {
		include ('./views/list_products.html');
	} elseif ($type == 'coffee') {
		include ('./views/list_coffees.html');
	} else {
		include ('./views/noproducts.html');
	}
}
// Include the footer
include ('includes/footer.html');
?>