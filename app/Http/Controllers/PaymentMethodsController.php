<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\paymethod;
use App\Models\supported_paymethod;

class PaymentMethodsController extends Controller
{
    /**
     * Change payment methods for advertisements based on usage/performance
     * This function updates the payment methods of an advertisement dynamically
     * 
     * @param array $data Configuration data containing ad ID and payment method settings
     * @return mixed Response from the Binance API or error message
     */
    public function changepayMethods($data)
    {
        // Get current ads data from Binance
        $ads_data = git_data::ads_data();

        // Check if user is logged in
        if ($ads_data->status() !== 200) {
            return "You need to log in";
        }

        // Get the specific advertisement data
        $my_ad_data = git_data::ad_data($ads_data, $data);

        // Get all available payment methods
        $my_payMethods = proces::git_all_paymethod($data);
        $data["payTypes"] = $my_payMethods;

        // Update the advertisement with new payment methods
        echo "Updating payment methods for ad: " . $data["name"] . "\n";
        $response = git_data::update_ad_paymethods($my_ad_data, $data);

        if ($response->status() === 200) {
            echo "Payment methods updated successfully!\n";
        } else {
            echo "Failed to update payment methods\n";
        }

        return $response;
    }
}
