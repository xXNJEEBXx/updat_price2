<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Http;
use Illuminate\Console\Command;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\track_controller;
use App\Http\Controllers\progress_orders;
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
                //["name" => "SELL BTC AD", "price_multiplied" => 1.015, "id" => "11496911875305992192", "price_type" => "auto", "asset" => "BTC", "fiat" => "USD", "track_type" => "choce_best_price", "trade_type" => "SELL", "payTypes" => "Wise"],
                //["name" => "SELL BTC AD with SAR", "price_multiplied" => 1.1, "id" => "12704852398681194496", "price_type" => "auto", "asset" => "BTC", "fiat" => "SAR", "track_type" => "choce_best_price", "trade_type" => "SELL",/*this is only for the search*/ "payTypes" => ["stcpay"]],
                // ["name" => "SELL USDT AD", "price_multiplied" => 1.013, "id" => "11539542195302817792", "price_type" => "auto", "asset" => "USDT", "fiat" => "USD", "track_type" => "choce_best_price", "trade_type" => "SELL",  "payTypes" => "Wise"],
                //---BUY
                // ["name" => "BUY USDT AD", "price_multiplied" => 1.011, "id" => "11506316506589458432", "price_type" => "auto", "asset" => "USDT", "fiat" => "USD", "track_type" => "choce_best_price", "trade_type" => "BUY", "max_amount" => 1000, "payTypes" => "Wise"],
                //["name" => "BUY BUSD AD", "price_multiplied" => 1.005, "id" => "11471976478695088128", "price_type" => "auto", "asset" => "BUSD", "fiat" => "USD", "track_type" => "choce_best_price", "trade_type" => "BUY", "max_amount" => 150, "payTypes" => "Wise"],

                //--------------good_dule-------------------------
                //---BUY
                //["name" => "BUY BTC track", "price_multiplied" => 1.005, "asset" => "BTC", "fiat" => "USD", "track_type" => "good_dule", "max_amount" => 50, "buy_the_lowist" => true, "payTypes" => "Wise", "price_type" => "auto", "trade_type" => "BUY"],
                //["name" => "BUY USDT track", "price_multiplied" => 1.005, "asset" => "USDT", "fiat" => "USD", "track_type" => "good_dule", "max_amount" => 1000, "buy_the_lowist" => true, "payTypes" => "Wise", "price_type" => "auto", "trade_type" => "BUY"],
                //["name" => "BUY BUSD track", "price_multiplied" => 1.005, "asset" => "BUSD", "fiat" => "USD", "track_type" => "good_dule", "max_amount" => 100, "buy_the_lowist" => true, "payTypes" => "Wise", "price_type" => "auto", "trade_type" => "BUY"],
                // ---SELL
                //["name" => "SELL BTC track", "price_multiplied" => 1.034, "asset" => "BTC", "fiat" => "USD", "track_type" => "good_dule", "payTypes" => "Wise", "price_type" => "auto", "trade_type" => "SELL"],
                //["name" => "SELL BTC with SAR track", "price_multiplied" => 1.15, "asset" => "BTC", "fiat" => "SAR", "track_type" => "good_dule", "payTypes" => ["stcpay"],"periods"=>[15, 30],"countries"=>["SA"], "price_type" => "auto", "trade_type" => "SELL", "max_amount" => 70],
               // ["name" => "SELL USDT with SAR track", "price_multiplied" => 1.03, "asset" => "USDT", "fiat" => "SAR", "track_type" => "good_dule", "payTypes" => ["stcpay"],"periods"=>[15, 30],"countries"=>["SA"], "price_type" => "auto", "trade_type" => "SELL", "max_amount" => 70],
                // ["name" => "SELL USDT track", "price_multiplied" => 1.013, "asset" => "USDT", "fiat" => "USD", "track_type" => "good_dule", "payTypes" => "Wise", "price_type" => "auto", "trade_type" => "SELL"],


                // --------------pading_ads-------------------------
                 ["name" => "chack progress orders", "track_type" => "pading_ads"]


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
    public function make_req($data){
        if ($data["track_type"] == "choce_best_price") {
            $ApiController = new ApiController;
            $req = $ApiController->changprics($data);
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

        return ["secands" => $secands, "last_req" => $req];
    }
}
