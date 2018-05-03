<?php 
function curl($url)  {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$result=curl_exec($curl);
		curl_close($curl);	
		return $result;
}

function curl_sepehr360($flight_info) {
		$flight_info=json_encode($flight_info);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,"https://sepehr360.com/fa/Api/FlightApi/FlightSearchGrouped");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$flight_info);
		curl_setopt($ch, CURLOPT_HTTPHEADER,array('Content-Type: application/json;charset=UTF-8'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result=curl_exec ($ch);
		curl_close ($ch);

		return $result;
}

// function Prepare_Querry_Url_To_Call_Services($Service_Url,$Querry) {

// 	return $this->

// }
