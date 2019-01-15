<?php
// Include the config file before any other thing
require ('./includes/config.inc.php');

// Set the page title
$page_title = 'Wish List';

// Check for, or create a user session ID
if (isset($_COOKIE['SESSION'])) {
	$uid = $_COOKIE['SESSION'];
} else {
	$uid = md5(uniqid('biped', true));
}
// Send the cookie
setcookie('SESSION', $uid, time()+(60*60*24*30));

// Include the header file
include ('./includes/header.html');

// Require the database connection:
require ('./../connection.php');
$conn = dbConnect('exec');

// Include the product functions
include ('./includes/product_functions.inc.php');

if (isset($_GET['sku'])) {
	list($sp_type, $pid) = parse_sku($_GET['sku']);
}

if (isset($sp_type, $pid, $_GET['action']) && ($_GET['action']) == 'remove') {
	$result = $conn->query("CALL remove_from_wish_list('$uid', '$sp_type', $pid)");
	if (!$result) echo $conn->error; // for debugging purposes

} elseif (isset($sp_type, $pid, $_GET['action'], $_GET['qty']) && ($_GET['action'] == 'move')) { // Move it to the cart
	// Determine the quantity
	$qty = (filter_var($_GET['qty'], FILTER_VALIDATE_INT, array('min_range' => 1))) ? $_GET['qty'] : 1;

	// Add it to the cart
	$result = $conn->query("CALL add_to_wish_list('$uid', '$sp_type', $pid, $qty)");
	if (!$result) echo $conn->error; // for debugging purposes

	// Remove it from the wish list:
	$result = $conn->query("CALL remove_from_cart('$uid', '$sp_type', $pid)");

} elseif (isset($_POST['quantity'])) {
	// Loop through each item
	foreach ($_POST['quantity'] as $sku => $qty) {

		// Parse the SKU
		list($sp_type, $pid) = parse_sku($sku);
		if (isset($sp_type, $pid)) {

			// Determine the quantity
			$qty = (filter_var($qty, FILTER_VALIDATE_INT, array('min_range' => 0)) !== false) ? $qty : 1;

			// Update the quantity in the cart
			$result = $conn->query("CALL update_wish_list('$uid', '$sp_type', $pid, $qty)");
			if (!$result) echo $conn->error; // for debugging purposes
		}
	} // End of FOREACH loop
} // End of main IF

$result = $conn->query("CALL get_wish_list_contents('$uid')");

if ($result->num_rows > 0) {
	include ('./views/wishlist.html');
} else { // Empty catr!
	include ('./views/emptycart.html');
}
// Include the footer file
include ('./includes/footer.html');