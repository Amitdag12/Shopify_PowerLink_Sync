


<?php


define('SHOPIFY_APP_SECRET', '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');



function verify_webhook($data, $hmac_header)
{
  $calculated_hmac = base64_encode(hash_hmac('sha256', $data, SHOPIFY_APP_SECRET, true));
  return hash_equals($hmac_header, $calculated_hmac);
}



$hmac_header = $_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'];
$data = file_get_contents('php://input');
//error_log('Webhook verified: '.var_export($verified, true)); //check error.log to see the result
error_log("A!   ".$hmac_header);
$obj=json_decode($data);
error_log($obj->{'id'});

//error_log($data);
if(!CheckIfUserExist($obj->{'id'})){
AddNewCustomer($obj->{'first_name'},$obj->{'id'},$obj->{'addresses'}[0]->{'phone'},$obj->{'addresses'}[0]->{'city'});
}
/*
//add new user
$data = array(
      "accountname" => "משה",
      "telephone1" => "036339060",
      "idnumber" => "1234",
      "billingcity" => "תל אביב"
);
$url='https://api.powerlink.co.il/api/record/account';
$data_string = json_encode($data);

$curl = curl_init();
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_HTTPHEADER, array(
'Content-Type: application/json',
'tokenid: xxxxxxx-xxxxx-xxxxx-xxxxx',
 'Content-Length: ' . strlen($data_string))
);
$result = curl_exec($curl);
curl_close($curl);
///////////////////
//GET /admin/orders.json?customer_id=207119551
*/
function AddNewCustomer($name,$id,$tel='',$city=''){
  //add new user
	$name =str_contains($name,'\u')?json_decode($name):$name;
	$city =str_contains($city,"\u")?json_decode($city):$city;
  $data = array(
        "accountname" => $name,
        "telephone1" => $tel,
        "idnumber" => $id,
        "billingcity" => $city
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
  $data = array(
        "page_number" => 1,
        "objecttype" => 1,
        "page_size" => 100,
        "query" => "(idnumber = ".$idNumber .")",    //optional field
        "fields" => "*",    //optional field
        "sort_by" => "createdon",    //optional field
        "sort_type" => "desc" //optional field
  );
  $url='https://api.powerlink.co.il/api/query';
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
  $result = json_decode($result);
  curl_close($curl);
  if($result->{"data"}->{'Data'}[0]==NULL){
    return FALSE;
  }
  else {
    return TRUE;
  }
}
function AssignCustomersOrders($customerId){
  $orders = shopify_call($token, $shop, "/admin/orders.json?customer_id=".$customerId, array(), 'GET');
  $orders = json_decode($orders['response'], TRUE);
  foreach ($orders as &$order) {
      AssignOrderToUser($order);
  }
}
function AssignOrderToUser($order){
  $lineItems=$order->{'line_items'};
  $orderDesc="";
  foreach ($lineItems as &$lineItem) {
      $orderDesc.=$lineItem->{'title'}." ID:". $lineItem->{'product_id'}." price:".$lineItem->{'price'}." <br>";
  }
  $data = array(
        "description" => $orderDesc,
        "accountid" => $id,
        "crmordernumber" => $order->{'id'},
        "objecttype" => "13"
  );
  MakeApiCallToPowerLInk($data);
}
function MakeApiCallToPowerLInk($data){
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







?>
