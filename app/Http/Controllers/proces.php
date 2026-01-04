<?php

namespace App\Http\Controllers;

use App\Models\finshed_orders;
use Illuminate\Http\Request;
use App\Models\status;
use App\Models\progress_order;
use App\Models\wise_transaction;
use PhpParser\Node\Expr\Isset_;
use App\Models\paymethod;
use App\Models\supported_paymethod;
use Monolog\Handler\IFTTTHandler;
use Telegram\Bot\Laravel\Facades\Telegram;

class proces extends Controller
{

    static function add_defult_ad_amount($my_data, $my_ad_data, $ads_list)
    {
        $my_data["track_amount"] = 0;
        if ($my_ad_data["tradeType"] == "SELL") {
            $my_data["track_amount"] = $my_ad_data["surplusAmount"] * $my_data["orginal_price"];
        }

        return $my_data;
    }
    static function add_crupto_amount($my_data, $my_ad_data)
    {
        if (isset($my_data["max_amount"]) && ($my_data["max_amount"] < $my_data["track_amount"])) {
            $my_data["crupto_amount"] = $my_data["max_amount"] / $my_data["orginal_price"];
        } else {
            $my_data["crupto_amount"] = $my_data["free_amount"] + $my_ad_data["tradableQuantity"];
        };
        return $my_data;
    }



    static function difference_value($my_data)
    {
        if ((($my_data["asset"] == "USDT") || ($my_data["asset"] == "BUSD")) && ($my_data["fiat"] = "USD")) {
            if ($my_data["trade_type"] == "SELL") {
                return -0.001;
            } else {
                return 0.001;
            }
        } else {
            if ($my_data["trade_type"] == "SELL") {
                return -0.01;
            } else {
                return 0.01;
            }
        }
    }
    static function difference_index($my_data)
    {
        if (($my_data["asset"] == "USDT") && ($my_data["fiat"] = "USD")) {
            return 3;
        } else {
            return 2;
        }
    }
    static function change_price($ads_list, $my_ad_data, $my_data)
    {

        $enemy_ad = git_data::enemy_ad($ads_list, $my_data, $my_ad_data);
        return git_data::change_price_req($enemy_ad, $my_ad_data, $my_data);
        return "success";
    }

    static function make_order($my_data, $ads_list, $my_payMethods)
    {
        $traked_ad = git_data::traked_ad($my_data, $ads_list, $my_payMethods);
        git_data::open_order_req($my_data, $traked_ad, $my_payMethods);

        //send massge telegram
        $telegram_massge = self::make_new_order_massge_countent($my_data, $traked_ad, $my_payMethods);
        git_data::send_massge($telegram_massge);
    }


    static function update_amount($my_data)
    {

        $full_orders = git_data::full_orders([4], "SELL");
        $track_table = status::where('name', "track_amount")->first();
        $paynethod_table = paymethod::all();
        $supported_paymethod_table = supported_paymethod::all();
        $finshed_orders = finshed_orders::all();
        foreach ($full_orders as $order) {
            if ($order["tradeType"] == "BUY") {
                if ($track_table->updated_at->valueOf() < $order["createTime"]) {
                    if (self::array_any($finshed_orders, $order["orderNumber"])) {
                        $table = new  finshed_orders;
                        $table->order_id = $order["orderNumber"];
                        $table->save();
                        $selled_amount = $order["totalPrice"];
                        $track_table = status::where('name', "track_amount")->first();
                        $track_table->value = $track_table->value - $selled_amount;
                        $track_table->save();
                        echo "update_amount\n";
                    }
                }
            }
            if ($order["tradeType"] == "SELL") {
                if (self::array_any($finshed_orders, $order["orderNumber"])) {
                    foreach ($order["payMethods"] as $payMethod) {
                        if ($payMethod["payMethodId"] == $order["selectedPayId"]) {
                            foreach ($supported_paymethod_table as $supported_paymethod) {
                                if ($supported_paymethod->paymethod_id == $payMethod["payMethodId"]) {
                                    foreach ($paynethod_table as $paymethod) {
                                        if ($paymethod->id == $supported_paymethod->paymethod_name_id) {
                                            //update number_of_use
                                            $table = new  finshed_orders;
                                            $table->order_id = $order["orderNumber"];
                                            $table->save();
                                            $paymethod->number_of_use = $paymethod->number_of_use + 1;
                                            $paymethod->save();
                                            echo "update number of use for " . $paymethod["name"] . "\n";
                                            //i want too add here cofifrmation for the payment that customer pick if it is not the same as the one in the order
                                            //if it is not the same i will send poll massge to the telegram to fix it manually
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $selected_paymethod_name = "";
                    $options = [];
                    //get the order payment methods
                    foreach ($order["payMethods"] as $payMethod) {
                        foreach ($supported_paymethod_table as $supported_paymethod) {
                            if ($supported_paymethod->paymethod_id == $payMethod["payMethodId"]) {
                                foreach ($paynethod_table as $paymethod) {
                                    if ($paymethod->id == $supported_paymethod->paymethod_name_id) {
                                        if ($supported_paymethod->paymethod_id == $order["selectedPayId"]) {
                                            $selected_paymethod_name = $paymethod["name"];
                                        }
                                        if ($paymethod->id == $supported_paymethod->paymethod_name_id) {
                                            $options[] = $paymethod["name"];
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if (count($options) > 1) {
                        Telegram::sendPoll([
                            'chat_id' => "438631667",
                            'question' => "Is the payment method correct? " . $selected_paymethod_name . " ? if not please select the correct one",
                            //list the payment methods from the order
                            'options' => $options

                        ]);
                    }
                }
            }
        }
        Telegram::deleteWebhook(); // Delete webhook to allow getUpdates
        $updates = Telegram::getUpdates();
        foreach ($updates as $update) {
            // echo "update\n";
            //check if the update is for our poll if it start with "Is the payment method correct?" 
            if (isset($update["poll"]) && isset($update["poll"]["question"]) && str_contains($update["poll"]["question"], "Is the payment method correct?")) {
                //check if the poll is closed
                //error_log("Poll found: " . $update["poll"]["question"] . " - Is closed: " . ($update["poll"]["is_closed"] ? 'true' : 'false')); // Added logging
                $options = $update["poll"]["options"];
                $selected_index = -1;
                $max_votes = 0;

                // Find the option with the most votes
                foreach ($options as $index => $option) {
                    if ($option["voter_count"] > $max_votes) {
                        $max_votes = $option["voter_count"];
                        $selected_index = $index;
                    }
                }
                if ($selected_index != -1) {
                    //get the poll answer(the selected payment method)
                    // Get the poll options and votes

                    $selected_paymethod = $options[$selected_index]["text"];

                    // Extract the payment method name from the question
                    $question = $update["poll"]["question"];
                    $start_pos = strlen("Is the payment method correct? ");
                    $end_pos = strpos($question, " ? if not please select the correct one");
                    $question_paymethod_name = substr($question, $start_pos, $end_pos - $start_pos);

                    //update the number of use for the payment methods
                    foreach ($paynethod_table as $paymethod) {
                        if ($paymethod["name"] == $question_paymethod_name) {
                            $paymethod->number_of_use = $paymethod->number_of_use - 1;
                            $paymethod->save();
                        }
                        if ($paymethod["name"] == $selected_paymethod) {
                            $paymethod->number_of_use = $paymethod->number_of_use + 1;
                            $paymethod->save();
                            echo "Change the payment method from " . $question_paymethod_name . " to " . $selected_paymethod . "\n";
                        }
                    }
                }
            }
            Telegram::markUpdateAsRead($update->getUpdateId());
        }
        return "work";
    }

    static function array_any($array, $id)
    {
        foreach ($array as $key) {
            if ($key["order_id"] == $id) {
                return false;
            }
        }
        return true;
    }


    static function make_new_order_massge_countent($my_data, $traked_ad, $my_payMethods)
    {
        $telegram_massge = "
You have a new " . $my_data["trade_type"] . " P2P Order \n
time:" . $traked_ad["adv"]["payTimeLimit"] . " min\n
price:" . $traked_ad["adv"]["price"] . "\n
amount:" . git_data::total_amount($my_data, $traked_ad) . $my_data["fiat"] .  " \n
payment method:" . git_data::pay_methed($my_data, $traked_ad, $my_payMethods)["identifier"] . "\n
remarks:" . $traked_ad["adv"]["remarks"];
        return $telegram_massge;
    }

    static function send_progress_orders($order)
    {
        $table = new  progress_order;
        $payment = self::git_payment($order);
        $table->order_id = $order["orderNumber"];
        $table->payment = $payment["tradeMethodName"];
        $table->type = $order["tradeType"];
        $table->status = 0;
        $table->value = $order["totalPrice"];
        if ($order["tradeType"] == "BUY") {
            $name = self::git_name($payment);
            $table->binace_name = $name;
            $email = self::git_email($payment);
            if (!$email) {
                $email = "no email";
            }
            $table->email = $email;
            $pay_id = $payment["payMethodId"];
            $table->pay_id = $pay_id;
        } else {
            $table->binace_name = git_data::get_binance_sell_order_name($order);
            $table->status = 10;
        }
        if ($order["orderStatus"] == 5) {
            $table->status = 7;
        }
        $table->save();
        echo "progress order added\n";
    }

    static function get_orders_dones($progress_orders, $finshed_progress_orders)
    {
        $orders_dones = [];
        foreach ($finshed_progress_orders as $finshed_order) {
            $flag = true;
            foreach ($progress_orders as $order) {
                if ($order["orderNumber"]  == $finshed_order["order_id"]) {
                    $flag = false;
                }
            }
            if ($flag) {
                $orders_dones[] = $finshed_order;
            }
        }
        return $orders_dones;
    }

    static function update_orders_status($order)
    {
        $table = progress_order::where('order_id', $order["order_id"])->first();
        $table->status = $order["status"];
        $table->save();

        echo "order closed\n";
    }

    static function git_name($payment)
    {
        foreach ($payment["fields"] as $field) {
            if ($field["fieldName"]  == "Name") {
                return $field["fieldValue"];
            }
        }
    }
    //need to remove functions
    static function convert_binance_paymethod($payMethod_from_binance)
    {
        $payMethod = [];
        $payMethod["payId"] = $payMethod_from_binance["id"];
        $payMethod["payMethodId"] = $payMethod_from_binance["payMethodId"];
        $payMethod["payType"] = $payMethod_from_binance["payType"];
        $payMethod["payAccount"] = $payMethod_from_binance["payAccount"];
        $payMethod["payBank"] = $payMethod_from_binance["payBank"];
        $payMethod["paySubBank"] = $payMethod_from_binance["paySubBank"];
        $payMethod["identifier"] = $payMethod_from_binance["identifier"];
        $payMethod["iconUrlColor"] = $payMethod_from_binance["iconUrlColor"];
        $payMethod["tradeMethodName"] = $payMethod_from_binance["tradeMethodName"];
        $payMethod["tradeMethodShortName"] = $payMethod_from_binance["tradeMethodShortName"];
        $payMethod["tradeMethodBgColor"] = $payMethod_from_binance["tradeMethodBgColor"];
        return $payMethod;
    }

    static function git_all_paymethod($my_data = ["fiat" => "SAR"])
    {
        $paymethods  = [];
        if ($my_data["fiat"] == "BHD") {
            $paymethods = ["BENEFITPAY"];
            return $paymethods;;
        }
        $payMethods_from_binance = git_data::git_my_payMethods_from_binance();

        $my_paymethods = paymethod::all();
        $supported_paymethods = supported_paymethod::all();

        foreach ($my_paymethods as $my_paymethod) {
            $paymethods[$my_paymethod->id] = $my_paymethod->toArray();
            $paymethods[$my_paymethod->id]['supported_paymethod'] = [];
        }

        foreach ($my_paymethods as $my_paymethod) {
            foreach ($supported_paymethods as $supported_paymethod) {
                if ($my_paymethod->id == $supported_paymethod->paymethod_name_id) {
                    $paymethods[$my_paymethod->id]['supported_paymethod'][] = $supported_paymethod->toArray();
                }
            }
        }

        foreach ($paymethods as $paymethodId => $paymethod) {
            foreach ($paymethod['supported_paymethod'] as $key => $supported_paymethod) {
                foreach ($payMethods_from_binance as $payMethod_from_binance) {
                    if ($payMethod_from_binance["payMethodId"] == $supported_paymethod["paymethod_id"]) {
                        $paymethods[$paymethodId]['supported_paymethod'][$key] = self::convert_binance_paymethod($payMethod_from_binance);
                    }
                }
            }
        }
        //
        if (isset($my_data["track_type"]) && ($my_data["track_type"] == "paymethods_change" || $my_data["track_type"] == "choce_best_price")) {

            $min_usage = PHP_INT_MAX;
            foreach ($paymethods as $pm_item) {
                if ($pm_item['number_of_use'] < $min_usage) {
                    $min_usage = $pm_item['number_of_use'];
                }
            }


            if ($min_usage < 6) {
                foreach ($paymethods as $key => $paymethod) {
                    if ($paymethod['number_of_use'] > $min_usage) {
                        unset($paymethods[$key]);
                    }
                }
            }
            //print_r($paymethods);
        }


        foreach ($paymethods as $key => $paymethod) {
            if ($paymethod['number_of_use'] >= 6) {
                unset($paymethods[$key]);
            }
        }

        return $paymethods;
    }

    static function git_payment($order)
    {
        $my_payMethods = proces::git_all_paymethod();



        foreach ($order["payMethods"] as $payMethod) {
            foreach ($my_payMethods as $my_payMethod) {
                foreach ($my_payMethod["supported_paymethod"] as $supported_paymethod) {
                    if ($payMethod["payMethodId"] == $supported_paymethod["payMethodId"]) {
                        return $payMethod;
                    }
                }
            }
        }
    }

    static function git_email($payment)
    {
        foreach ($payment["fields"] as $field) {
            if ($field["fieldName"]  == "Email Address") {
                return $field["fieldValue"];
            }
        }
    }

    static function orders_need_to_store_it($progress_orders, $finshed_progress_orders)
    {
        $orders_need_to_store_it = [];
        foreach ($progress_orders as $order) {
            $flag = true;
            foreach ($finshed_progress_orders as $finshed_order) {
                if ($order["orderNumber"]  == $finshed_order["order_id"]) {
                    $flag = false;
                }
            }
            if ($flag) {
                $orders_need_to_store_it[] = $order;
            }
        }
        return $orders_need_to_store_it;
    }

    static function orders_finshed($progress_orders, $finshed_progress_orders)
    {
        $orders_need_to_store_it = [];
        foreach ($progress_orders as $order) {
            $flag = false;
            foreach ($finshed_progress_orders as $finshed_order) {
                if ($order["orderNumber"]  == $finshed_order["order_id"]) {
                    $flag = true;
                }
            }
            if ($flag) {
                $orders_need_to_store_it[] = $order;
            }
        }
        return $orders_need_to_store_it;
    }

    static function real_progress_orders($progress_orders, $finshed_progress_orders)
    {
        $real_progress_orders = [];
        foreach ($finshed_progress_orders as $finshed_order) {
            $flag = false;
            foreach ($progress_orders as $order) {
                if ($order["orderNumber"]  == $finshed_order["order_id"]) {
                    $flag = true;
                }
            }
            if ($flag) {
                $real_progress_orders[] = $order;
            }
        }
        return $real_progress_orders;
    }

    static function get_finshed_progress_orders()
    {
        $finshed_progress_orders = progress_order::all();
        $finshed_progress_orders_array = [];

        foreach ($finshed_progress_orders as $finshed_order) {
            if ($finshed_order->status != 1) {
                $finshed_progress_orders_array[] = $finshed_order;
            }
        }
        return $finshed_progress_orders_array;
    }

    static function chack_if_thare_is_orders_just_updated($progress_orders, $finshed_progress_orders)
    {
        foreach ($progress_orders as $order) {
            foreach ($finshed_progress_orders as $finshed_order) {
                if ($order["orderNumber"]  == $finshed_order["order_id"]) {
                    if (($order["orderStatus"] == 2) && ($finshed_order["status"] == 9)) {
                        $finshed_order["status"] = 10;
                        self::update_orders_status($finshed_order);
                        echo "the buyer marked the order as paid\n";
                    }
                }
            }
        }
    }
    //no need delete it
    static function update_transactions($finshed_progress_orders)
    {
        $wise_transactions = wise_transaction::where("status", 0)->get();


        foreach ($wise_transactions as $wise_transaction) {
            if (self::chack_value($wise_transaction, $finshed_progress_orders)) {
                $wise_transaction["status"] = 1;
                $wise_transaction->save();
                echo "transaction closed\n";
            }
        }
    }
    static function chack_value($wise_transaction, $finshed_progress_orders)
    {
        foreach ($finshed_progress_orders as $finshed_order) {
            if ($wise_transaction["value"] == $finshed_order["value"]) {
                return true;
            }
        }
        return false;
    }
    static function chack_transaction($order)
    {
        $wise_transactions = wise_transaction::where("status", 0)->get();
        foreach ($wise_transactions as $wise_transaction) {
            if ($wise_transaction["value"] == $order["value"]) {
                return true;
            }
        }
        return false;
    }

    static function close_transaction($order)
    {
        $wise_transactions = wise_transaction::where("status", 0)->get();
        foreach ($wise_transactions as $wise_transaction) {
            if ($wise_transaction["value"] == $order["value"]) {
                $wise_transaction["status"] = 1;
                $wise_transaction->save();
                echo "transaction closed\n";
            }
        }
    }
    // @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ convert asset @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

    static function get_usdt_amount_from_spot_wallet($spot_wallet_assets)
    {
        foreach ($spot_wallet_assets as $asset) {
            if ($asset["asset"] == "USDT") {
                return $asset["free"];
            }
        }
        return 0;
    }
}