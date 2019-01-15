<?php
function dbConnect($usertype, $connectionType = 'mysqli') {
	$host = 'localhost';
	$db = 'ecommerce2';
	if ($usertype == 'read') {
		$user = 'psread';
		$pwd = 'password';
	} elseif ($usertype == 'write') {
		$user = 'pswrite';
		$pwd = 'password';
	} elseif ($usertype == 'exec') {
		$user = 'psexec';
		$pwd = 'password';
	} else {
		exit('Unrecognized user');
	}
	if ($connectionType == 'mysqli') {
		$conn = @ new mysqli($host, $user, $pwd, $db);
		if ($conn->connect_error) {
			exit($conn->connect_error);
		}
		return $conn;
	} else {
		try {
			return new PDO("mysql:host=$host;dbname=$db", $user, $pwd);
		} catch (PDOException $e) {
                    $e->getMessage();
                }
	}
}