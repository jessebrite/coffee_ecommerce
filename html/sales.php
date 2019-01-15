<?php
// This file is the sales page. 
// It lists every sales item.

// Require the configuration before any PHP code:
require ('./includes/config.inc.php');

// Set the page title
$page_title = 'Sale Items';

// Include the header file
include ('./includes/header.html');

// Require the database connection:
require ('./../connection.php');
$conn = dbConnect('exec');

// Invoke the stored procedure
$result = $conn->query('CALL select_sale_items(true)');
if ($result->num_rows > 0) {
	include ('./views/list_sales.html');
} else {
	include ('./views/nonproducts.html');
}
// Include the footer
include ('./includes/footer.html');