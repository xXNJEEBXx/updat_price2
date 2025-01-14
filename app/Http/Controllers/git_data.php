<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use App\Models\cookie;
use App\Models\status;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Http\Client\ConnectionException;
use GuzzleHttp\Exception\RequestException;
use App\Models\finshed_otp_binane_email;
use App\Models\binance_task;
use Carbon\Carbon;


class git_data extends Controller
{

    static function catch_errors($callback)
    {
        do {
            try {
                $falg = false;
                $res = $callback();
            }
            //catch exception
            catch (QueryException $error) {
                if (!isset($error_alarm)) {
                    $error_alarm = true;
                    echo "QueryException error \n";
                }
                $falg = true;
                sleep(2);
            } catch (ConnectionException $error) {
                if (!isset($error_alarm)) {
                    $error_alarm = true;
                    echo "ConnectionExceptiong error \n";
                }
                $falg = true;
                sleep(2);
            } catch (RequestException $error) {
                if (!isset($error_alarm)) {
                    $error_alarm = true;
                    echo "RequestException error \n";
                }
                $falg = true;
                sleep(2);
            }
        } while ($falg);

        return $res;
    }
    static function heders()
    {

        $cookies = self::catch_errors(function () {
            return cookie::all()->first();
        });
        return self::heders_for_regolar($cookies);
    }
    static function heders_for_regolar($cookies)
    {
        return [
            'authority' => 'p2p.binance.com',
            'accept' => '*/*',
            'accept-language' => 'ar,en-US;q=0.9,en;q=0.8,ar-SA;q=0.7,en-GB;q=0.6',
            'bnc-uuid' => '678765ba-dc29-4e44-8f0d-a303a4fe3a63',
            'c2ctype' => 'c2c_merchant',
            'clienttype' => 'web',
            'content-type' => 'application/json',
            'cookie' => $cookies->cookies,
            'csrftoken' => $cookies->csrftoken,
            'device-info' => 'eyJzY3JlZW5fcmVzb2x1dGlvbiI6IjE5MjAsMTA4MCIsImF2YWlsYWJsZV9zY3JlZW5fcmVzb2x1dGlvbiI6IjE5MjAsMTA0MCIsInN5c3RlbV92ZXJzaW9uIjoiV2luZG93cyAxMCIsImJyYW5kX21vZGVsIjoidW5rbm93biIsInN5c3RlbV9sYW5nIjoiYXIiLCJ0aW1lem9uZSI6IkdNVCszIiwidGltZXpvbmVPZmZzZXQiOi0xODAsInVzZXJfYWdlbnQiOiJNb3ppbGxhLzUuMCAoV2luZG93cyBOVCAxMC4wOyBXaW42NDsgeDY0KSBBcHBsZVdlYktpdC81MzcuMzYgKEtIVE1MLCBsaWtlIEdlY2tvKSBDaHJvbWUvMTAwLjAuNDg5Ni42MCBTYWZhcmkvNTM3LjM2IiwibGlzdF9wbHVnaW4iOiJQREYgVmlld2VyLENocm9tZSBQREYgVmlld2VyLENocm9taXVtIFBERiBWaWV3ZXIsTWljcm9zb2Z0IEVkZ2UgUERGIFZpZXdlcixXZWJLaXQgYnVpbHQtaW4gUERGIiwiY2FudmFzX2NvZGUiOiJhNDBkZGEzMiIsIndlYmdsX3ZlbmRvciI6Ikdvb2dsZSBJbmMuIChOVklESUEpIiwid2ViZ2xfcmVuZGVyZXIiOiJBTkdMRSAoTlZJRElBLCBOVklESUEgR2VGb3JjZSBHVFggMTA2MCA2R0IgRGlyZWN0M0QxMSB2c181XzAgcHNfNV8wLCBEM0QxMSkiLCJhdWRpbyI6IjEyNC4wNDM0NzUyNzUxNjA3NCIsInBsYXRmb3JtIjoiV2luMzIiLCJ3ZWJfdGltZXpvbmUiOiJBc2lhL1JpeWFkaCIsImRldmljZV9uYW1lIjoiQ2hyb21lIFYxMDAuMC40ODk2LjYwIChXaW5kb3dzKSIsImZpbmdlcnByaW50IjoiNzhhYjQ0MjRiMDQ4M2MwNmU4M2Q5NjcyNWIxODBjYzkiLCJkZXZpY2VfaWQiOiIiLCJyZWxhdGVkX2RldmljZV9pZHMiOiIifQ==',
            'fvideo-id' => '324abb49409eabdba08ffeb52bcdcdcde24728f7',
            'lang' => 'ar',
            'origin' => 'https://p2p.binance.com',
            'referer' => 'https://p2p.binance.com/ar/myads',
            'sec-ch-ua' => '" Not A;Brand";v="99", "Chromium";v="100", "Google Chrome";v="100"',
            'sec-ch-ua-mobile' => '?0',
            'sec-ch-ua-platform' => '"Windows"',
            'sec-fetch-dest' => 'empty',
            'sec-fetch-mode' => 'cors',
            'sec-fetch-site' => 'same-origin',
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/100.0.4896.60 Safari/537.36',
            'x-trace-id' => 'ee96a687-d600-4fa3-bc1c-a674b88ad426',
            'x-ui-request-trace' => 'ee96a687-d600-4fa3-bc1c-a674b88ad426'
        ];;
    }
    static function heders_for_convert($cookies)
    {

        return [
            'authority' => 'www.binance.com',
            'method' => 'GET',
            'path' => '/bapi/margin/v2/friendly/new-otc/get-from-selector?walletType=SPOT_FUNDING&showBlock=1',
            'scheme' => 'https',
            'Accept' => '*/*',
            'Accept-Encoding' => 'gzip, deflate, br,zstd',
            'Accept-Language' => 'en-SA,en;q=0.9,ar-SA;q=0.8,ar;q=0.7,en-GB;q=0.6,en-US;q=0.5',
            'Bnc-Location' => 'BINANCE',
            'Bnc-Uuid' => '3ece6282-abad-4618-877c-e86fa082fe18',
            'Cache-Control' => 'no-cache',
            'Clienttype' => 'web',
            'Content-type' => 'application/json',
            'cookie' => $cookies->cookies,
            'csrftoken' => $cookies->csrftoken,
            'device-info' => 'eyJzY3JlZW5fcmVzb2x1dGlvbiI6IjE5MjAsMTA4MCIsImF2YWlsYWJsZV9zY3JlZW5fcmVzb2x1dGlvbiI6IjE5MjAsMTA0MCIsInN5c3RlbV92ZXJzaW9uIjoiV2luZG93cyAxMCIsImJyYW5kX21vZGVsIjoidW5rbm93biIsInN5c3RlbV9sYW5nIjoiYXIiLCJ0aW1lem9uZSI6IkdNVCszIiwidGltZXpvbmVPZmZzZXQiOi0xODAsInVzZXJfYWdlbnQiOiJNb3ppbGxhLzUuMCAoV2luZG93cyBOVCAxMC4wOyBXaW42NDsgeDY0KSBBcHBsZVdlYktpdC81MzcuMzYgKEtIVE1MLCBsaWtlIEdlY2tvKSBDaHJvbWUvMTAwLjAuNDg5Ni42MCBTYWZhcmkvNTM3LjM2IiwibGlzdF9wbHVnaW4iOiJQREYgVmlld2VyLENocm9tZSBQREYgVmlld2VyLENocm9taXVtIFBERiBWaWV3ZXIsTWljcm9zb2Z0IEVkZ2UgUERGIFZpZXdlcixXZWJLaXQgYnVpbHQtaW4gUERGIiwiY2FudmFzX2NvZGUiOiJhNDBkZGEzMiIsIndlYmdsX3ZlbmRvciI6Ikdvb2dsZSBJbmMuIChOVklESUEpIiwid2ViZ2xfcmVuZGVyZXIiOiJBTkdMRSAoTlZJRElBLCBOVklESUEgR2VGb3JjZSBHVFggMTA2MCA2R0IgRGlyZWN0M0QxMSB2c181XzAgcHNfNV8wLCBEM0QxMSkiLCJhdWRpbyI6IjEyNC4wNDM0NzUyNzUxNjA3NCIsInBsYXRmb3JtIjoiV2luMzIiLCJ3ZWJfdGltZXpvbmUiOiJBc2lhL1JpeWFkaCIsImRldmljZV9uYW1lIjoiQ2hyb21lIFYxMDAuMC40ODk2LjYwIChXaW5kb3dzKSIsImZpbmdlcnByaW50IjoiNzhhYjQ0MjRiMDQ4M2MwNmU4M2Q5NjcyNWIxODBjYzkiLCJkZXZpY2VfaWQiOiIiLCJyZWxhdGVkX2RldmljZV9pZHMiOiIifQ==',
            'Fvideo-Id' => '3208c5733dda270bd919abde5e4c2e92a370532',
            'Fvideo-Token' => 'u6BkDXV/9MSy1+7n+aXiUrAy9MPU+bieouKm7+xyazzul9R3G98j2IXkueeX9Kg0mtcSK/pkDI9PsdvBEBUAVwvQKw6wT4Pawf8hrByGdxvpTe8WfBVxVODSTOXblIv3unVtIMs0PsGcbOlA7z0AUBzM8IdhGW3ubkMQ+ZVqIeUqXNR99ELFVROUNBhQbSuTc=22',
            'Lang' => 'ar',
            'Pragma' => 'no-cache',
            'Priority' => 'u=1,i',
            'Referer' => 'https://www.binance.com/ar/convert/USDT/BTC',
            'Sec-Ch-Ua' => "'Chromium';v='124', 'Google Chrome';v='124', 'Not-A.Brand';v='99'",
            'Sec-Ch-Ua-Mobile' => '?0',
            'Sec-Ch-Ua-Platform' => 'Windows',
            'Sec-Fetch-Dest' => 'empty',
            'Sec-Fetch-Mode' => 'cors',
            'Sec-Fetch-Site' => 'same-origin',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            'X-Passthrough-Token' => '',
            'X-Trace-Id' => 'c076ecee-7605-402a-a647-d45906a3fe21',
            'X-Ui-Request-Trace' => 'c076ecee-7605-402a-a647-d45906a3fe21',
        ];
    }

    static function ads_data()
    {
        $ads_data = self::catch_errors(function () {
            return Http::withHeaders(
                self::heders()
            )->post("https://p2p.binance.com/bapi/c2c/v2/private/c2c/adv/list-by-page", ['inDeal' => 1, 'rows' => 10, 'page' => 1]);
        });
        return $ads_data;
    }

    static function ads_list($my_data)
    {
        if ($my_data["track_type"] == "choce_best_price") {
            $my_data["trade_type"] = $my_data["trade_type"] == "SELL" ? "BUY" : "SELL";
        }
        $payTypes = [];
        if (isset($my_data["payTypes"]) > 0) {
            $payTypes = $my_data["payTypes"];
        }
        $periods = [];
        if (isset($my_data["periods"]) > 0) {
            $periods = $my_data["periods"];
        }
        $countries= [];
        if (isset($my_data["countries"]) > 0) {
            $countries = $my_data["countries"];
        }
        $ads_list = self::catch_errors(function () use (&$payTypes, &$my_data, &$periods, &$countries) {
            return  Http::withHeaders(self::heders())->post("https://p2p.binance.com/bapi/c2c/v2/friendly/c2c/adv/search", ["additionalKycVerifyFilter" => 0, "asset" => $my_data["asset"], "assetClassifies" => ["mass", "profession", "fiat_trade"], "countries" => $countries, "fiat" => $my_data["fiat"], "filterType" => "all", "page" => 1, "payTypes" => $payTypes, "periods" => $periods, "proMerchantAds" => false, "publisherType" => null, "rows" => 10, "shieldMerchantAds" => false, "tradeType" => $my_data["trade_type"]]);
        });

        return $ads_list["data"];
    }

    static function change_price_req($enemy_ad, $my_ad_data, $my_data)
    {
        print_r(self::paylode_for_change_price($enemy_ad, $my_ad_data, $my_data));
        $res = self::catch_errors(function () use ($enemy_ad, $my_ad_data, $my_data) {
            return  Http::withHeaders(self::heders())->post("https://p2p.binance.com/bapi/c2c/v3/private/c2c/adv/update", self::paylode_for_change_price($enemy_ad, $my_ad_data, $my_data));
        });
        return "ok";
    }

    static function ad_data($ads_data, $my_data)
    {
        foreach ($ads_data["data"] as $my_ad_data) {
            if ($my_ad_data["advNo"] == $my_data["id"]) {
                return $my_ad_data;
            }
        }
    }

    static function enemy_ad($ads_list, $my_data, $my_ad_data)
    {
        foreach ($ads_list as $ad_data) {
            if (chack_list::chack_ad($ad_data, $my_data, $my_ad_data)) {
                return $ad_data;
            }
        }
    }

    static function new_price($my_data, $enemy_ad)
    {
        return $enemy_ad["adv"]["price"] + proces::difference_value($my_data);
    }

    static function paylode_for_change_price($enemy_ad, $my_ad_data, $my_data)
    {
        //$initAmount=self::total_initAmount($enemy_ad, $my_ad_data, $my_data);
        $initAmount = $my_ad_data["initAmount"];
        $paylode = [
            "adAdditionalKycVerifyItems" => $my_ad_data["adAdditionalKycVerifyItems"],
            "adTags" => [],
            "advNo" => $my_ad_data["advNo"],
            "advStatus" => $my_ad_data["advStatus"],
            "assetScale" => $my_ad_data["assetScale"],
            "asset" => $my_ad_data["asset"],
            "autoReplyMsg" => $my_ad_data["autoReplyMsg"],
            "buyerBtcPositionLimit" => $my_ad_data["buyerBtcPositionLimit"],
            "buyerRegDaysLimit" => $my_ad_data["buyerRegDaysLimit"],
            "classify" => $my_ad_data["classify"],
            "fiatScale" => $my_ad_data["fiatScale"],
            "fiatUnit" => $my_ad_data["fiatUnit"],
            "initAmount" => $initAmount,
            "isSafePayment" => $my_ad_data["isSafePayment"],
            "launchCountry" => $my_ad_data["launchCountry"],
            "maxSingleTransAmount" => $my_ad_data["maxSingleTransAmount"],
            "minSingleTransAmount" => $my_ad_data["minSingleTransAmount"],
            "onlineDelayTime" => 0,
            "onlineNow" => true,
            "payTimeLimit" => $my_ad_data["payTimeLimit"],
            "price" => self::new_price($my_data, $enemy_ad),
            "priceFloatingRatio" => $my_ad_data["priceFloatingRatio"],
            "priceScale" => $my_ad_data["priceScale"],
            "priceType" => $my_ad_data["priceType"],
            "remarks" => $my_ad_data["remarks"],
            "takerAdditionalKycRequired" => $my_ad_data["takerAdditionalKycRequired"],
            "tradeMethods" => $my_ad_data["tradeMethods"],
            "tradeType" => $my_ad_data["tradeType"],
            "visible" => 1,
        ];
        return $paylode;
    }


    static function total_initAmount($enemy_ad, $my_ad_data, $my_data)
    {
        if ($my_data["trade_type"] == "BUY") {
            $my_data["crupto_amount"] = $my_data["track_amount"] / self::new_price($my_data, $enemy_ad);
        }
        $my_data["crupto_amount"] += self::total_transefer_amount($my_ad_data);
        $my_data["crupto_amount"] = $my_data["crupto_amount"] * 0.999;
        if ($my_ad_data["asset"] == "USDT") {
            return  round($my_data["crupto_amount"], 2, PHP_ROUND_HALF_DOWN);
        } else {
            return  round($my_data["crupto_amount"], 8, PHP_ROUND_HALF_DOWN);
        }
    }
    static function total_transefer_amount($my_ad_data)
    {
        return   $my_ad_data["initAmount"] - $my_ad_data["tradableQuantity"];
    }


    static function ad_amount($my_ad_data)
    {
        return   round($my_ad_data["tradableQuantity"] * $my_ad_data["price"], 2, PHP_ROUND_HALF_DOWN);
    }


    static function ad_msta($ad_amount)
    {
        if ($ad_amount <= 350) {
            return 200;
        }


        if ($ad_amount <= 500) {
            return 300;
        }

        if ($ad_amount <= 1000) {
            return 500;
        }

        if ($ad_amount <= 1900) {
            return 1000;
        }

        if ($ad_amount <= 2500) {
            return 1500;
        }


        if ($ad_amount <= 5000) {
            return 2000;
        }


        if ($ad_amount <= 7000) {
            return 3000;
        }


        if ($ad_amount <= 12000) {
            return 4000;
        }
    }

    static function ad_own($ad_amount)
    {
        if ($ad_amount <= 500) {
            return 60;
        }
        if ($ad_amount <= 1000) {
            return 400;
        }

        if ($ad_amount <= 1900) {
            return 900;
        }

        if ($ad_amount <= 2500) {
            return 1000;
        }

        if ($ad_amount <= 5000) {
            return 1000;
        }

        if ($ad_amount <= 7000) {
            return 1000;
        }

        if ($ad_amount <= 12000) {
            return 2500;
        }
    }
    //@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@track orders& closes orders@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@


    static function git_my_payMethods_from_binance()
    {
        $my_paymthods = self::catch_errors(function () {
            return Http::withHeaders(self::heders())->withBody('{}', 'application/json')->post("https://p2p.binance.com/bapi/c2c/v2/private/c2c/pay-method/user-paymethods");
        });
        return $my_paymthods["data"];
    
    }


    static function git_track_data()
    {

        $data = self::catch_errors(function () {
            return status::where('name', "track_amount")->first();
        });
        if ($data == null) {
            $track_table = new  status;
            $track_table->name = "track_amount";
            $track_table->value = 0;
            self::catch_errors(function () use ($track_table) {
                $track_table->save();
            });
            $track_table = new  status;
            $track_table->name = "track_status";
            $track_table->value = 0;
            self::catch_errors(function () use ($track_table) {
                $track_table->save();
            });
        } else {
            $data3 = self::catch_errors(function () {
                return  status::where('name', "track_status")->first();
            });
            return ["amount" => $data["value"], "status" => $data3["value"]];
        }
    }

    static function track_status()
    {
        $data = status::where('name', "track_status")->first();
        return  $data["value"];
    }



    static function orginal_price($my_data)
    {

        $data = self::catch_errors(function () {
            return Http::withHeaders(self::heders())->get("https://www.binance.com/bapi/composite/v1/public/marketing/symbol/list");
        });
        foreach ($data["data"] as $element) {
            if ($my_data["fiat"] == "SAR") {
                $element["price"] = $element["price"] * 3.75;
            }
            if ($element["name"] == $my_data["asset"]) {
                $my_data["price"] = $element["price"] * $my_data["price_multiplied"];
                $my_data["orginal_price"] = $element["price"];
            }
        }
        return  $my_data;
    }

    static function traked_ad($my_data, $ads_list, $my_payMethods)
    {
        foreach ($ads_list as $ad) {
            if (chack_list::chack_ad_for_track($my_data, $ad, $my_payMethods)) {
                $traked_ad = self::catch_errors(function () use ($ad) {
                    return  Http::withHeaders(self::heders())->get("https://p2p.binance.com/bapi/c2c/v2/public/c2c/adv/selected-adv/" . $ad["adv"]["advNo"]);
                });
                return $traked_ad["data"];
            }
        }
    }

    static function open_order_req($my_data, $traked_ad, $my_payMethods)
    {
        
        print_r($traked_ad);
        print_r(self::paylode_for_open_order($my_data, $traked_ad, $my_payMethods));
        self::catch_errors(function () use ($my_data, $traked_ad,$my_payMethods) {
            Http::withHeaders(self::heders())->post("https://p2p.binance.com/bapi/c2c/v3/private/c2c/order-match/placeOrder", self::paylode_for_open_order($my_data, $traked_ad, $my_payMethods));
        });
    }
    //need to add all payTypes
    static function paylode_for_open_order($my_data, $traked_ad, $my_payMethods)
    {
        $pay_methed = self::pay_methed($my_data, $traked_ad, $my_payMethods);

        //need to re check
        return ["advOrderNumber" => $traked_ad["adv"]["advNo"],"area"=>"p2pZone","asset" => $my_data["asset"], "buyType" => "BY_MONEY","channel"=>"c2c" , "fiatUnit" => $my_data["fiat"],"matchPrice" => $traked_ad["adv"]["price"], "origin" => "MAKE_TAKE", "payId" => $pay_methed["payId"], "payType" => $pay_methed["identifier"], "totalAmount" => self::total_amount($my_data, $traked_ad), "tradeType" => $my_data["trade_type"]];
    }

    static function total_amount($my_data, $traked_ad)
    {
        $buy_amount = ($my_data["orginal_price"] * $my_data["track_amount"]) / $traked_ad["adv"]["price"];
        if ($buy_amount >= self::MSA($traked_ad)) {
            $amount = self::MSA($traked_ad);
        } else {
            $amount = $buy_amount;
        }
        if (isset($my_data["buy_the_lowist"]) && $my_data["buy_the_lowist"]) {
            if ($amount > $traked_ad["adv"]["minSingleTransAmount"]) {
                $amount = $traked_ad["adv"]["minSingleTransAmount"];
            }
        }
        return round($amount, 2, PHP_ROUND_HALF_DOWN);
    }

    // static function selled_amount($my_data, $traked_ad)
    // {
    //     $buy_amount = ($my_data["orginal_price"] * $my_data["track_amount"]) / $traked_ad["adv"]["price"];
    //     if ($buy_amount >= self::MSA($traked_ad)) {

    //         $selled_amount = ($traked_ad["adv"]["price"] * self::MSA($traked_ad)) / $my_data["orginal_price"];
    //     } else {
    //         $selled_amount = $my_data["track_amount"];
    //     }
    //     return round($selled_amount, 2, PHP_ROUND_HALF_DOWN);
    // }


    static function pay_methed($my_data, $traked_ad, $my_payMethods)
    {
        // foreach ($traked_ad["adv"]["tradeMethods"] as $pay_methed) {
        //     foreach ($my_data["payTypes"] as $payType) {
        //         if ($pay_methed["identifier"] == $payType) {
        //             if ($my_data["trade_type"] == "SELL") {
        //                 if ($pay_methed["identifier"] == "Wise") {
        //                     $pay_methed["payId"] = 33677319;
        //                 }
        //             }
        //             return $pay_methed;
        //         }
        //     }
        // }
        foreach ($traked_ad["adv"]["tradeMethods"] as $payMethod) {
            foreach ($my_payMethods as $my_payMethod) {
                foreach ($my_payMethod["supported_paymethod"] as $supported_paymethod) {
                    if ($payMethod["identifier"] == $supported_paymethod["identifier"]) {
                        if ($my_data["trade_type"] == "SELL") {
                            return  $supported_paymethod;
                        }else{
                            return  $payMethod;
                        }
                    }
                }
            }
        }

    }

    static function send_massge($telegram_massge)
    {
        self::catch_errors(function () use ($telegram_massge) {
            Http::withHeaders(self::heders())->get("https://api.telegram.org/bot5546910942:AAFWrAYCeosx1x3x2K9HE5tpKagTwE-M0bI/sendMessage?chat_id=438631667,&text=" . $telegram_massge);
        });
    }

    static function MSA($traked_ad)
    {
        if ($traked_ad["adv"]["maxSingleTransAmount"] > $traked_ad["adv"]["surplusAmount"] * $traked_ad["adv"]["price"]) {
            return $traked_ad["adv"]["surplusAmount"] * $traked_ad["adv"]["price"];
        } else {
            return $traked_ad["adv"]["maxSingleTransAmount"];
        }
    }

    static function track_amount($my_data)
    {
        $my_data["free_amount"] = 0;
        $wallet_amount = self::git_wallet_amount();
        if ($my_data["trade_type"] == "BUY") {
            $my_data = self::set_track_buy_amount($my_data);
            $my_data = self::set_progress_orders_amount($my_data);
        } else {
            //Sell
            $my_data = self::set_free_amount_and_track_amount($my_data, $wallet_amount);
        }
        $my_data = self::set_max_amount($my_data, $wallet_amount);
        $my_data["track_amount"] = round($my_data["track_amount"], 2, PHP_ROUND_HALF_DOWN);
        return $my_data;
    }
    static function set_track_buy_amount($my_data)
    {
        $track_table = self::catch_errors(function () {
            return   status::where('name', "track_amount")->first();
        });
        $my_data["track_amount"] = $track_table["value"];
        return $my_data;
    }
    static function git_wallet_amount()
    {
        return self::catch_errors(function () {
            return  Http::withHeaders(self::heders())->post("https://www.binance.com/bapi/asset/v3/private/asset-service/asset/get-ledger-asset", ["needBtcValuation" => true, "quoteAsset" => "BTC"]);
        });
    }

    static function set_free_amount_and_track_amount($my_data, $wallet_amount)
    {
        foreach ($wallet_amount["data"] as $crupto) {
            if ($crupto["asset"] == $my_data["asset"]) {
                //convert crupto to usd amount
                if (isset($my_data["track_amount"])) {
                    $my_data["track_amount"] += $crupto["free"] * $my_data["orginal_price"];
                } else {
                    $my_data["track_amount"] = $crupto["free"] * $my_data["orginal_price"];
                }
                $my_data["free_amount"] = $crupto["free"];
                return $my_data;
            }
        }
    }

    static function set_max_amount($my_data, $wallet_amount)
    {
        if (isset($my_data["max_amount"])) {
            foreach ($wallet_amount["data"] as $crupto) {
                if ($crupto["asset"] == $my_data["asset"]) {
                    $my_data["free_amount"] = $crupto["free"];
                    //mybe it good for buys tracks
                    //  $my_data["max_amount"] -= ($crupto["free"] + $crupto["freeze"]) * $my_data["orginal_price"];
                }
            }
            if ($my_data["track_amount"] > $my_data["max_amount"]) {
                $my_data["track_amount"] = $my_data["max_amount"];
            }
        }
        return $my_data;
    }

    static function get_progress_orders()
    {
        //5
        $progress_orders = [];
        $all_orders = git_data::progress_orders();
        foreach ($all_orders as $order) {
            //4 complete orders 6 canceled orders
            if ($order["tradeType"] == "BUY") {
             //   $progress_orders[] = $order;
            } else {
                if ($order["orderStatus"] == 2) {
                    $progress_orders[] = $order;
                }
            }
        }
        return $progress_orders;
    }


    static function set_progress_orders_amount($my_data)
    {

        $progress_orders = self::progress_orders();
        foreach ($progress_orders as $order) {
            if ($order["tradeType"] == "BUY") {
                $my_data["track_amount"] -= $order["totalPrice"];
            }
        }

        return $my_data;
    }

    static function full_orders($orderStatusList, $tradetype)
    {
        $GMT_time = time() - (3600 * 3);
        $end_time = (((($GMT_time - ($GMT_time % 86400)) + 86400 * 1) - (3600 * 3)) * 1000) - 1;
        $start_time = (($end_time + 1) - 86400 * 1000);
        $carbon = Carbon::createFromTimestamp($start_time / 1000);
        $start_time = $carbon->subMonths(3);
        $start_time = strtotime($start_time) * 1000;
        $paylode = ["page" => 1, "rows" => 10, "orderStatusList" => $orderStatusList, "startDate" => $start_time, "endDate" => $end_time];
        if ($tradetype != "all orders") {
            $paylode = array_merge($paylode, ["tradeType" => $tradetype]);
        }
        $full_orders = self::catch_errors(function () use ($paylode) {

            return Http::withHeaders(self::heders())->post("https://p2p.binance.com/bapi/c2c/v1/private/c2c/order-match/order-list-archived-involved", $paylode);
        });
        return $full_orders["data"];
    }





    //@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ progress_orders @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@


    static function progress_orders()
    {
        $processing_ads_list = self::catch_errors(function () {
            return Http::withHeaders(self::heders())->post("https://p2p.binance.com/bapi/c2c/v2/private/c2c/order-match/order-list", ["page" => 1, "rows" => 10, "orderStatusList" => [0, 1, 2, 3, 5]]);
        });

        return $processing_ads_list["data"];
    }

    static function mark_order_as_paid($order)
    {
        self::catch_errors(function () use ($order) {
            Http::withHeaders(self::heders())->post("https://p2p.binance.com/bapi/c2c/v1/private/c2c/order-match/notifyOrderPayed", ["orderNumber" => $order["order_id"], "payId" => $order["pay_id"]]);
        });
        return "done";
    }

    static function save_binace_email_otp_id($id)
    {
        $table = new  finshed_otp_binane_email;
        $table->otp_id = $id;
        $table->save();
    }

    static function send_binace_task_for_release($order)
    {
        $table = new binance_task();
        $table->order_id = $order["order_id"];
        $table->status = 0;
        $table->type = "release";
        $table->save();
    }

    static function get_binance_sell_order_name($order)
    {
        $ad_info = self::catch_errors(function () use ($order) {
            $paylode = ["createTime" => $order["createTime"], "orderNumber" => $order["orderNumber"]];
            return Http::withHeaders(self::heders())->post("https://p2p.binance.com/bapi/c2c/v2/private/c2c/order-match/order-detail", $paylode);
        });

        return $ad_info["data"]["buyerName"];
    }
    // @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ convert asset @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

    static function get_assets($my_data)
    {
        $data = self::catch_errors(function () use ($my_data) {
            return Http::withHeaders(self::heders($my_data))->get("https://www.binance.com/bapi/margin/v2/friendly/new-otc/get-from-selector?walletType=SPOT_FUNDING&showBlock=1");
        });
        //  print_r($data["data"]);
        return $data["data"]["toList"];
    }

    static function get_quote($my_data, $free_usdt)
    {
        $data = self::catch_errors(function () use ($my_data, $free_usdt) {
            return Http::withHeaders(self::heders($my_data))->post("https://www.binance.com/bapi/margin/v1/private/new-otc/get-quote", ["allowBlock" => 1, "fromCoin" => "USDT", "requestAmount" => "1,095", "requestCoin" => "USDT", "toCoin" => "NOT", "walletType" => "SPOT_FUNDING"]);
        });
        return $data["data"];
    }

    static function execute_quote($my_data, $quote)
    {
        $data = self::catch_errors(function () use ($my_data, $quote) {
            return Http::withHeaders(self::heders($my_data))->post("https://www.binance.com/bapi/margin/v1/private/new-otc/execute-quote", ["quoteId" => $quote["quoteId"]]);
        });
        return $data["data"];
    }

    static function make_covert_order($my_data, $execute_quote)
    {
        $data = self::catch_errors(function () use ($my_data, $execute_quote) {
            return Http::withHeaders(self::heders($my_data))->get("https://www.binance.com/bapi/margin/v1/private/new-otc/query-trade-order?orderId=" . $execute_quote["orderId"]);
        });
        return $data["data"];
    }

    static function get_quote_for_chack_login()
    {
        $data = self::catch_errors(function () {
            return Http::withHeaders(self::heders())->get("https://www.binance.com/bapi/asset/v2/private/asset-service/asset/get-user-asset");
        });

        return $data;
    }

    //@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@track prices system@@@@@@@@@@@@@@@@
    static function get_crupto_pricec_from_marketcup()
    {
        // $data = self::catch_errors(function () {
        //     return Http::get("https://api.coinmarketcap.com/data-api/v3/cryptocurrency/listing?start=1&limit=10000&sortBy=market_cap&sortType=desc&convert=USD,BTC,ETH&cryptoType=all&tagType=all&audited=false&aux=ath,atl,high24h,low24h,num_market_pairs,cmc_rank,date_added,max_supply,circulating_supply,total_supply,volume_7d,volume_30d,self_reported_circulating_supply,self_reported_market_cap&marketCapRange=10000000~");
        // });
        // return $data["data"];
        // https://pro-api.coinmarketcap.com/v1/cryptocurrency/quotes/latest?symbol=BTC,ETH&convert=USDT
        $data = self::catch_errors(function () {
            return Http::withHeaders(["X-CMC_PRO_API_KEY" => "69ef9c0d-2c54-4b6f-a447-0ae6f410824b"])->get("https://pro-api.coinmarketcap.com/v1/cryptocurrency/quotes/latest?symbol=BTC,ETH,LTC,XRP,EOS,ADA,DOT,SOL,UNI,LINK,DOGE,BNB,AVAX,MATIC,ATOM,NEAR,ALGO,TRX,FTM,XLM,MANA,ICP,SAND,VET,XTZ,FIL,EGLD,AXS,KLAY,HNT,THETA&convert=USD");
        });
        return $data["data"];
    }
}
