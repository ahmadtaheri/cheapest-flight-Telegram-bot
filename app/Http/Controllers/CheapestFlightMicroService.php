<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\UserRequest;

class CheapestFlightMicroService extends Controller
{
    //
    private $Response_MS_Url= 'https://www.sunsoria.ir/response';
    private $Interactive_Response_MS_Url= 'https://www.sunsoria.ir/InteractiveResponse';

	public function Cheapest(Request $Request) {
	    $chat_id=$Request->input('chat_id');
	    $from=$Request->input('from');
	    $to=$Request->input('to');
	    $date=$Request->input('date');
	    $first_name=$Request->input('first_name');
	    $last_name=$Request->input('last_name');
	    $callback_query_id=$Request->input('callback_query_id');

		$User_Flight_Info=$this->Prepare_Flight_Info_for_Sepehr360($from,$to,urldecode($date));

		$Result_Of_Search=curl_sepehr360($User_Flight_Info);

		$Result_Object=json_decode($Result_Of_Search);

		if ($Result_Object->FlightList!==null) {
			$Result_Array_To_Response=$this->Make_array_From_Sepehr360_Result($Result_Object);
		    $Result_Array_To_Response['chat_id']=$chat_id;
		    $Result_Array_To_Response['callback_query_id']=$callback_query_id;
		    $Result_Array_To_Response['msgtype']='HasFlight';
		   
		    // $Send_Result_To_Response=curl($this->Response_MS_Url.'?'.http_build_query($Result_Array_To_Response));
		    $Receipt_Of_Sent_Message=curl($this->Interactive_Response_MS_Url.'?'.http_build_query($Result_Array_To_Response));

		    if (json_decode($Receipt_Of_Sent_Message)->ok==true) {
		    	$Request_DB_Table= new UserRequest;
		    	$Request_DB_Table->Save_Request_To_DB($Request_DB_Table,$chat_id,$from,$to,$date,$first_name,$last_name,$Result_Array_To_Response['CheapesPrice']);
		    }

		    

		    // return True;		
		}

		if ($Result_Object->FlightList==null && $Result_Object->EarliestAndCheapestFlightsInTwoWeeksLater->EarliestFlightDate!==null) {
			$EarliestFlightInfo=$Result_Object->EarliestAndCheapestFlightsInTwoWeeksLater;
			$Result_Array_To_Response['EarliestFlightDate']=$EarliestFlightInfo->EarliestFlightDate;
			$Result_Array_To_Response['EarliestFlightTime']=$EarliestFlightInfo->EarliestFlightTime;
		    $Result_Array_To_Response['chat_id']=$chat_id;
		    $Result_Array_To_Response['msgtype']='HasEarliestFlight';
		    // $Send_Result_To_Response=curl($this->Response_MS_Url.'?'.http_build_query($Result_Array_To_Response));
		    $Send_Result_To_Response=curl($this->Interactive_Response_MS_Url.'?'.http_build_query($Result_Array_To_Response));	
		}

		if ($Result_Object->EarliestAndCheapestFlightsInTwoWeeksLater->EarliestFlightDate==null && $Result_Object->FlightList==null) {
			// $EarliestFlightInfo=$Result_Object->EarliestAndCheapestFlightsInTwoWeeksLater;
			$Result_Array_To_Response['callback_query_id']=$callback_query_id;
		    $Result_Array_To_Response['chat_id']=$chat_id;
		    $Result_Array_To_Response['msgtype']='HasNoFlight';
		    // $Send_Result_To_Response=curl($this->Response_MS_Url.'?'.http_build_query($Result_Array_To_Response));
		    $Send_Result_To_Response=curl($this->Interactive_Response_MS_Url.'?'.http_build_query($Result_Array_To_Response));	
		}


		// return $Send_Result_To_Response;
	
	
	}//Cheapest




	public function Prepare_Flight_Info_for_Sepehr360($From,$To,$Date) {
			$flight=$this->Flight_General_Info();
			$flight["From"]=$From;
			$flight["To"]=$To;
			$flight["StartDate"]=$Date;
	        return $flight; 
	}//Prepare_Flight_Info_for_Sepehr360



	private function Flight_General_Info() {
			$flight=[
			"IsDirectContract"=>false,
			"IsCancelAvail"=>false,
			"IsFlightContinuous"=>false,
			"IsTour"=>false,
			"getTwoWay"=>false,
			"MinMaxDepartureList"=>[],
			"AirLineList"=>[],
			"DaysList"=>[],
			"FareClassList"=>[],
			"PassengerItem"=>[],
			"Client"=>"web",
			"RecordId"=>1,
			"SortOrder"=>1,
			"CurencyType"=>"IRR",
			"IsTwoWay"=>false,
			"PageSize"=>5,
			"IsLogin"=>true
			];

		    return $flight;

	}//Flight_General_Info


	public function Make_array_From_Sepehr360_Result($Result_Object) {

		// $Result_Object=json_decode($Result);

		if(isset($Result_Object->FlightList[1]->FlightItems[0]->FlightInfo)){

				$Result_Array_To_Response=array(
				"From"=>$Result_Object->FromCity,
				"To"=>$Result_Object->ToCity,
				"Date"=>$Result_Object->JalaliDepartureDateFormatted,
				"FlightNumber"=>$Result_Object->FlightList[1]->FlightNumber,
				"CheapesPrice"=>$Result_Object->FlightList[1]->FlightItems[0]->FlightInfo->FormatedPrice,
				"AirLine"=>$Result_Object->FlightList[1]->FlightItems[0]->FlightInfo->AirLineTitle,
				"DepartureTime"=>$Result_Object->FlightList[1]->FlightItems[0]->FlightInfo->DepartureTime,
				"AgencyName"=>$Result_Object->FlightList[1]->FlightItems[0]->FlightInfo->AgencyName,
				// "AgencyUrl"=>$Result_Object->FlightList[1]->FlightItems[0]->FlightInfo->AgencySiteUrl,
				"AgencyUrl"=>'https://sepehr360.com/fa'.$Result_Object->FlightList[1]->FlightItems[0]->FlightInfo->AgencyAddress,				

			);

				return $Result_Array_To_Response;
				// return $Result_Array_To_Response;
		} 

		if($Result_Object->FlightList[0]->FirstAdevrtisment==null){


				$Result_Array_To_Response=array(
				"From"=>$Result_Object->FromCity,
				"To"=>$Result_Object->ToCity,
				"Date"=>$Result_Object->JalaliDepartureDateFormatted,
				"FlightNumber"=>$Result_Object->FlightList[0]->FlightNumber,
				"CheapesPrice"=>$Result_Object->FlightList[0]->FlightItems[0]->FlightInfoList[0]->FormatedPrice,
				"AirLine"=>$Result_Object->FlightList[0]->FlightItems[0]->FlightInfoList[0]->AirLineTitle,
				"DepartureTime"=>$Result_Object->FlightList[0]->FlightItems[0]->FlightInfoList[0]->DepartureTime,
				"AgencyName"=>$Result_Object->FlightList[0]->FlightItems[0]->FlightInfoList[0]->AgencyName,
				"AgencyUrl"=>'https://sepehr360.com/fa'.$Result_Object->FlightList[0]->FlightItems[0]->FlightInfoList[0]->AgencyAddress


			);

				return $Result_Array_To_Response;
				
		} 

		// elseif (isset($Result_Object->FlightList[1]->FlightItems["0"])) {
			
		else {
				$Result_Array_To_Response=array(
				
				"From"=>$Result_Object->FromCity,
				"To"=>$Result_Object->ToCity,
				"Date"=>$Result_Object->JalaliDepartureDateFormatted,
				"FlightNumber"=>$Result_Object->FlightList[1]->FlightNumber,
				"CheapesPrice"=>$Result_Object->FlightList[1]->FlightItems[0]->FlightInfoList[0]->FormatedPrice,
				"AirLine"=>$Result_Object->FlightList[1]->FlightItems[0]->FlightInfoList[0]->AirLineTitle,
				"DepartureTime"=>$Result_Object->FlightList[1]->FlightItems[0]->FlightInfoList[0]->DepartureTime,
				"AgencyName"=>$Result_Object->FlightList[1]->FlightItems[0]->FlightInfoList[0]->AgencyName,
				"AgencyUrl"=>'https://sepehr360.com/fa'.$Result_Object->FlightList[1]->FlightItems[0]->FlightInfoList[0]->AgencyAddress
			);
				
				return $Result_Array_To_Response;
		}
		
		
		// return $Result_Array_To_Response
	   
	} // Make_array_From_Sepehr360_Result 

    
}
