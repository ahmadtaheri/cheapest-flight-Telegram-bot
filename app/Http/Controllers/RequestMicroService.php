<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\test;
use App\UserRequest;
use Carbon\Carbon;
use p3ym4n\JDate\JDate;

class RequestMicroService extends Controller
{
	private $Bot_Token="533694876:AAGzyfMWKKYPcMPFkXCjwr1YKnGwb4eTNNA";
    private $Bot_Url = "https://api.telegram.org/bot";

    private $channel='@testcheapflight';

	private $Response_MS_Url= 'https://www.sunsoria.ir/response';
	private $Interactive_Response_MS_Url= 'https://www.sunsoria.ir/InteractiveResponse';
	private $CheapestFlight_MS_Url= 'https://www.sunsoria.ir/Cheapest';



    public function interactive() {

	    $Message = file_get_contents("php://input");
		$Message_Object= json_decode($Message);



			if (isset($Message_Object->message)&&$Message_Object->message->text=='/start') {

				$from_id = $Message_Object->message->from->id;
				$join=curl($this->Bot_Url.$this->Bot_Token."/getChatMember?chat_id=".$this->channel."&user_id=".$from_id);
				$join=json_decode($join);
				$is_join =$join->result->status;
				if($join->ok=='true'){
					if($is_join=='left'){
						$Query_Array=$this->Make_Query_Array_For_Other_Services($Message_Object,'Join');
				        curl($this->Interactive_Response_MS_Url."?".http_build_query($Query_Array)); 
					}
					else{
						$Query_Array=$this->Make_Query_Array_For_Other_Services($Message_Object,'From');
						curl($this->Interactive_Response_MS_Url."?".http_build_query($Query_Array));
					}
				}		

			}//if 


			if (isset($Message_Object->callback_query)) {

				$CallBack_Data=explode("-",$Message_Object->callback_query->data,10);

				if(count($CallBack_Data)==2 && $CallBack_Data[0]=='cancel'){
				
					$Data=new UserRequest;
					$cancel=$Data->find($CallBack_Data[1]);
					$cancel->Is_Cancelled=True;
					$cancel->save();
					$callback_query_id=$Message_Object->callback_query->id;
					$Query_Array=$this->Make_Query_Array_For_Other_Services($cancel,'CancelNotification',$callback_query_id);
					curl($this->Interactive_Response_MS_Url."?".http_build_query($Query_Array));
				}

				if (count($CallBack_Data)==3) {
				$Query_Array=$this->Make_Query_Array_For_Other_Services($Message_Object,'To',$CallBack_Data);
				curl($this->Interactive_Response_MS_Url."?".http_build_query($Query_Array));
				}

				if (count($CallBack_Data)==5 && ($CallBack_Data[2]==$CallBack_Data[4])) {
				$Query_Array=$this->Make_Query_Array_For_Other_Services($Message_Object,'To',$CallBack_Data);
				curl($this->Interactive_Response_MS_Url."?".http_build_query($Query_Array));		
				}


				if(count($CallBack_Data)==5 && ($CallBack_Data[2]!==$CallBack_Data[4])) {
				$Query_Array=$this->Make_Query_Array_For_Other_Services($Message_Object,'Date',$CallBack_Data);
				curl($this->Interactive_Response_MS_Url."?".http_build_query($Query_Array));
				}
			
				if (count($CallBack_Data)==6) {
				$Query_Array=$this->Make_Query_Array_For_Other_Services($Message_Object,'FlightResponse',$CallBack_Data);
				$Has_Flight=curl($this->CheapestFlight_MS_Url."?".http_build_query($Query_Array));
				}

			}


		
    }


    private function Make_Query_Array_For_Other_Services($Message_Object,$msgtype,$CallBack_Data=null) {

    	switch ($msgtype) {

    		case 'Join':
    		$Query_Array=array();
			$Query_Array['chat_id']=$Message_Object->message->from->id;
			$Query_Array['msgtype']='Join';
			$Query_Array['channel']=$this->channel;
    			break;

    		case 'From':
    		$Query_Array=array();
			$Query_Array['chat_id']=$Message_Object->message->chat->id;
			$Query_Array['msgtype']='From';
    			break;
    		
    		case 'To':
    		$Query_Array=array();
			$Query_Array['chat_id']=$Message_Object->callback_query->from->id;
			$Query_Array['msgtype']='To';
			$Query_Array['callback_query_id']=$Message_Object->callback_query->id;
			$Query_Array['from']=$CallBack_Data[2];
    			break;

    		case 'Date':
    		$Query_Array=array();
			$Query_Array['chat_id']=$Message_Object->callback_query->from->id;
			$Query_Array['msgtype']='Date';
			$Query_Array['callback_query_id']=$Message_Object->callback_query->id;
			$Query_Array['from']=$CallBack_Data[2];
			$Query_Array['to']=$CallBack_Data[4];		
    			break;

    		case 'FlightResponse':
    		$Query_Array=array();
    		$Query_Array['chat_id']=$Message_Object->callback_query->from->id;
    		$Query_Array['first_name']=$Message_Object->callback_query->from->first_name;
    		$Query_Array['last_name']=$Message_Object->callback_query->from->last_name;
    		$Query_Array['msgtype']='FlightResponse';
    		$Query_Array['callback_query_id']=$Message_Object->callback_query->id;
    		$Query_Array['from']=$CallBack_Data[2];
    		$Query_Array['to']=$CallBack_Data[4];
    		$Query_Array['date']=$CallBack_Data[5];
    		    break;

    		case 'CancelNotification':
    		$Query_Array=array();
    		$Query_Array['chat_id']=$Message_Object->From_Id;
    		$Query_Array['msgtype']='CancelNotification';
    		$Query_Array['from']=$Message_Object->Trip_From;
    		$Query_Array['to']=$Message_Object->Trip_To;
    		$Query_Array['date']=$Message_Object->Trip_Jalali_Date;
    		$Query_Array['callback_query_id']=$CallBack_Data;
    		    break;


    		default:
    			// code...
    			break;
    	}

    	return $Query_Array;

    }


    public function db() {

        
     }


}


