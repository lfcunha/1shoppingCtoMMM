<?php

class Client
{
//1shopping cart


private $time;
private $date;
private $orders;
private $orders2;

private $url;
private $xml;
private $sxml;
private $clientId;
private $clientInfo;



function __construct($clientId, $merchantId, $merchantKey)
{
$this->clientId=$clientId; 
$this->merchantId=$merchantId;
$this->merchantKey=$merchantKey;
}






//use curl to get the contents

private function download_page($path){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$path);
	curl_setopt($ch, CURLOPT_FAILONERROR,1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 15);
	$retValue = curl_exec($ch);			 
	curl_close($ch);
	return $retValue;
}




private function process()    //create an associative array with each property for each bus
{
$this->clientInfo=array();

$this->url="https://www.mcssl.com/API/". $this->merchantId . "/Clients/" . $this->clientId ."?KEY=" . $this->merchantKey;
//echo $this->path;

$xml = $this->download_page($this->url);
$this->sxml=new SimpleXMLElement($xml);


foreach ($this->sxml->{'ClientInfo'} as $key) {
        $this->clientInfo['first_name']=(string)$key->{'FirstName'};
        $this->clientInfo['last_name']=(string)$key->{'LastName'};
        $this->clientInfo['email']=(string)$key->{'Email'};
        $this->clientInfo['phone']=(string)$key->{'Phone'};
        $this->clientInfo['tel2']=(string)$key->{'SecondaryPhone'};
        $this->clientInfo['fax']=(string)$key->{'Fax'};
        $this->clientInfo['address']=(string)$key->{'Address1'};
        $this->clientInfo['Addr(cont)']=(string)$key->{'Address2'};
        $this->clientInfo['city']=(string)$key->{'City'};
        $this->clientInfo['zip']=(string)$key->{'Zip'};
        $this->clientInfo['state']=(string)$key->{'StateName'};       
        $this->clientInfo['country']=(string)$key->{'CountryName'};
        $this->clientInfo['createdfromip']=(string)$key->{'CreatedFromIp'};
        $this->clientInfo['company']=(string)$key->{'Company'};


  
        
}
//var_dump($this->clientInfo);

}//end process();



public function getclientInfo(){
$this->process();
return $this->clientInfo;
}



}//end class

?>