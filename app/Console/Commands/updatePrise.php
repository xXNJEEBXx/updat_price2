<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Http;
use Illuminate\Console\Command;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\track_controller;
use App\Http\Controllers\progress_orders;
use App\Http\Controllers\PaymentMethodsController;
use App\Http\Controllers\convert;


class updatePrise extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:update_prise';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to run the program';




    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $commands =
            [
                //--------------choce_best_price-------------------
                //---sell
                //["name" => "SELL BTC AD with SAR", "price_multiplied" => 1.06, "max_price_multiplied" => 1.08, "id" => "12704852398681194496", "price_type" => "auto", "asset" => "BTC", "fiat" => "SAR", "track_type" => "choce_best_price", "trade_type" => "SELL", "fiat_coverter_to_usd" => 3.75],
                ["name" => "SELL USD AD with SAR", "price_multiplied" => 1.06, "id" => "12786359076646486016", "track_type" => "paymethods_change", "fiat" => "SAR", "asset" => "USDT", "trade_type" => "SELL", "price_type" => "auto", "fiat_coverter_to_usd" => 3.75],

                //---BUY

                //--------------good_dule-------------------------
                //---BUY
                //["name" => "BUY USDT with BHD track", "price_multiplied" => 1.00, "asset" => "USDT", "fiat" => "BHD", "track_type" => "good_dule","periods"=>[15, 30],"countries"=>[],"search_amount"=>100, "price_type" => "auto", "trade_type" => "BUY" ,"fiat_coverter_to_usd" => 0.379],
                //["name" => "BUY BTC with BHD track", "price_multiplied" => 1.00, "asset" => "BTC", "fiat" => "BHD", "track_type" => "good_dule","periods"=>[15, 30],"countries"=>[],"search_amount"=>100, "price_type" => "auto", "trade_type" => "BUY" ,"fiat_coverter_to_usd" => 0.379],

                // ---SELL
                //["name" => "SELL BTC with SAR track", "price_multiplied" => 1.09, "asset" => "BTC", "fiat" => "SAR", "track_type" => "good_dule","periods"=>[15, 30],"countries"=>["SA"],"search_amount"=>2000, "price_type" => "auto", "trade_type" => "SELL", "max_amount" => 4000 ,"fiat_coverter_to_usd" => 3.75],
                //["name" => "SELL USDT with SAR track", "price_multiplied" => 1.07, "asset" => "USDT", "fiat" => "SAR", "track_type" => "good_dule","periods"=>[15, 30],"countries"=>["SA"],"search_amount"=>500, "price_type" => "auto", "trade_type" => "SELL", "max_amount" => 4000,"fiat_coverter_to_usd" => 3.75],


                // --------------pading_ads-------------------------
                //["name" => "chack progress orders", "track_type" => "pading_ads"]


                // --------------convert-------------------------
                // ["name" => "convert_USDT_to_NAT", "track_type" => "convert", "my_data" => ["heder" => "convert"]]
            ];
        while (1) {
            $time = microtime(true);
            for ($i = 0; $i < count($commands); $i++) {
                if (!isset($commands[$i]["req_info"])) {
                    $commands[$i]["req_info"] = ["secands" => 0, "last_req" => null];
                }
                if ($time >= $commands[$i]["req_info"]["secands"]) {
                    $commands[$i]["req_info"] = $this->make_req($commands[$i]);
                }
            }


            sleep(1);
        }
        return Command::SUCCESS;
    }
    public function make_req($data)
    {
        if ($data["track_type"] == "choce_best_price") {
            $ApiController = new ApiController;
            $req = $ApiController->changprics($data);
        }

        if ($data["track_type"] == "paymethods_change") {
            $paymentMethodsController = new PaymentMethodsController;
            $req = $paymentMethodsController->changepayMethods($data);
        }
        if ($data["track_type"] == "good_dule") {
            $trackController = new track_controller;
            $req = $trackController->track_orders($data);
        }

        if ($data["track_type"] == "pading_ads") {
            $progressController = new progress_orders;
            $req = $progressController->chack_orders($data);
        }

        if ($data["track_type"] == "convert") {
            $convertController = new convert;
            $req = $convertController->convert($data["my_data"]);
        }

        if ($req != $data["req_info"]["last_req"]) {
            echo ($data["name"] . ":" . $req . "\n");
            // info($req);
        }


        $secands = microtime(true);
        if ($req == "ad is turn off in binance") {
            $secands = $secands + 200;
        }
        if ($req == "ad have best price") {
            $secands = $secands + 75;
        }
        if ($req == "You need to log in") {
            $secands = $secands + 60;
        }
        if ($req == "all ads bad") {
            $secands = $secands + 400;
        }
        if ($req == "max_amount is out of amount") {
            $secands = $secands + 4000;
        }
        if ($req == "ad out of amount") {
            $secands = $secands + 120;
        }
        if ($req == "New order opened") {
            $secands = $secands + 75;
        }
        if ($req == "no orders to chack") {
            $secands = $secands + 10;
        }
        if ($req == "order completed") {
            $secands = $secands + 1000;
        }
        //SELL BTC AD with SAR:ad price

        if (strpos($req, 'ad price') !== false) {
            $secands = $secands + 20;
        }
        if ($req == "Thare is no good price") {
            $secands = $secands + 5;
        }
        if ($req == "Payment methods are already up to date") {
            $secands = $secands + 200;
        }

        return ["secands" => $secands, "last_req" => $req];
    }
}
