<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use p3ym4n\JDate\JDate;

class ResponseMicroService extends Controller
{
    private $Bot_Token="533694876:AAGzyfMWKKYPcMPFkXCjwr1YKnGwb4eTNNA";
    private $Bot_Url = "https://api.telegram.org/bot";
    Private $Bot_SendMessage="/sendmessage";


		    public function Interactive_Response_With_User(Request $Request) {


					$msgtype=$Request->input('msgtype');
					$chat_id=$Request->input('chat_id');

					switch ($msgtype) {

						case "Join":
					    $chat_id=$Request->input('chat_id');
					    $channel=$Request->input('channel');
					    $text="برای استفاده از ربات خواهشمند است ابتدا در کانال زیر عضو شوید:"."\n";
					    $text.=$channel."\n";
					    $text.="بعد از عضویت در کانال مجددا دستور زیر را ارسال نمایید:"."\n";
					    $text.="/start";
						curl($this->Telegram_SendMessage_Url()."?chat_id=".$chat_id."&text=".urlencode($text));
							break;

					    case "From":
					    $query_array=$this->Prepare_SendMessage_Query_For_From_Menu($chat_id);
						curl($this->Telegram_SendMessage_Url()."?".http_build_query($query_array));
							break;

						case "To":
						$from=$Request->input('from');
						$callback_query_id=$Request->input('callback_query_id');
						$query_array=$this->Prepare_SendMessage_Query_For_To_Menu($chat_id,$from,$callback_query_id);
						curl($this->Telegram_SendMessage_Url()."?".http_build_query($query_array));
						curl ($this->Bot_Url.$this->Bot_Token."/answerCallbackQuery?callback_query_id=".$callback_query_id);
							break;

						case "Date":
					    $from=$Request->input('from');
					    $to=$Request->input('to');
						$callback_query_id=$Request->input('callback_query_id');
					    $query_array=$this->Prepare_SendMessage_Query_For_Date_Menu($chat_id,$from,$to,$callback_query_id);
						curl($this->Telegram_SendMessage_Url()."?".http_build_query($query_array));
						curl ($this->Bot_Url.$this->Bot_Token."/answerCallbackQuery?callback_query_id=".$callback_query_id);
							  break;

						case "HasFlight":
						$from=$Request->input('From');
						$to=$Request->input('To');
						$date=$Request->input('Date');
						$callback_query_id=$Request->input('callback_query_id');
						$text=$this->Prepare_Response_Format_Before_Send($Request);
						$res=curl($this->Telegram_SendMessage_Url()."?"."chat_id=".$chat_id."&text=".urlencode($text)."&disable_web_page_preview=TRUE"."&parse_mode=HTML");
						curl ($this->Bot_Url.$this->Bot_Token."/answerCallbackQuery?callback_query_id=".$callback_query_id);
						$notification="🔔"."\n";
						$notification.="تا ۲۴ ساعت آینده ارزان ترین بلیت پرواز زیر هر یک ساعت به شما اطلاع رسانی می شود:"."\n";
						$notification.="\n".$from.' به '.$to."\n".'روز '.$date;
						curl($this->Telegram_SendMessage_Url()."?"."chat_id=".$chat_id."&text=".urlencode($notification)."&disable_web_page_preview=TRUE"."&parse_mode=HTML");
						return $res;
							break;

						case "HasEarliestFlight":
						$callback_query_id=$Request->input('callback_query_id');
						$EarliestFlightDate=$Request->input('EarliestFlightDate');
						$EarliestFlightTime=$Request->input('EarliestFlightTime');
						$text="⛔"."\n";
						$text.="متاسفانه پروازی برای این تاریخ وجود ندارد."."\n";
						$text.='نزدیکترین زمان پرواز در'.'تاریخ '.'<b>'.$EarliestFlightDate.'</b>'.' ساعت '.$EarliestFlightTime."\n";
						$text.='لطفا جستجوی خود را در تاریخ '.$EarliestFlightDate.' انجام دهید.'."\n"."⛔";
						$res=curl($this->Telegram_SendMessage_Url()."?"."chat_id=".$chat_id."&text=".urlencode($text)."&disable_web_page_preview=TRUE"."&parse_mode=HTML");
						curl ($this->Bot_Url.$this->Bot_Token."/answerCallbackQuery?callback_query_id=".$callback_query_id);
							break;

						case "HasNoFlight":
						$text="⛔"."\n";
						$text.="متاسفانه پروازی برای این تاریخ وجود ندارد."."\n";
						$res=curl($this->Telegram_SendMessage_Url()."?"."chat_id=".$chat_id."&text=".urlencode($text)."&disable_web_page_preview=TRUE"."&parse_mode=HTML");
							break;


						case "NotificationHasFlight":;
						$text=$this->Prepare_Response_Format_Before_Send_NotificationHasFlight($Request);
						$id=$Request->input('id');
						$from=$Request->input('From');
						$to=$Request->input('To');
						$cancel=$this->Prepare_SendMessage_Query_For_Notification_Cancel_Menu($id,$from,$to);		
						$res=curl($this->Telegram_SendMessage_Url()."?"."chat_id=".$chat_id."&text=".urlencode($text)."&disable_web_page_preview=TRUE&reply_markup=".$cancel);
						return $res;
							break;

						case "NotificationNoFlight":;
						$text=$this->Prepare_Response_Format_Before_Send_NotificationNoFlight($Request);
						$id=$Request->input('id');
						$from=$this->IATA_To_City($Request->input('Trip_From'));
						$to=$this->IATA_To_City($Request->input('Trip_To'));
						$cancel=$this->Prepare_SendMessage_Query_For_Notification_Cancel_Menu($id,$from,$to);		
						$res=curl($this->Telegram_SendMessage_Url()."?"."chat_id=".$chat_id."&text=".urlencode($text)."&reply_markup=".$cancel);
						return $res;
							break;

						case "CancelNotification":
						$callback_query_id=$Request->input('callback_query_id');
						$from=$this->IATA_To_City($Request->input('from'));
						$to=$this->IATA_To_City($Request->input('to'));
						$date=$Request->input('date');
						$text='اعلان ساعتی برای پرواز '.$from.' به '.$to.' روز '.$date.' لغو شد.';
						$res=curl($this->Telegram_SendMessage_Url()."?"."chat_id=".$chat_id."&text=".urlencode($text)."&disable_web_page_preview=TRUE"."&parse_mode=HTML");
						curl ($this->Bot_Url.$this->Bot_Token."/answerCallbackQuery?callback_query_id=".$callback_query_id);

						break;
							  default:			       
							}
		    } // Interactive_Response_With User()


       		private function Prepare_Response_Format_Before_Send($Request) {
		    	    	$text="\xE2\x9C\x88"."\n";
		    	    	$text.="از:"." ".$Request->input('From')."\n";
		    	    	$text.="به:"." ".$Request->input('To')."\n";
		    	    	$text.="تاریخ:"." ".$Request->input('Date')."\n";
		    	    	$text.="----------------------------------------------"."\n";
		    	    	$text.="ارزانترین بلیت:"." ".$Request->input('CheapesPrice')."تومان"."\n";
		    	    	$text.="----------------------------------------------"."\n";
		    	    	$text.="شماره پرواز:"." ".$Request->input('FlightNumber')."\n";
		    	    	$text.="شرکت هواپیمایی:"." ".$Request->input('AirLine')."\n";
		    	    	$text.="ساعت پرواز:"." ".$Request->input('DepartureTime')."\n";
		    	    	$text.="آژانس فروشنده:"." ".$Request->input('AgencyName')."\n";
		    	    	$text.="سایت آژانس:"." ".$Request->input('AgencyUrl')."\n"."\n"."\n";
		    	    	$text.="\xE2\x9C\x88";
		    	    	return $text;
		    }// 

		    private function Prepare_Response_Format_Before_Send_NotificationHasFlight($Request) {
		    	    	$text="🔔"."اعلان ساعتی"."🔔"."\n";
		    	    	$text.="از:"." ".$Request->input('From')."\n";
		    	    	$text.="به:"." ".$Request->input('To')."\n";
		    	    	$text.="تاریخ:"." ".$Request->input('Date')."\n";
		    	    	$text.="----------------------------------------------"."\n";
		    	    	$text.="ارزانترین بلیت:"." ".$Request->input('CheapesPrice')."تومان"."\n";
		    	    	$text.="----------------------------------------------"."\n";
		    	    	$text.="شماره پرواز:"." ".$Request->input('FlightNumber')."\n";
		    	    	$text.="شرکت هواپیمایی:"." ".$Request->input('AirLine')."\n";
		    	    	$text.="ساعت پرواز:"." ".$Request->input('DepartureTime')."\n";
		    	    	$text.="آژانس فروشنده:"." ".$Request->input('AgencyName')."\n";
		    	    	$text.="سایت آژانس:"." ".$Request->input('AgencyUrl')."\n"."\n"."\n";
		    	    	$text.="🔔";
		    	    	return $text;
		    }// 

			private function Prepare_Response_Format_Before_Send_NotificationNoFlight($Request) {
		    	    	$text="🔔"."اعلان ساعتی"."🔔"."\n\n";
		    	    	$text.="از:"." ".$this->IATA_To_City($Request->input('Trip_From'))."\n";
		    	    	$text.="به:"." ".$this->IATA_To_City($Request->input('Trip_To'))."\n";
		    	    	$text.="تاریخ:"." ".$Request->input('Trip_Jalali_Date')."\n";
		    	    	$text.="----------------------------------------------"."\n";
		    	    	$text.="برای سفر فوق بلیتی وجود ندارد."."😞"."\n";
		    	    	$text.="----------------------------------------------"."\n";
		    	    	$text.="🔔";
		    	    	return $text;
		    }// 

		    private function Prepare_SendMessage_Query_For_From_Menu($chat_id){
		    	    $From=['inline_keyboard'=>
			    	    	[
				        		[ //1strow
												           			           
									['text'=>'مشهد','callback_data'=>$chat_id.'-From-MHD'],
									['text'=>'تهران','callback_data'=>$chat_id.'-From-THR,IKA']
				        		] , //1st row
				        		[ //2nd row
				        			['text'=>'تبریز','callback_data'=>$chat_id.'-From-TBZ'],
				        			['text'=>'اهواز','callback_data'=>$chat_id.'-From-AWZ'],
									['text'=>'شیراز','callback_data'=>$chat_id.'-From-SYZ'],

				        		],//2nd row
				        		[ //3rd row
									['text'=>'اصفهان','callback_data'=>$chat_id.'-From-IFN'],
									['text'=>'قشم','callback_data'=>$chat_id.'-From-GSM'],
									['text'=>'کیش','callback_data'=>$chat_id.'-From-KIH'],
									['text'=>'بندرعباس','callback_data'=>$chat_id.'-From-BND'],
				        		],
				        		[ //4th	 row
				        		    ['text'=>'زاهدان','callback_data'=>$chat_id.'-From-ZAH'],
				        		    ['text'=>'کرمان','callback_data'=>$chat_id.'-From-KER'],
									['text'=>'عسلویه','callback_data'=>$chat_id.'-From-PGU'],
									['text'=>'گچساران','callback_data'=>$chat_id.'-From-GCH'],
									['text'=>'بوشهر','callback_data'=>$chat_id.'-From-BUZ'],					        
				        		]
				    		]

		 			];// 

					$From=json_encode($From);
					// querry parameters for sendmessage method of telegram
				    $query_array=array();
					$query_array["chat_id"]=$chat_id;
					$query_array["text"]='لطفا مبدا را انتخاب نمایید:'."\n"."🛫";
					$query_array["reply_markup"]=$From;
					
					return $query_array;
		    }

		    private function Prepare_SendMessage_Query_For_To_Menu($chat_id,$from,$callback_query_id) {

		    	$To=['inline_keyboard'=>
		    	       [
			        	  [ 				      
						  
							['text'=>'شیراز','callback_data'=>$chat_id.'-From-'.$from.'-To-SYZ'],
							['text'=>'تهران','callback_data'=>$chat_id.'-From-'.$from.'-To-THR,IKA'],
											          
			           	  ], //1st row
			              [ 
			        		
							['text'=>'کیش','callback_data'=>$chat_id.'-From-'.$from.'-To-KIH'],
							['text'=>'اهواز','callback_data'=>$chat_id.'-From-'.$from.'-To-AWZ'],
							['text'=>'مشهد','callback_data'=>$chat_id.'-From-'.$from.'-To-MHD'],						  
			        		
			              ],//2nd row
			        	  [ 

			        		['text'=>'بوشهر','callback_data'=>$chat_id.'-From-'.$from.'-To-BUZ'],
							['text'=>'قشم','callback_data'=>$chat_id.'-From-'.$from.'-To-GSM'],
							['text'=>'تبریز','callback_data'=>$chat_id.'-From-'.$from.'-To-TBZ'],
							['text'=>'بندرعباس','callback_data'=>$chat_id.'-From-'.$from.'-To-BND'],
							

			        	  ],//3rd row
			        	  [

			        		['text'=>'گچساران','callback_data'=>$chat_id.'-From-'.$from.'-To-GCH'],
							['text'=>'عسلویه','callback_data'=>$chat_id.'-From-'.$from.'-To-PGU'],
							['text'=>'کرمان','callback_data'=>$chat_id.'-From-'.$from.'-To-KER'],
                            ['text'=>'زاهدان','callback_data'=>$chat_id.'-From-'.$from.'-To-ZAH'],
                            ['text'=>'اصفهان','callback_data'=>$chat_id.'-From-'.$from.'-To-IFN'],				           
			        	  ]//4th row
			    		]
				];//

				$To=json_encode($To);
				$from_city=$this->IATA_To_City($from);
				$query_array=array();
				$query_array["chat_id"]=$chat_id;
				$query_array["text"]="مبدا:"."\t".$from_city."\n".'لطفا مقصد را انتخاب نمایید:'."\n"."🛬"."\n";
				$query_array["reply_markup"]=$To;
				return $query_array;
		    }

		    private function Prepare_SendMessage_Query_For_Date_Menu ($chat_id,$from,$to,$callback_query_id) {

			    $Next_7days_Jalali_Date=$this->Next_7days_Jalali_Date();
			    		
			    $Date=['inline_keyboard'=>
			    	        [
				        		[ 

				        			['text'=>'فردا'."\n".$Next_7days_Jalali_Date['jalaliDateText'][1],'callback_data'=>$chat_id.'-From-'.$from.'-To-'.$to."-".$Next_7days_Jalali_Date["jalaliDateNumber"][1]],

									['text'=>'امروز'."\n".$Next_7days_Jalali_Date['jalaliDateText'][0],'callback_data'=>$chat_id.'-From-'.$from.'-To-'.$to."-".$Next_7days_Jalali_Date["jalaliDateNumber"][0]],
												           
												           
				        		] , //1st row
				        		[ 
								    ['text'=>$Next_7days_Jalali_Date['jalaliDateText'][3],'callback_data'=>$chat_id.'-From-'.$from.'-To-'.$to."-".$Next_7days_Jalali_Date["jalaliDateNumber"][3]],

							        ['text'=>$Next_7days_Jalali_Date['jalaliDateText'][2],'callback_data'=>$chat_id.'-From-'.$from.'-To-'.$to."-".$Next_7days_Jalali_Date["jalaliDateNumber"][2]],
													      
				        	    ],//2nd row

				        		[
				        		    ['text'=>$Next_7days_Jalali_Date['jalaliDateText'][5],'callback_data'=>$chat_id.'-From-'.$from.'-To-'.$to."-".$Next_7days_Jalali_Date["jalaliDateNumber"][5]],

									['text'=>$Next_7days_Jalali_Date['jalaliDateText'][4],'callback_data'=>$chat_id.'-From-'.$from.'-To-'.$to."-".$Next_7days_Jalali_Date["jalaliDateNumber"][4]],
				        		],//3rd row
				        		[
				        			['text'=>$Next_7days_Jalali_Date['jalaliDateText'][6],'callback_data'=>$chat_id.'-From-'.$from.'-To-'.$to."-".$Next_7days_Jalali_Date["jalaliDateNumber"][6]],
				        		],
				    		]
					   ];//Date
								
					$Date=json_encode($Date);
					$from_city=$this->IATA_To_City($from);
					$to_city=$this->IATA_To_City($to);
					$query_array=array
					(
					"chat_id"=>$chat_id,
					"text"=>"مبدا: ".$from_city."\n"."مقصد: ".$to_city."\n".'لطفا تاریخ پرواز را انتخاب نمایید:'."\n"."\xE2\x8F\xB0",
					"reply_markup"=> $Date
					);
					
					return $query_array;
		    }
 
			private function Prepare_SendMessage_Query_For_Notification_Cancel_Menu($id,$from,$to){
				$cancel=['inline_keyboard'=>
							    	    	[
								        		[ //1strow
													['text'=>'لغو اعلان ساعتی پرواز'.' |'.$from.'-'.$to.'|','callback_data'=>'cancel'.'-'.$id],
								        		],
								        	]
								     ]; //1st row
		 				$cancel=json_encode($cancel);

		 		return $cancel;	
			}

		    public function Telegram_SendMessage_Url() {

		    	return $this->Bot_Url.$this->Bot_Token.$this->Bot_SendMessage;
		    }

		    private function IATA_To_City($IATA) {

		    	$IATA_To_City_Array=[
		    		"MHD"=>"مشهد",
		    		"THR,IKA"=>"تهران",
		    		"SYZ"=>"شیراز",
		    		"TBZ"=>"تبریز",
		    		"IFN"=>"اصفهان",
		    		"KIH"=>"کیش",
		    		"GSM"=>"قشم",
		    		"BND"=>"بندرعباس",
		    		"BUZ"=>"بوشهر",
		    		"AWZ"=>"اهواز",
		    		"ZAH"=>"زاهدان",
		    		"KER"=>"کرمان",
		    		"PGU"=>"عسلویه",
		    		"KSH"=>"کرمانشاه",
		    		"GCH"=>"گچساران",
		    	];

		    	return $IATA_To_City_Array[$IATA];
		    }

		 	private function Next_7days_Jalali_Date() {

				$carbon = new Carbon();
		    	$jdate = JDate::createFromCarbon($carbon);
		    	$Next_7days_Jalali_Date= array();
		    	for ($i = 0; $i<7 ; $i++) {
		    	$day=$jdate->now('Asia/Tehran')->addDays($i);
		    	$Next_7days_Jalali_Date["jalaliDateNumber"][]=$jdate->now('Asia/Tehran')->addDays($i)->format('Y/m/d');
		    	$Week_Day_Name=$this->Convert_WeekDay_Num_To_Name($day->format('N'));
		    	$Day_Of_Month=ltrim($day->format('d'), '0');
		    	$Month_Name=$this->Convert_Month_Num_To_Jalali_Name($day->format('m'));
		    	$Next_7days_Jalali_Date["jalaliDateText"][]=$Week_Day_Name." ".$Day_Of_Month." ".$Month_Name;
		    	}
		    	return $Next_7days_Jalali_Date;    		
		  	}

			private function Convert_WeekDay_Num_To_Name($WeekDay_Number){

				$WeekDay_Number_To_Name=[
					"1"=> "شنبه",
					"2"=>"یکشنبه",
					"3"=> "دوشنبه",
					"4"=>"سه شنبه",
					"5"=> "چهارشنبه",
					"6"=>"پنج شنبه",
					"7"=> "جمعه"
				];

			 return $WeekDay_Number_To_Name[$WeekDay_Number];
		    }

			private function Convert_Month_Num_To_Jalali_Name($Month_Number){
				$Month_Number_To_Name=[

					"01"=> "فروردین",
					"02"=>"اردیبهشت",
					"03"=> "خرداد",
					"04"=>"تیر",
					"05"=> "مرداد",
					"06"=>"شهریور",
					"07"=> "مهر",
					"08"=>"آبان",
					"09"=> "آذر",
					"10"=>"دی",
					"11"=> "بهمن",
					"12"=>"اسفند",
				];

				return $Month_Number_To_Name[$Month_Number];
			}

}
