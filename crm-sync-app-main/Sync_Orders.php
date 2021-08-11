<?php


define('SHOPIFY_APP_SECRET', 'secretKey');


$hmac_header = $_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'];
$data = file_get_contents('php://input');
error_log("A!   ".$hmac_header);
$obj=json_decode($data);
error_log("A!   ".$obj->{'customer'}->{'id'});
if(!CheckIfUserExist($obj->{'customer'}->{'id'})){//if user does not exist create one
$customer = $obj->{'customer'};
AddNewCustomer($customer->{'first_name'},$customer->{'id'},$customer->{'phone'},$customer->{'addresses'}[0]->{'city'});
}
AssignOrderToUser($obj,$obj->{'customer'}->{'id'});




function AssignCustomersOrders($customerId){
  $orders = shopify_call($token, $shop, "/admin/orders.json?customer_id=".$customerId, array(), 'GET');
  $orders = json_decode($orders['response'], TRUE);
  foreach ($orders as &$order) {
      AssignOrderToUser($order);
  }
}
function AssignOrderToUser($order,$id){
  $lineItems=$order->{'line_items'};
  $orderDesc="";
  $items=[];
  $item;
  foreach ($lineItems as &$lineItem) {
      $orderDesc.=" שם מוצר: ".$lineItem->{'title'}."   צבע: ".$lineItem->{'vendor'}."   מידה:".$lineItem->{'variant_title'}."       מחיר:".$lineItem->{'price'}."                                              ";
        $item=array("productname"=>$lineItem->{'title'},"itemprice"=>$lineItem->{'price'},"itemquantity"=>$lineItem->{'quantity'},"itemtotalprice" =>($lineItem->{'quantity'}*$lineItem->{'price'}),"description"=>"צבע: ".$lineItem->{'vendor'}."   מידה:".$lineItem->{'variant_title'});
	  $items[]=$item;
  }
	
  $orderDesc.="                                                   סהכ:".$order->{'total_price'};
  $data = array(
        "description" => $orderDesc,
        "accountid" => FindUserId($id),
        "orgidnumber" => $order->{'name'},
        "totalamount" => str_replace(".00","",$order->{'total_price'}),
        "idnumber" => $order->{'name'},
        "productstotal"=>str_replace(".00","",$order->{'total_price'}),
        "modifiedon" => $order->{'created_at'},
	"Items" => array("Item"=>$items)
  );
  MakeApiCallToPowerLInk($data,'CrmOrder');
}
function MakeApiCallToPowerLInk($data,$objectType){
  $url='https://api.powerlink.co.il/api/record/'.$objectType;
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
  'tokenid: ***********************',
   'Content-Length: ' . strlen($data_string))
  );
  $result = curl_exec($curl);
  error_log(curl_error($curl));
  curl_close($curl);
  error_log($data_string."     ".strlen($data_string));
}
function FindUserId($idNumber){

  //query users
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
      'tokenid: ************************',
      'Content-Length: ' . strlen($data_string))
  );
  $result = curl_exec($curl);
  $result = json_decode($result);

  error_log($data_string."     ".strlen($data_string));
  curl_close($curl);
  return $result->{"data"}->{'Data'}[0]->{"accountid"};



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
      'tokenid: ***********************',
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
  'tokenid: *************************',
   'Content-Length: ' . strlen($data_string))
  );
  $result = curl_exec($curl);
  error_log(curl_error($curl));
  curl_close($curl);
  error_log($data_string."     ".strlen($data_string));
}


?>
