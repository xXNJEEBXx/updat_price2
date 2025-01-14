<?php

namespace App\Http\Controllers;

use App\Models\status;
use Illuminate\Http\Request;
use PhpParser\Node\Expr\Print_;

class track_controller extends Controller
{
    public function track_orders($my_data)
    {

 

        $ads_data = git_data::ads_data();
        if ($ads_data->status() !== 200) {
            return  "You need to log in";
        }

 

        //proces::update_amount($my_data);
        $my_data = chack_list::set_auto_price($my_data);
        $my_data = chack_list::set_auto_amount($my_data);
        $ads_list = git_data::ads_list($my_data);


        //TURN OFF FOR NOW
        // if (chack_list::chack_max_amount($my_data)) {
        //     return  "max amount is out of amount";
        // }

        if (chack_list::chack_amount($my_data)) {
            return  "thare is no amount";
        }

        //need to add black list
        $my_payMethods=proces::git_all_paymethod();    

        if (chack_list::chack_ads($my_data, $ads_list, $my_payMethods)) {
            if (chack_list::chack_multiple_orders()) {
                return "stop opens orders";
            } else {
                proces::make_order($my_data, $ads_list, $my_payMethods);
                return "New order opened";
            }
        };

        return "Thare is no good price";
    }



    public function post_track_amount_and_price(Request $my_data)
    {
        $data = status::where('name', "track_amount")->first();
        if ($data == null) {
            $track_table1 = new  status;
            $track_table1->name = "track_amount";
            $track_table1->value = $my_data["amount"];
            $track_table1->save();
            $track_table2 = new  status;
            $track_table2->name = "track_price";
            $track_table2->value = $my_data["price"];
            $track_table2->save();
        } else {
            $track_table1 = status::where('name', "track_amount")->first();
            $track_table1->name = "track_amount";
            $track_table1->value = $my_data["amount"];
            $track_table1->save();
            // $track_table2 = status::where('name', "track_price")->first();
            // $track_table2->name = "track_price";
            // $track_table2->value = $my_data["price"];
            // $track_table2->save();
        }
        return ["amount" => $track_table1->value/*, "price" => $track_table2->value*/];
    }

    public function post_track_status(Request $my_data)
    {
        $data = status::where('name', "track_status")->first();
        if ($data == null) {
            $track_table = new  status;
            $track_table->value = 0;
        } else {
            $track_table = status::where('name', "track_status")->first();
            $track_table->value = ($track_table->value == 0 ? 1 : 0);
        }
        $track_table->name = "track_status";
        $track_table->save();

        return ["status" => $track_table->value];
    }


    public function git_track_data()
    {
        return git_data::git_track_data();
    }
}
