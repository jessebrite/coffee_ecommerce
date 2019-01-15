<?php

function create_form_input($name, $type, $errors, $values = 'POST', $extras = '') {
	// Initialiae the value
	$value = false;

	if ($values == 'SESSION') {
		if (isset($_SESSION[$name])) $value = $_SESSION[$name];
	} elseif ($value == 'POST') {
		if (isset($_POST[$name])) $value = $_POST[$name];
		if ($value && get_magic_quotes_gpc()) $value = stripcslashes($value);
	}

	if ($type == 'text' || $type == 'password') {
		echo '<input type="' . $type . '" name="' . $name . '" id="' . $name . '"';
		if ($value) echo ' value="' . htmlentities($value) . '"';
		if (!empty($extras)) echo "$extras";
		if (array_key_exists($name, $errors)) {
			echo 'class="error"><br><span class ="error">' . $errors[$name] . '<span>';
		} else {
			echo '>';
		}
	} elseif ($type == 'select') { // select menu
		
		if (($name == 'region') || ($name == 'cc_region')) { // Create a list of regions.
			
			$data = array('AS' => 'Ashanti Region', 'BA' => 'Brong Ahafo', 'ER' => 'Eastern Region', 'GA' => 'Greater Accra', 'NR' => 'Northern Region', 'UE' => 'Upper East', 'UW' => 'Upper West', 'VR' => 'Volta Region', 'WR' => 'Western Region');
			
		} elseif ($name == 'cc_exp_month') { // Create a list of months.

			$data = array(1 => 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August',  'September', 'October', 'November', 'December');
			
		} elseif ($name == 'cc_exp_year') { // Create a list of years.
			$data = array();
			$start = date('Y');
			for ($i = $start; $i <= $start + 5; $i++) { 
				$data[$i] = $i;
			} // End of FOE loop	

	} // End of $name IF-ELSEIF

	// Open the <SELECT> tag
	echo '<select name="' . $name . '"';

	// If error exists, add error class
	if (array_key_exists($name, $errors)) echo 'class="error"';

		// Close tag
	 	echo '>';

		 // create each option
	 	foreach ($data as $key => $value) {
		 	echo "<option value=\"$key\"";

		 	// Select the existing values
		 	if ($value == $key) echo 'selected="selected"';

		 	echo ">$value</option>\n";

		 } // End FOREACH

		 // Complete the <SELECT> tag
		 echo '</select>';

		 // Add an error mesage if one exists
		 if (array_key_exists($name, $errors)) {
		 	echo '<br><span class="error">' . $errors[$name] . '</span>';
		}

	} // End of primary IF-ELSE

} // End of create_form_input() fucntion