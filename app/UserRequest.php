<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserRequest extends Model
{
    
      protected $table='RequestsTable';


      public function Save_Request_To_DB($Request_DB_Table,$chat_id,$from,$to,$date,$first_name,$last_name,$price) {

      	
      	// $db=new UserRequest;

    	// $Request_DB_Table->Is_Ok=True;
    	// $Request_DB_Table->update_id=$Message_Object->update_id;
    	// $Request_DB_Table->Message_Id=$Message_Object->message->message_id;
    	 $Request_DB_Table->From_Id=$chat_id;
    	// $Request_DB_Table->From_Is_Bot=$Message_Object->callback_query->from->is_bot;
    	 $Request_DB_Table->From_First_Name=$first_name;
    	 $Request_DB_Table->From_Last_Name=$last_name;
        // $Request_DB_Table->From_Language_Code=$Message_Object->callback_query->from->language_code;
        $Request_DB_Table->Trip_From=$from;
        $Request_DB_Table->Trip_To=$to;
        $Request_DB_Table->Trip_Jalali_Date=$date;
        $Request_DB_Table->Cheapest_Price=$price;
        // $Request_DB_Table->Trip_Jalali_Month=2;
        // $Request_DB_Table->Trip_Jalali_Year=1397;

        $result=$Request_DB_Table->save();
        return $result;
      }
}
