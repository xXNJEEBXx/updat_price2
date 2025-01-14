<?php


namespace App\Http\Controllers;



use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Models\cookie;
use App\Models\status;


class ApiController extends Controller
{
    public function changprics_api()
    {
        return $this->changprics("11329712394179661824");
    }

    public function changprics($my_data)
    {
        // $my_data = ["name" => "SELL USDT AD", "price_multiplied" => 1.005, "id" => "11489302371517079552", "price_type" => "auto", "asset" => "USDT", "fiat" => "USD", "track_type" => "choce_best_price", "trade_type" => "SELL",  "payTypes" => "Wise"];



        $ads_list = git_data::ads_list($my_data);


        $ads_data = git_data::ads_data();
        if ($ads_data->status() !== 200) {
            return "You need to log in";
        }

        $my_ad_data = git_data::ad_data($ads_data, $my_data);
        print_r($my_ad_data);
        //proces::update_amount($my_data);
        $my_data = chack_list::set_auto_price($my_data);

        $my_data = proces::add_defult_ad_amount($my_data, $my_ad_data, $ads_list);
        $my_data = chack_list::set_amount_for_ads($my_data,$my_ad_data);
      // $my_data = proces::add_crupto_amount($my_data, $my_ad_data);

        if (chack_list::chack_ad_status($my_ad_data)) {
            return  "ad is turn off in binance";
        }

        if (chack_list::chack_max_amount($my_data)) {
            return  "max_amount is out of amount";
        }
        //need to chack if the amount biger then the min amount


        if (chack_list::chack_amount($my_data)) {
            return  "ad out of amount";
        }

        if (chack_list::chack_min($my_data,$my_ad_data)) {
            return  "min amount is biger then the amount";
        }

        if (chack_list::chack_full_list($ads_list, $my_data, $my_ad_data)) {
            return  "all ads bad";
        }

        if (chack_list::chack_up_njeeb($ads_list, $my_data, $my_ad_data)) {
            //Ad price need to reduction
            proces::change_price($ads_list, $my_ad_data, $my_data);
            return  "ad price reduction from " . $my_ad_data["price"] . " to " . git_data::new_price($my_data, git_data::enemy_ad($ads_list, $my_data, $my_ad_data));
        }

        if (chack_list::chack_down_njeeb($ads_list, $my_data, $my_ad_data)) {
            //Ad price need to incress
            proces::change_price($ads_list, $my_ad_data, $my_data);
            return  "ad price increesed from " . $my_ad_data["price"] . " to " . git_data::new_price($my_data, git_data::enemy_ad($ads_list, $my_data, $my_ad_data));
        }
        if (chack_list::chack_the_best($ads_list, $my_data, $my_ad_data)) {
            return  "ad have best price";
        }
        return  "test";
    }

    public function postcookies(Request $request)
    {
        $request = $request->json()->all();
        $data = cookie::get()->first();
        if ($data == null) {
            $cookie_table = new  cookie;
            $cookie_table->cookies = $request["cookies"];
            $cookie_table->csrftoken = $request["csrftoken"];
            $cookie_table->save();
        } else {
            $cookie_table = cookie::where('id', "1")->first();
            $cookie_table->cookies = $request["cookies"];
            $cookie_table->csrftoken = $request["csrftoken"];
            $cookie_table->save();
        }
        return  ["data" => $this->getdate($cookie_table->updated_at)];
    }


    public function getlastupdate()
    {
        $cookie_table = cookie::where('id', "1")->first();
        if ($cookie_table != null) {
            return  $this->getdate($cookie_table->updated_at);
        }
        return "test";
    }

    public function getdate($date)
    {
        $secands = (time() - strtotime($date));
        function loop($secands, $minets, $hours)
        {
            if ($secands >= 3600) {
                $secands2 = $secands % 3600;
                $hours = ($secands - $secands2) / 3600;
                return loop($secands2, $minets, $hours);
            }
            if ($secands >= 60) {
                $secands2 = $secands % 60;
                $minets  = ($secands - $secands2) / 60;
                return loop($secands2, $minets, $hours);
            }
            if ($hours) {
                $hours = $hours . " hours ";
            } else {
                $hours = "";
            }
            if ($minets) {
                $minets = $minets . " minets ";
            } else {
                $minets = "";
            }
            if ($secands) {
                $secands = $secands . " secands ";
            } else {
                $secands = "";
            }
            if (!$secands && !$minets && !$hours) {
                $secands = 1;
            }
            return $hours . $minets . $secands;
        }
        return loop($secands, 0, 0);
    }

    public function poststatus(Request $request)
    {
        $data = status::get()->first();
        if ($data == null) {
            $status_table = new  status;
            $status_table->name = $request["name"];
            $status_table->status = 1;
            $status_table->save();
        } else {
            $status_table = status::where('name', $request["name"])->first();
            $status_table->name = $request["name"];
            if ($status_table->status) {
                $status_table->status = 0;
            } else {
                $status_table->status = 1;
            }
            $status_table->save();
        }
        return  $status_table->status;
    }

    public function getstatus()
    {
        $data = status::get()->first();
        if ($data != null) {
            return  $data->status;
        }
    }

    public function get_crupto_pricec_from_marketcup()
    {
        return git_data::get_crupto_pricec_from_marketcup();
    }
}
