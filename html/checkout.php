<?php

// This file is the first step in the checkout process.
// It takes and validates the shipping information.

// Require the configuration before any PHP code:
require ('./includes/config.inc.php');

// Check for the user's session ID, to retrieve the cart contents:
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	if (isset($_GET['session'])) {
		$uid = $_GET['session'];
		// Use the existing user ID:
		session_id($uid);
		session_start();
	} else { // Redirect the user.
		$location = 'http://' . BASE_URL . 'cart.php';
		header("Location: $location");
		exit();
	}
} else { // POST request
	session_start();
	$uid = session_id();
}

// Create a session for the checkout process...

// Require the database connection:
require ('./../connection.php');
$conn = dbConnect('exec');

// Create array for storing billing errors
$shipping_errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	if (preg_match('/^[A-Z \'.-]{2,20}$/i', $_POST['first_name'])) {
		$fn = addslashes($_POST['first_name']);
	} else {
		$shipping_errors['first_name'] = 'Please enter your first name!';
	}

	if (preg_match ('/^[A-Z \'.-]{2,40}$/i', $_POST['last_name'])) {
		$ln = addslashes($_POST['last_name']);
	} else {
		$shipping_errors['last_name'] = 'Please enter your last name!';
	}

	if (preg_match ('/^[A-Z0-9 \',.#-]{2,80}$/i', $_POST['address1'])) {
		$a1 = addslashes($_POST['address1']);
	} else {
		$shipping_errors['address1'] = 'Please enter your street address!';
	}

	if (empty($_POST['address2'])) {
		$a2 = NULL;
	} elseif (preg_match ('/^[A-Z0-9 \',.#-]{2,80}$/i', $_POST['address2'])) {
		$a2 = addslashes($_POST['address2']);
	} else {
		$shipping_errors['address2'] = 'Please enter your street address!';
	}

	if (preg_match ('/^[A-Z \'.-]{2,60}$/i', $_POST['city'])) {
		$c = addslashes($_POST['city']);
	} else {
		$shipping_errors['city'] = 'Please enter your city!';
	}

	if (preg_match ('/^[A-Z]{2}$/', $_POST['region'])) {
		$s = $_POST['region'];
	} else {
		$shipping_errors['region'] = 'Please enter your regiom!';
	}

	if (preg_match ('/^(\d{5}$)|(^\d{5}-\d{4})$/', $_POST['zip'])) {
		$z = $_POST['zip'];
	} else {
		$shipping_errors['zip'] = 'Please enter your 5  or 9-digit zip code!';
	}

	$phone = str_replace(array(' ', '-', '(', ')'), '', $_POST['phone']);
	if (preg_match ('/^[0-9]{10}$/', $phone)) {
		$p = $phone;
	} else {
		$shipping_errors['phone'] = 'Please enter your phone number!';
	}

	if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
		$e = $_POST['email'];
		$_SESSION['email'] = $_POST['email'];
	} else {
		$shipping_errors['email'] = 'Please enter a valid email address!';
	}

	if (isset($_POST['use']) && $_POST['use'] == 'Y') {
		$_SESSION['shipping_for_billing'] = true;
		$_SESSION['cc_first_name'] = $_POST['first_name'];
		$_SESSION['cc_last_name'] = $_POST['last_name'];
		$_SESSION['cc_address'] = $_POST['address1'] . ' ' . $_POST['address2'];
		$_SESSION['cc_city'] = $_POST['city'];
		$_SESSION['cc_state'] = $_POST['region'];
		$_SESSION['cc_zip'] = $_POST['zip'];
	}

	if (empty($shipping_errors)) { // If everything's OK...
		
		// Add the user to the database...
		$result = mysqli_query($conn, "CALL add_customer('$e', '$fn', '$ln', '$a1', '$a2', '$c', '$s', $z, $p, @cid)");

		if ($result) { // If it was successful
			// Retrieve the customer ID:
			$result = mysqli_query($conn, 'SELECT @cid');
			if (mysqli_num_rows($result) == 1) {
			list($_SESSION['customer_id']) = mysqli_fetch_array($result);

			$location = 'https://' . BASE_URL . 'billing.php';
			header("Location: $location");
			exit( );
			}
		}

		// Log the error, send an email, panic!
		trigger_error('Your order could not be processed due to a system error.
			We apologize for the inconvenience.');

	} // Errors occurred IF.

} // End of REQUEST_METHOD IF.

// Include the header file:
$page_title = 'Coffee - Checkout - Your Shipping Information';
include ('./includes/checkout_header.html');

// Get the cart contents:
$result = mysqli_query($conn, "CALL get_shopping_cart_contents('$uid')");

if (mysqli_num_rows($result) > 0) { // Products to show!
	include ('./views/checkout.html');
} else { // Empty cart!
	include ('./views/emptycart.html');
}

// Finish the page:
include ('./includes/footer.html');