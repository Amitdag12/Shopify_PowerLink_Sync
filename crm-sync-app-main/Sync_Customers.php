


<?php


define('SHOPIFY_APP_SECRET', 'ShopifYSecretKey');



function verify_webhook($data, $hmac_header)
{
  $calculated_hmac = base64_encode(hash_hmac('sha256', $data, SHOPIFY_APP_SECRET, true));
  return hash_equals($hmac_header, $calculated_hmac);
}



$hmac_header = $_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'];
$data = file_get_contents('php://input');
$verified = verify_webhook($data, $hmac_header);
//error_log('Webhook verified: '.var_export($verified, true)); //check error.log to see the result
error_log("A!   ".$hmac_header);
$obj=json_decode($data);
error_log($obj->{'id'});
//error_log($data);
AddNewCustomer($obj->{'first_name'},$obj->{'phone'},$obj->{'id'},$obj->{'addresses'}[0]->{'city'});
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








?>
