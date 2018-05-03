<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\UserRequest;
use Carbon\Carbon;

class FlightNotificationMicroService extends Controller
{
  
    private $Interactive_Response_MS_Url= 'https://www.sunsoria.ir/InteractiveResponse';

    public function flightNotification() {
    	$Response_Object=new ResponseMicroService;
    	$Data=new UserRequest;
    	$now=new Carbon;
    	$yesterday_till_now=$now->subDays(1);
    	$Flights = $Data::where([['created_at','>',$yesterday_till_now],['Is_Cancelled',0],])->get();
    	$User_Flight_Req=[];
        $Users_Chat_Id=[];
    
    	for ($i = 0; $i<count($Flights) ; $i++) {
            $User_Flight_Req[$i]['From_Id']=$Flights[$i]->From_Id;
    		$User_Flight_Req[$i]['Trip_From']=$Flights[$i]->Trip_From;
    		$User_Flight_Req[$i]['Trip_To']=$Flights[$i]->Trip_To;
    		$User_Flight_Req[$i]['Trip_Jalali_Date']=$Flights[$i]->Trip_Jalali_Date;
            array_shift($User_Flight_Req[$i]);
    	}
       $User_Flight_Req=array_unique($User_Flight_Req,SORT_REGULAR);
       
    	foreach ($User_Flight_Req as $Flight) {
        $result=$this->get_result_from_sepehr360_for_notification($Flight);
        $people = $Data::select(['From_Id'])->where('Trip_From',$result['Trip_From'])->where('Trip_To',$result['Trip_To'])->where('Trip_Jalali_Date',$result['Trip_Jalali_Date'])->where('created_at','>',$yesterday_till_now)->where('Is_Cancelled',0)->distinct()->get();
        
                    foreach ($people as $person) {
                    $id=$Data::select('id')->where('From_Id',$person->From_Id)->where('Trip_From',$result['Trip_From'])->where('Trip_To',$result['Trip_To'])->where('Trip_Jalali_Date',$result['Trip_Jalali_Date'])->where('created_at','>',$yesterday_till_now)->where('Is_Cancelled',0)->limit(1)->get();
                    
                    $result['id']=$id[0]->id;
                    $result['chat_id']=$person->From_Id;
                        if($result['HasFlight']==true) {
                        $result['msgtype']='NotificationHasFlight';   
                        }
                        else {
                        $result['msgtype']='NotificationNoFlight';  
                        }
                   
                    $Send_Result_To_Response=curl($this->Interactive_Response_MS_Url.'?'.http_build_query($result));
                    }

    	}  	

    }


    private function get_result_from_sepehr360_for_notification($Flight){
        $CheapestFlight_Object=new CheapestFlightMicroService;
        $from=$Flight['Trip_From'];
        $to=$Flight['Trip_To'];
        $date=$Flight['Trip_Jalali_Date'];
        $User_Flight_Info=$CheapestFlight_Object->Prepare_Flight_Info_for_Sepehr360($from,$to,$date);
        $Result_Of_Search=curl_sepehr360($User_Flight_Info);
        $Result_Object=json_decode($Result_Of_Search);

        if ($Result_Object->FlightList!==null) {
            $Result=$CheapestFlight_Object->Make_array_From_Sepehr360_Result($Result_Object);
            $Result['Trip_From']=$Flight['Trip_From'];
            $Result['Trip_To']=$Flight['Trip_To'];
            $Result['Trip_Jalali_Date']=$Flight['Trip_Jalali_Date'];
            $Result['HasFlight']=true;
       
            return $Result;     
        }

        if ($Result_Object->FlightList==null && $Result_Object->EarliestAndCheapestFlightsInTwoWeeksLater->EarliestFlightDate!==null) {
   
            $Result['Trip_From']=$Flight['Trip_From'];
            $Result['Trip_To']=$Flight['Trip_To'];
            $Result['Trip_Jalali_Date']=$Flight['Trip_Jalali_Date']; 
            $Result['HasFlight']=false;

            return $Result; 
        }


        if ($Result_Object->EarliestAndCheapestFlightsInTwoWeeksLater->EarliestFlightDate==null && $Result_Object->FlightList==null) {
            $Result['Trip_From']=$Flight['Trip_From'];
            $Result['Trip_To']=$Flight['Trip_To'];
            $Result['Trip_Jalali_Date']=$Flight['Trip_Jalali_Date']; 
            $Result['HasFlight']=false;

            return $Result; 

        }

    }
}
