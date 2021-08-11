<?php
require_once("inc/functions.php");

// Set variables for our request
$shop = "ShopName";
$token = "Token";
$query = array(
	"Content-type" => "application/json" // Tell Shopify that we're expecting a response in JSON format
);

// Run API call to get products
$customer = shopify_call($token, $shop, "/admin/customers.json", array(), 'GET');

// Convert product JSON information into an array
$customer = json_decode($customer['response'], TRUE);

foreach ($customer as &$valuess) {
  foreach ($valuess as &$values) {
  //  foreach ($values as &$value) {
  //    try {
        echo var_dump($values).'<br>';
  //  }catch (Exception $e) {

//}
  //}
}
}
//echo $customer[0];


 ?>
