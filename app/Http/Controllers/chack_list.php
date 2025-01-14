<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\status;
use App\Models\finshed_otp_binane_email;



class chack_list extends Controller
{
    static function chack_ad_status($my_ad_data)
    {
        if ($my_ad_data["advStatus"] == 1) {
            return false;
        } else {
            return true;
        }
    }


    static function chack_ad($ad, $my_data, $my_ad_data)
    {
        if ($my_ad_data["tradeType"] == "BUY") {
            if (round($ad["adv"]["price"], 8) > round($my_data["price"], 8)) {

                return false;
            }
        } else {
            if (round($ad["adv"]["price"], 8) < round($my_data["price"], 8)) {
                return false;
            }
        }
        if ($ad["advertiser"]["nickName"] == "NJEEB") {
            return false;
        }
        if ($ad["adv"]["minSingleTransAmount"] > git_data::ad_msta($my_data["track_amount"])) {


            return false;
        }
        // chack if the diffrince is biger than 100
        // if (($ad["adv"]["maxSingleTransAmount"] - $ad["adv"]["minSingleTransAmount"]) < 100) {
        //     return false;
        // }
        // // chack if he has at lees 1000
        // if (($ad["adv"]["surplusAmount"] * $ad["adv"]["price"]) < git_data::ad_msta($my_data["track_amount"])) {
        //     echo "surplusAmount\n";
        //     return false;
        // }
        return true;
    }


    static function chack_full_list($ads_list, $my_data, $my_ad_data)
    {
        foreach ($ads_list as $ad) {

            if (self::chack_ad($ad, $my_data, $my_ad_data)) {
                return false;
            }
        }
        return true;
    }

    static function chack_amount($my_data)
    {
        $min_amount_for_usdt = 0;
        $min_amount_for_BTC= 0;
        if ($my_data["fiat"] == "USD") {
            $min_amount_for_usdt = 100;
            $min_amount_for_BTC = 10;
        } else {
            $min_amount_for_usdt = 60;
            $min_amount_for_BTC = 60;
        }
        echo $my_data["track_amount"]."\n";
        if ($my_data["asset"] == "USDT" || $my_data["asset"] == "BUSD") {
            if ($my_data["track_amount"] < $min_amount_for_usdt) {
                return true;
            }
        } else {
            if ($my_data["track_amount"] < $min_amount_for_BTC) {
                return true;
            }
        }
        return false;
    }

    static function chack_min($my_data, $my_ad_data)
    {
        if ($my_ad_data["minSingleTransAmount"] > $my_data["track_amount"]) {
            return true;
        }
        return false;
    }


    static function chack_max_amount($my_data)
    {
        //this is only for the BUY's ads and for open orders buy
        if (isset($my_data["max_amount"])) {
            if ($my_data["asset"] == "USDT") {
                if ($my_data["max_amount"] < 100) {
                    return true;
                }
            } else {
                if ($my_data["max_amount"] < 10) {
                    return true;
                }
            }
        }
        return false;
    }




    static function chack_up_njeeb($ads_list, $my_data, $my_ad_data)
    {
        foreach ($ads_list as $ad) {
            if ($ad["advertiser"]["nickName"] != "NJEEB") {
                if (self::chack_ad($ad, $my_data, $my_ad_data)) {
                    return true;
                }
            } else {
                return false;
            }
        }
    }



    public static function chack_down_njeeb($ads_list, $my_data, $my_ad_data)
    {
        $flag = false;
        $NJEEB = 0;
        for ($i = 0; $i < sizeof($ads_list); $i++) {
            if ($flag) {
                if (self::chack_ad($ads_list[$i], $my_data, $my_ad_data)) {
                    $num1 = round($ads_list[$i]["adv"]["price"] + proces::difference_value($my_data), proces::difference_index($my_data));
                    $num2 = round($ads_list[$NJEEB]["adv"]["price"], proces::difference_index($my_data));
                    if ($num1 == $num2) {
                        return false;
                    } else {
                        return true;
                    }
                }
            }
            if ($ads_list[$i]["advertiser"]["nickName"] == "NJEEB") {
                $flag = true;
                $NJEEB = $i;
            }
        }
    }

    static function chack_the_best($ads_list, $my_data, $my_ad_data)
    {
        if (!self::chack_up_njeeb($ads_list, $my_data, $my_ad_data) && !self::chack_down_njeeb($ads_list, $my_data, $my_ad_data)) {
            return true;
        } else {
            return false;
        }
    }










    //@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ chack tracks @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

    static function chack_track_status()
    {
        $track_status = git_data::track_status();
        if ($track_status == 1) {
            return false;
        } else {
            return true;
        }
    }

    static function chack_ad_for_track($my_data, $ad, $my_payMethods)
    {
        if ($my_data["trade_type"] == "BUY") {
            if ($ad["adv"]["price"] > $my_data["price"]) {
                return false;
            }
            if ($ad["adv"]["minSingleTransAmount"] > git_data::total_amount($my_data, $ad)) {
                return false;
            }
            if (!$ad["adv"]["isTradable"]) {
                return false;
            }
        } else {

            if ($ad["adv"]["price"] < $my_data["price"]) {
                return false;
            }
            if ($ad["adv"]["minSingleTransAmount"] > git_data::total_amount($my_data, $ad)) {
                return false;
            }
            if (!$ad["adv"]["isTradable"]) {
                return false;
            }
            if (self::chack_target_ad_paymetods($ad, $my_payMethods)) {
                return false;
            }
        }

        return true;
    }



    static function chack_ads($my_data, $ads_list, $my_payMethods)
    {
        foreach ($ads_list as $ad) {
            if (self::chack_ad_for_track($my_data, $ad, $my_payMethods)) {
                return true;
            }
        }
        return false;
    }


    static function set_auto_price($my_data)
    {
        if ($my_data["price_type"] == "auto") {

            $my_data = git_data::orginal_price($my_data);
        }
        return $my_data;
    }

    static function set_amount_for_ads($my_data,$my_ad_data)
    {
        //$my_data = git_data::track_amount($my_data, $my_ad_data);
        $my_data["track_amount"] =$my_ad_data["initAmount"] * $my_data["orginal_price"];
        return $my_data;
    }


    static function set_auto_amount($my_data)
    {
        $my_data = git_data::track_amount($my_data);
        // $my_data["track_amount"] =$my_ad_data["initAmount"] * $my_data["orginal_price"];
        return $my_data;
    }

    static function chack_multiple_orders()
    {
        $progress_orders = git_data::progress_orders();
        if (count($progress_orders) >= 2) {
            return true;
        }
        return false;
    }

    static function chack_target_ad_paymetods($ad, $my_payMethods)
    {
        foreach ($ad["adv"]["tradeMethods"] as $payMethod) {
            foreach ($my_payMethods as $my_payMethod) {
                foreach ($my_payMethod["supported_paymethod"] as $supported_paymethod) {
                    if ($payMethod["identifier"] == $supported_paymethod["identifier"]) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    static function chack_binace_email_otp_id($id)
    {
        $data = finshed_otp_binane_email::where('otp_id', $id)->first();
        if ($data == null) {
            return true;
        } else {
            return false;
        }
    }
    // @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ convert asset @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

    static function chack_if_it_has_NOT($spot_wallet_assets)
    {
        print_r($spot_wallet_assets);
        foreach ($spot_wallet_assets as $coin) {
            if ($coin["coin"] == "NOT") {
                return true;
            }
        }
        return false;
    }
}
