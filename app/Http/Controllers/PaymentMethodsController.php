<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\paymethod;
use App\Models\supported_paymethod;
use App\Models\status;
use App\Http\Controllers\proces;
use App\Http\Controllers\git_data;

class PaymentMethodsController extends Controller
{
    /**
     * Reset all payment methods usage count to 0.
     */
    public function resetUsageCount()
    {
        try {
            // Update all records in paymethods table
            $updatedRows = paymethod::query()->update(['number_of_use' => '0']);
            
            return response()->json([
                'success' => true,
                'message' => 'All payment methods usage count reset to 0 successfully',
                'updated_records' => $updatedRows
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reset usage count',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update local payment methods list by comparing with Binance remote methods.
     */
    public function changepaymethods($my_data)
    {

        // Fetch the current ad data from Binance
        $ads_data = git_data::ads_data();
        // Check if the request was successful

        if ($ads_data->status() !== 200) {
            return "You need to log in";
        }
        // refresh tracked balances
        proces::update_amount($my_data);
        // fetch available pay methods
        $my_payMethods = proces::git_all_paymethod($my_data);
        $my_data["payTypes"] = $my_payMethods;
        $my_ad_Methods = git_data::make_my_ad_paymetods($my_data);;

        // load current ad info
        $my_ad_data = git_data::ad_data($ads_data, $my_data);
        if (chack_list::chack_ad_status($my_ad_data)) {
            return  "ad is turn off in binance";
        }

        if (chack_list::chack_payMethods($my_data)) {
            return  "Thare is no avilable payMethods";
        }
        //need to chack if the amount biger then the min amount
        $my_data = chack_list::set_auto_price($my_data);
        $my_data = chack_list::set_amount_for_ads($my_data, $my_ad_data);

        if (chack_list::chack_amount_for_ads_post($my_data, $my_ad_data)) {
            return  "ad out of amount";
        }
        // check if paymethods need to be updated
        $ad_payMethods = $my_ad_data['tradeMethods']; // Fixed: correct path to tradeMethods
        $need_update = false;

        // Check if the ad payment methods match exactly what should be available

        // print_r($ad_payMethods);
        // print_r($my_ad_Methods);


        foreach ($ad_payMethods as $method) {
            $flag2 = false;

            foreach ($my_ad_Methods as $my_method) {
                if ($method['payMethodId'] == $my_method['payMethodId']) {
                    $flag2 = true; // Found a matching method
                    break; // No need to check further for this method
                }
            }
            if (!$flag2) {
                $need_update = true; // Found a method that is not in the local list
                break; // No need to check further
            }
        }


        if ($need_update) {
            // Update payment methods for the ad
            $result = git_data::update_ad_paymethods($my_ad_data, $my_data);
            return "Payment methods updated for the ad";
        }
        return "Payment methods are already up to date";
    }
}
