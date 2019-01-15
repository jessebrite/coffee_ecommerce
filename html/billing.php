<?php
// This file is the second step in the checkout process.
// It takes and validates the billing information.
require ('./includes/config.inc.php');

//Begin the session and retrieve the Session ID
session_start();
$uid = session_id(); // user's cart ID:

// Redirect invalid users
if (!isset($_SESSION['customer_id'])) {
	$location = 'https://' . BASE_URL . 'checkout.php';
	header("location: $location");
	exit();
}

// Require the database connection:
require ('./../connection.php');
$conn = dbConnect('exec');

// Create array for storing billing errors
$billing_errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (get_magic_quotes_gpc()) {
		$_POST['cc_first_name'] = stripslashes($_POST['cc_first_name']);
	}
	if (get_magic_quotes_gpc()) {
		$_POST['cc_last_name'] = stripslashes($_POST['cc_last_name']);
	}
	// Check for first name
	if (preg_match ('/^[A-Z \'.-]{2,20}$/i', $_POST['cc_first_name'])) {
		$cc_first_name = $_POST['cc_first_name'];
	} else {
		$billing_errors['cc_first_name'] = 'Please enter your first name!';
	}

	// Check for last name
if (preg_match ('/^[A-Z \'.-]{2,40}$/i', $_POST['cc_last_name'])) {
		$cc_last_name = $_POST['cc_last_name'];
	} else {
		$billing_errors['cc_last_name'] = 'Please enter your last name!';
	}

	// Check for a valid credit card number...
	// Strip out spaces or hyphens:
	$cc_number = str_replace(array(' ', '-'), '', $_POST['cc_number']);

	// Validate the card number against allowed types:
	if (!preg_match ('/^4[0-9]{12}(?:[0-9]{3})?$/', $cc_number) // Visa
	&& !preg_match ('/^5[1-5][0-9]{14}$/', $cc_number) // MasterCard
	&& !preg_match ('/^3[47][0-9]{13}$/', $cc_number) // American Express
	&& !preg_match ('/^6(?:011|5[0-9]{2})[0-9]{12}$/', $cc_number) // Discover
	) {
		$billing_errors['cc_number'] = 'Please enter a valid credit card number!';
	}

	// Check for an expiration date:
	if ( ($_POST['cc_exp_month'] < 1 || $_POST['cc_exp_month'] > 12)) {
		$billing_errors['cc_exp_month'] = 'Please enter your expiration month!';	
	}

	// Check for expiration year
	if ($_POST['cc_exp_year'] < date('Y')) {
		$billing_errors['cc_exp_year'] = 'Please enter your expiration year!';
	}
	
	// Check for a CVV:
	if (preg_match ('/^[0-9]{3,4}$/', $_POST['cc_cvv'])) {
		$cc_cvv = $_POST['cc_cvv'];
	} else {
		$billing_errors['cc_cvv'] = 'Please enter your 3 or 4-digit CVV!';
	}
	// Check for a street address:
	if (preg_match ('/^[A-Z0-9 \',.#-]{2,160}$/i', $_POST['cc_address'])) {
		$cc_address  = $_POST['cc_address'];
	} else {
		$billing_errors['cc_address'] = 'Please enter your street address!';
	}
		
	// Check for a city:
	if (preg_match ('/^[A-Z \'.-]{2,60}$/i', $_POST['cc_city'])) {
		$cc_city = $_POST['cc_city'];
	} else {
		$billing_errors['cc_city'] = 'Please enter your city!';
	}
	
	// Check for a state:
	if (preg_match ('/^[A-Z]{2}$/', $_POST['cc_region'])) {
		$cc_state = $_POST['cc_region'];
	} else {
		$billing_errors['cc_region'] = 'Please enter your state!';
	}
	
	// Check for a zip code:
	if (preg_match ('/^(\d{5}$)|(^\d{5}-\d{4})$/', $_POST['cc_zip'])) {
		$cc_zip = $_POST['cc_zip'];
	} else {
		$billing_errors['cc_zip'] = 'Please enter your 4 or 5-digit zip code!';
	}

	if (empty($billing_errors)) { // If everything's OK...

		// Convert the expiration date to the right format:
		$cc_exp = sprintf('%02d%d', $_POST['cc_exp_month'], $_POST['cc_exp_year']);
		
		// Check for an existing order ID:
		if (isset($_SESSION['order_id'])) { // Use existing order info:
			$order_id = $_SESSION['order_id'];
			$order_total = $_SESSION['order_total'];
		} else { // Create a new order record:
			
			// Get the last four digits of the credit card number:
			$cc_last_four = substr($cc_number, -4);

			// Call the stored procedure:
			$result = mysqli_query($conn, "CALL add_order({$_SESSION['customer_id']}, '$uid', {$_SESSION['shipping']}, $cc_last_four, @total, @oid)");
			// Confirm that it worked:

			if ($result) {

				// Retrieve the order ID and total:
				$result = mysqli_query($conn, 'SELECT @total, @oid');
				if (mysqli_num_rows($result) == 1) {
					list($order_total, $order_id) = mysqli_fetch_array($result);
					
					// Store the information in the session:
					$_SESSION['order_total'] = $order_total;
					$_SESSION['order_id'] 	 = $order_id;

				} else { // Could not retrieve the order ID and total.
					unset($cc_number, $cc_cvv);
					trigger_error('Your order could not be processed due to a system error. We apologize for the inconvenience.');
				}

			} else { // The add_order() procedure failed.
				unset($cc_number, $cc_cvv);
				trigger_error('Your order could not be processed due to a system error. We apologize for the inconvenience.');
			}
			
		} // End of isset($_SESSION['order_id']) IF-ELSE.

	}

}

// Include the header file
include ('./includes/checkout_header.html');

// Get thte shopping cart contents
$result = mysqli_query($conn, "CALL get_shopping_cart_contents('$uid')");

// Include the view files
if (mysqli_num_rows($result) > 0) {
	if (isset($_SESSION['shipping_for_billing']) && $_SERVER['REQUEST_METHOD'] != 'POST') {
		$values = 'SESSION';
	} else { // POST Method
		$values = 'POST';
	}
	//Include billing.hrml script
	include ('./views/billing.html');

} else { // Empty cart!
	include ('./views/emptycart.html');
} // End IF-ELSE block

// Include the footer
include ('./includes/footer.html');
