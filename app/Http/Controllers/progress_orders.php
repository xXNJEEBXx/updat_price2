<?php

namespace App\Http\Controllers;

use App\Models\progress_order;
use App\Models\sms_notification;
use App\Models\wise_transaction;
use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Models\binance_task;
use Carbon\Carbon;
//Google2FA
use PragmaRX\Google2FA\Google2FA;
//google api
use Google\Client;
use Google\Service\Gmail;



class progress_orders extends Controller
{
    public function chack_orders($my_data)
    {

        $ads_data = git_data::ads_data();
        if ($ads_data->status() !== 200) {
            return  "You need to log in";
        }
        //this is to store all the orders that need to be store it
        //from here
        $finshed_progress_orders = proces::get_finshed_progress_orders();
        //i have  turn off buy orders
        $progress_orders = git_data::get_progress_orders();

        do {
            $orders_need_to_store_it = proces::orders_need_to_store_it($progress_orders, $finshed_progress_orders);
            foreach ($orders_need_to_store_it as $order) {
                proces::send_progress_orders($order);
            }
            if (count($orders_need_to_store_it) > 0) {
                $finshed_progress_orders = proces::get_finshed_progress_orders();
            }
        } while (count($orders_need_to_store_it) > 0);
        //to here
        //clsoe the orders that are done
        $orders_dones = proces::get_orders_dones($progress_orders, $finshed_progress_orders);
        foreach ($orders_dones as $order) {
            $order["status"] = 1;
            proces::update_orders_status($order);
        }
        proces::chack_if_thare_is_orders_just_updated($progress_orders, $finshed_progress_orders);
        //chack transactions if they take more than 15 minites
        self::chack_transactions();
        //here we are sure that all the orders are updated to thare status and make any process they could need
        $finshed_progress_orders = proces::get_finshed_progress_orders();
        if (count($finshed_progress_orders) == 0) {
            return "no orders to chack";
        }
        $updates = Telegram::getUpdates();
        foreach ($finshed_progress_orders as $order) {
            if ($order->status == 2) {
                echo "wroung email\n";
                $massege = "Thare is wrong email for the order id=" . $order["order_id"] . " amount=" . $order["value"];
                Telegram::sendMessage([
                    'chat_id' => "438631667",
                    'text' => $massege
                ]);
                $order["status"] = 7;
                proces::update_orders_status($order);
            }
            if ($order->status == 3) {
                self::telgram_send_Validat_massge($order, $updates);
                $order["status"] = 4;
                proces::update_orders_status($order);
                echo "waiting Validat name\n";
            }
            if ($order->status == 4) {
                if (self::chack_vote_no($updates, $order)) {
                    $order["status"] = 7;
                    proces::update_orders_status($order);
                    echo "bad name cancel order\n";
                } else {
                    $now = Carbon::now();
                    if (self::chack_vote_yes($updates, $order) || $order->updated_at->diffInMinutes($now) >= 5) {
                        $order["status"] = 5;
                        proces::update_orders_status($order);
                        echo "order just accepted\n";
                    }
                }
            }
            if ($order->status == 6) {
                $order["status"] = 7;
                proces::update_orders_status($order);
                git_data::mark_order_as_paid($order);
                echo "order marked as paid\n";
            }
            //chack the transaction and send the telgram massege for validate transaction for sell orders
            // print wait it to marked as paid

            if ($order->status == 8) {
                //turn off dirctly start on num 10
                $order["status"] = 9;
                proces::update_orders_status($order);
                echo "wait buyer mark order as paid\n";
            }
            //stauts 10 will convert automatic above 

            //order marked as paid
            if ($order->status == 10) {
                self::telgram_send_Validat_transaction_massge($order);
                $order["status"] = 11;
                proces::update_orders_status($order);
                echo "send telgram name transaction Validat\n";
            }
            if ($order->status == 11) {
                $now = Carbon::now();
                //need to update with the sms
                $wise_transactions = wise_transaction::where("status", 0)->get();
                if (count($wise_transactions) > 0 || $order->updated_at->diffInMinutes($now) >= 2 || self::chack_vote_yes($updates, $order)) {
                    if (self::chack_vote_no($updates, $order)) {
                        $order["status"] = 7;
                        proces::update_orders_status($order);
                        echo "bad name cancel auto close\n";
                        //or it take more than 2 minites to pay
                    } else {
                        if (self::chack_vote_yes($updates, $order) || (proces::chack_transaction($order) && $order->updated_at->diffInMinutes($now) >= 7)) {
                            if (proces::chack_transaction($order)) {
                                echo "you take 7 minites and you have no action system accept the order autmaticly\n";
                            }
                            //send task to binance
                            git_data::send_binace_task_for_release($order);
                            $order["status"] = 12;
                            proces::update_orders_status($order);
                            echo "amount need to relesed send task\n";
                            // echo "amount relesed and order just closed\n";
                        }
                    }
                    if (!(self::chack_vote_yes($updates, $order) || (proces::chack_transaction($order) && $order->updated_at->diffInMinutes($now) >= 7))) {
                        echo "waiting transaction name Validat or the 5 minites\n";
                    }
                } else {
                    echo "waiting transaction 2 minites for the transaction arive cansole the transaction\n";
                }
            }
        }
        return "orders chack successfully";
    }

    //states types 0 wating for requests 1 wating for finsh requests




    public function telgram_send_Validat_massge($order)
    {
        $question = "are you accept about this transaction?
id :" . $order["order_id"] . "
binace name :" . $order["binace_name"] . "
wise name :" . $order["wise_name"];

        Telegram::sendPoll([
            'chat_id' => "438631667",
            'question' => $question,
            'options' => ['Yes', 'No']
        ]);
    }

    public function telgram_send_Validat_transaction_massge($order)
    {
        $wise_transactions = wise_transaction::where("status", 0)->get();
        $transactions = "";
        foreach ($wise_transactions as $wise_transaction) {
            $transactions = $transactions . "name :" . $wise_transaction->wise_name . " value :" . $wise_transaction->value . "\n";
        }
        if (count($wise_transactions) == 0) {
            $transactions = "no transactions";
        }
        $question = "is this transaction are currct??
id :" . $order["order_id"] . "
binace name :" . $order["binace_name"] . " value :" . $order["value"] . " \nwise transactions  " . $transactions;
        Telegram::sendPoll([
            'chat_id' => "438631667",
            'question' => $question,
            'options' => ['Yes', 'No']
        ]);
    }

    public function chack_transactions()
    {
        $wise_transactions = wise_transaction::where('status', 0)->get();
        foreach ($wise_transactions as $wise_transaction) {
            $now = Carbon::now();
            //finishedOn is in Seconds 
            $finishedOn = Carbon::createFromTimestamp($wise_transaction->finishedOn);
            if ($finishedOn->diffInMinutes($now) >= 15) {
                $wise_transaction->status = 1;
                $wise_transaction->save();
                echo "transaction closed\n";
            }
        }
    }

    public function chack_vote_no($updates, $order)
    {
        foreach ($updates as $update) {
            if ($update->poll) {
                $poll = $update->poll;
                $id = self::getId($poll->question);
                if ($order["order_id"] == $id) {
                    if ($poll->options[1]["voter_count"] > 0) {
                        Telegram::markUpdateAsRead($update->getUpdateId());
                        return true;
                    }
                }
            }
        }
        return false;
    }


    public function chack_vote_yes($updates, $order)
    {
        foreach ($updates as $update) {
            if ($update->poll) {
                $poll = $update->poll;
                $id = self::getId($poll->question);
                if ($order["order_id"] == $id) {
                    if ($poll->options[0]["voter_count"] > 0) {
                        Telegram::markUpdateAsRead($update->getUpdateId());
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public function getId($message)
    {
        preg_match("/id\s*:\s*(\d+)/", $message, $matches);
        return $matches[1];
    }

    public function git_progress_order()
    {
        return ["data" => self::git_progress_order_text()];
    }

    public function git_progress_order_text()
    {
        $ads_data = git_data::ads_data();
        if ($ads_data->status() !== 200) {
            return  "You need to log in";
        }
        $finshed_progress_orders = self::git_orders_for_wise();
        if (count($finshed_progress_orders) > 0) {
            $progress_orders = git_data::full_orders([1], 0);
        }

        foreach ($finshed_progress_orders as $finshed_progress_order) {
            foreach ($progress_orders as $order) {
                if ($order["orderNumber"] == $finshed_progress_order->order_id) {
                    return $finshed_progress_order;
                }
            }
        }

        return "no progress order";
    }

    public function chack_progress_order(Request $task)
    {
        return ["data" => self::chack_progress_order_text($task)];
    }

    public function chack_progress_order_text($task)
    {
        $ads_data = git_data::ads_data();
        if ($ads_data->status() !== 200) {
            return  false;
        }
        
         $progress_orders = git_data::full_orders([1], 0);
        

            foreach ($progress_orders as $order) {
                if ($order["orderNumber"] == $task["order_id"]) {
                    return true;
                }
            }
        

        return false;
    }


    public function git_progress_task()
    {
        return ["data" => self::git_progress_task_text()];
    }

    public function git_progress_task_text()
    {

        $ads_data = git_data::ads_data();
        if ($ads_data->status() !== 200) {
            return  "You need to log in";
        }
        $finshed_progress_orders = self::git_task_for_binance();
        if (count($finshed_progress_orders) > 0) {
            // 1 for sell trade type 2 for relesed orders
            $progress_orders = git_data::full_orders([2], 1);
        }

        foreach ($finshed_progress_orders as $finshed_progress_order) {
            foreach ($progress_orders as $order) {
                if ($order["orderNumber"] == $finshed_progress_order->order_id) {
                    return $finshed_progress_order;
                }
            }
        }

        return "no progress task";
    }

    public function update_progress_task(Request $data)
    {
        $data = $data->json()->all();
        $finshed_progress_orders = progress_order::where('order_id', $data["order_id"])->get()->first();
        $finshed_progress_orders->status = $data["status"];
        $finshed_progress_orders->save();
        return ["data" => "task update successfully"];
    }

    // public function git_task_for_wise()
    // {
    //     $orders_for_wise = progress_order::all();
    //     $array = [];
    //     foreach ($orders_for_wise as $order_for_wise) {
    //         if ($order_for_wise->status == "0" || $order_for_wise->status == "5") {
    //             $array[] = $order_for_wise;
    //         }
    //     }

    //     return $array;
    // }


    public function update_progress_order(Request $order)
    {
        $order = $order->json()->all();
        $finshed_progress_orders = progress_order::where('order_id', $order["order_id"])->get()->first();
        $finshed_progress_orders->status = $order["status"];
        if (isset($order["wise_name"])) {
            $finshed_progress_orders->wise_name = $order["wise_name"];
        }
        $finshed_progress_orders->save();
        return ["data" => "orders update successfully"];
    }

    public function update_transactions(Request $transactions)
    {
        $transactions = $transactions->json()->all();
        $wise_transactions = wise_transaction::all();
        foreach ($transactions as $transaction) {
            if ($transaction["category"] == "MONEY_ADDED" && self::chack_transaction_id($transaction["id"], $wise_transactions)) {
                $amount = strip_tags($transaction["primaryAmount"]);
                $amount = str_replace(array("+", "USD", " "), "", $amount);
                $table = new  wise_transaction;
                $table->wise_transaction_id = $transaction["id"];
                $table->wise_name = strip_tags($transaction["title"]);
                $table->value = $amount;
                $table->status = 0;
                $table->finishedOn = strtotime($transaction["finishedOn"]);
                $table->save();
            }
        }
        return ["data" => "transactions updated successfully"];
    }

    public function chack_transaction_id($id, $wise_transactions)
    {
        foreach ($wise_transactions as $wise_transaction) {
            if ($wise_transaction->wise_transaction_id == $id) {
                return false;
            }
        }
        return true;
    }


    public function new_sms_massage($name, $number, $message)
    {
        $table = new  sms_notification;
        $table->name = $name;
        $table->number = $number;
        $table->massage =  $message;
        $table->status = false;
        $table->save();
        return "sms saved successfully";
    }
    public function git_order_otp()
    {
        return ["data" => self::git_order_otp_text()];
    }
    public function git_order_otp_text()
    {
        $sms_notifications = sms_notification::where('name', "TW Team")->get();
        foreach ($sms_notifications as $sms_notification) {
            $now = Carbon::now();
            if ($sms_notification->created_at->diffInMinutes($now) <= 3) {
                if ($sms_notification->status == false) {
                    $sms_notification->status = true;
                    $sms_notification->save();
                    return  self::extractCode($sms_notification->massage);
                }
            }
        }
        return "no sms massage yet";
    }


    public function git_binance_email_otp()
    {
        return ["data" => self::git_binance_email_otp_text()];
    }

    public function git_binance_email_otp_text()
    {
        $client = new Client();
        $id1='761941593218-d7dfkpg688v19mcsi1bcrevsg2e4i156.apps.g';
        $id2='oogleusercontent.com';
        $id=$id1.$id2;
        $Secret1='GOCSPX-c5tkIXow3_';
        $Secret2='XIblZaHuRrk_01eI3A';
        $Secret=$Secret1.$Secret2;
        $client->setClientId($id);
        $client->setClientSecret($Secret);
        $refreshToken = '1//04sGnuPGVQpbTCgYIARAAGAQSNwF-L9IrfWEVAjF7iebtjyho3tj1-99t1OLRi-sEiuwTUcvqBgpnj7c3dt9HEBFhsIktXaUbpP0';
        $client->refreshToken($refreshToken);
        $client->setAccessType('offline');
        $accessToken = $client->fetchAccessTokenWithRefreshToken($refreshToken);

        if (isset($accessToken['error'])) {
            return 'Error fetching token: ' . $accessToken['error_description'];
        }

        $client->setAccessToken($accessToken);
        if ($client->isAccessTokenExpired()) {
            $accessToken = $client->fetchAccessTokenWithRefreshToken($refreshToken);
            $client->setAccessToken($accessToken);
        }

        $service = new Gmail($client);
        $userId = 'me';
        $results = $service->users_messages->listUsersMessages($userId, ['maxResults' => 20]);

        foreach ($results->getMessages() as $message) {
            $messageData = $service->users_messages->get($userId, $message->getId());
            $internalDate = $messageData["internalDate"];
            $currentTime = round(microtime(true) * 1000);

            if (in_array('IMPORTANT', $messageData->getLabelIds()) && strpos($messageData->getSnippet(), 'Binance Release P2P') !== false && chack_list::chack_binace_email_otp_id($messageData["id"]) && ($currentTime - $internalDate <= 5 * 60 * 1000)) {
                git_data::save_binace_email_otp_id($messageData["id"]);
                preg_match_all("/Your verification code: (\d{6})/", $messageData["snippet"], $matches);
                return $matches[1][0];
            }
        }
        return "no otp code yet";
    }

    public function git_binance_g2fa_otp()
    {
        $_g2fa = new Google2FA();
        $current_otp = $_g2fa->getCurrentOtp("6H3FTKVCRUVPEUNQ");
        return ["data" => $current_otp];
    }
    public function git_wise_login_otp()
    {
        $_g2fa = new Google2FA();
        $current_otp = $_g2fa->getCurrentOtp("BMAUAAWP2ZLPBYGMKO2GAZP6FMJCMRQQ");
        return ["data" => $current_otp];
    }




    public function git_orders_for_wise()
    {
        $orders_for_wise = progress_order::all();
        $array = [];
        foreach ($orders_for_wise as $order_for_wise) {
            if ($order_for_wise->status == "0" || $order_for_wise->status == "5") {
                $array[] = $order_for_wise;
            }
        }

        return $array;
    }


    public function git_task_for_binance()
    {
        $orders_for_binance = binance_task::all();
        $array = [];
        foreach ($orders_for_binance as $order_for_binance) {
            if ($order_for_binance->status == "0") {
                $array[] = $order_for_binance;
            }
        }

        return $array;
    }

    function extractCode($text)
    {
        // The regular expression matches any six-digit number at the end of the text
        $regex = "/(\d{6})/";
        // The preg_match() function returns 1 if the pattern matches, or 0 if it does not
        $match = preg_match($regex, $text, $matches);
        // If there is a match, return the first element of the matches array, which is the code
        if ($match) {
            return $matches[0];
        }
        // Otherwise, return null
        else {
            return null;
        }
    }
}
