<?php

function shopify_call($token, $shop, $api_endpoint, $query = array(), $method = 'GET', $request_headers = array()) {
  $PASSWORD = "SecretKey";
  $API_KEY = "ApiKEy";
  $AUTHORIZATION = base64_encode($API_KEY . ':' . $PASSWORD);
	// Build URL
	$url = "https://" .$API_KEY . ':' . $PASSWORD . '@'. $shop . ".myshopify.com" . $api_endpoint;
	if (!is_null($query) && in_array($method, array('GET', 	'DELETE'))) $url = $url . "?" . http_build_query($query);

	// Configure cURL
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_HEADER, TRUE);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
	curl_setopt($curl, CURLOPT_MAXREDIRS, 3);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
	// curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 3);
	// curl_setopt($curl, CURLOPT_SSLVERSION, 3);
	curl_setopt($curl, CURLOPT_USERAGENT, 'My New Shopify App v.1');
	curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($curl, CURLOPT_TIMEOUT, 30);
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

	// Setup headers
	$request_headers[] = "";
  $header = array();
$header[] = 'Accept: application/json';
$header[] = 'Content-Type: application/json';
$header[] = 'Authorization: Basic ' . $AUTHORIZATION;
	if (!is_null($token)){
   $request_headers[] = "X-Shopify-Access-Token: " . $token;
 }
	curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

	if ($method != 'GET' && in_array($method, array('POST', 'PUT'))) {
		if (is_array($query)) $query = http_build_query($query);
		curl_setopt ($curl, CURLOPT_POSTFIELDS, $query);
	}

	// Send request to Shopify and capture any errors
	$response = curl_exec($curl);
	$error_number = curl_errno($curl);
	$error_message = curl_error($curl);

	// Close cURL to be nice
	curl_close($curl);

	// Return an error is cURL has a problem
	if ($error_number) {
		return $error_message;
	} else {

		// No error, return Shopify's response by parsing out the body and the headers
		$response = preg_split("/\r\n\r\n|\n\n|\r\r/", $response, 2);

		// Convert headers into an array
		$headers = array();
		$header_data = explode("\n",$response[0]);
		$headers['status'] = $header_data[0]; // Does not contain a key, have to explicitly set
		array_shift($header_data); // Remove status, we've already set it above
		foreach($header_data as $part) {
			$h = explode(":", $part);
			$headers[trim($h[0])] = trim($h[1]);
		}

		// Return headers and Shopify's response
		return array('headers' => $headers, 'response' => $response[1]);

	}

}
/*
//GET /admin/orders.json?customer_id=207119551
*/
function AddNewCustomer($name,$tel,$id,$city){
  //add new user
  $data = array(
        "accountname" => $name,
        "telephone1" => $tel,
        "idnumber" => $id,
        "billingcity" => $city,
        "Originatingleadcode" => "3"
  );
  $url='https://api.powerlink.co.il/api/record/account';
  $data_string = json_encode($data);

  $curl = curl_init();
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
  curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
  curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
  curl_setopt($curl, CURLOPT_URL, $url);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($curl, CURLOPT_HTTPHEADER, array(
  'Content-Type: application/json',
  'tokenid: 1e3ef135-1da0-4c61-b746-774a8a375b0a',
   'Content-Length: ' . strlen($data_string))
  );
  $result = curl_exec($curl);
  error_log(curl_error($curl));
  curl_close($curl);
  error_log($data_string."     ".strlen($data_string));
}
function CheckIfUserExist($idNumber){

  //query users

  $data = array(
        "page_number" => 1,
        "objecttype" => 1,
        "page_size" => 100,
        "query" => "(idnumber =".$idNumber .")",    //optional field
        "fields" => "*",    //optional field
        "sort_by" => "createdon",    //optional field
        "sort_type" => "desc" //optional field
  );
  $url='https://api.powerlink.co.il/api/query';
  $data_string = json_encode($data);
  $curl = curl_init();
  curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
  curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
  curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
  curl_setopt($curl, CURLOPT_URL, $url);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($curl, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json',
      'tokenid: xxxxxx-xxxxx-xxxxx-xxxxx',
      'Content-Length: ' . strlen($data_string))
  );
  $result = curl_exec($curl);
  curl_close($curl);
  if($result==NULL){
    return FALSE;
  }
  else {
    return TRUE;
  }



}
