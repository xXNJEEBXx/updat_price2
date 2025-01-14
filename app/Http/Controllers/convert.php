<?php

namespace App\Http\Controllers;



use Illuminate\Http\Request;

class convert extends Controller
{
    public function convert($my_data)
    {
        $ads_data = git_data::ads_data();

        if ($ads_data->status() !== 200) {
            return "You need to log in";
        }

        // $free_usdt = 0;
        // $spot_wallet_assets = 0;
        // do {
        //     do {
        //         $spot_wallet_assets = git_data::get_assets($my_data);
        //         // if (chack_list::chack_if_it_has_NOT($spot_wallet_assets)) {
        //         //     echo "NOT did't finded yet\n";
        //         // }
        //     } while (chack_list::chack_if_it_has_NOT($spot_wallet_assets));
        //     echo "'NOT' found\n";
        //     $free_usdt = proces::get_usdt_amount_from_spot_wallet($spot_wallet_assets);
        //     if ($free_usdt < 1000) {
        //         echo "no usdt amount\n";
        //     }
        // } while ($free_usdt < 1000);
        echo "usdt amount:" . 0 . "\n";
        $quote = git_data::get_quote($my_data, 0);
        print_r($quote);
        $execute_quote = git_data::execute_quote($my_data, $quote);
        print_r($execute_quote);
        $order = git_data::make_covert_order($my_data, $execute_quote);
        print_r($order);
        return "order completed";


        echo "hello\n";
    }
    //
}
