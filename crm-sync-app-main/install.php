<?php
//in the current moments this is not needed

// Set variables for our request
$shop = $_GET['shop'];
$api_key = "apiKey";
$scopes = "read_orders,write_products,read_customers";
$redirect_uri = "URL/generate_token.php";

// Build install/approval URL to redirect to
$install_url = "https://" . $shop . "/admin/oauth/authorize?client_id=" . $api_key . "&scope=" . $scopes . "&redirect_uri=" . urlencode($redirect_uri);

// Redirect
//echo $install_url;
header("Location: " . $install_url);
die();
?>
