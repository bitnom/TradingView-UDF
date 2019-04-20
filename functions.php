<?php

function api_ping(){
    return api_json([
    	"ip" => $_SERVER["REMOTE_ADDR"],
		"x-forwarded-for" => $_SERVER["HTTP_X_FORWARDED_FOR"],
		"http-client-ip" => $_SERVER["HTTP_CLIENT_IP"]
    ]);
}

function api_config(){
    return api_json([
		"supported_resolutions" => ["1", "5", "15", "30", "60", "1D", "1W", "1M"],
		"supports_group_request" => false,
		"supports_marks" => false,
		"supports_search" => true,
		"supports_timescale_marks" => false
    ]);
}

function api_get_exchange_name($symbol){
    if(strpos($symbol, ":") === false) return api_error('[Error] Cannot get exchange name');
    return (explode(":", $symbol)[0]);
}

function api_get_symbol_name($symbol){
    if(strpos($symbol, ":") === false) return api_error('[Error] Cannot get exchange name');
    return  (explode(":", $symbol)[1]);
}

function api_symbols(){
	$array = api_check(["symbol"]);
    $exchangeName = api_get_exchange_name($array["symbol"]);
    $symbol = api_get_symbol_name($array["symbol"]);
    $exchangeClass = "\\ccxt\\".strtolower($exchangeName);
    $exchange = new $exchangeClass  ([
        "verbose" => false,
        "timeout" => 30000,
    ]);
    $result = $exchange->fetch_ticker ($symbol);
    $array["name"] = $array["symbol"];
    $array["ticker"] = $array["symbol"];
    $array["description"] = $array["symbol"];
    $array["type"] = "crypto";
    $array["session"] = "24x7";
    $array["exchange"] = $exchangeName;
    $array["listed_exchange"] = $exchangeName;
    $array["timezone"] = "Etc/UTC";
    $array["minmov"] = 0;
    $array["pricescale"] = 0;
    $array["minmove2"] = "";
    $array["fractional"] = "";
    $array["has_intraday"] = true;
    $array["supported_resolutions"] = ["1", "5", "15", "30", "60", "1D", "1W", "1M"];
    $array["intraday_multipliers"] = [];
    $array["has_seconds"] = false;
    $array["seconds_multipliers"] = [];
    $array["has_daily"] = true;
    $array["has_weekly_and_monthly"] = true;
    $array["has_empty_bars"] = true;
    $array["force_session_rebuild"] = true;
    $array["has_no_volume"] = false;
    $array["volume_precision"] = 0;
    $array["data_status"] = "streaming";
    $array["expired"] = false;
    $array["industry"] = "crypto";
    $array["currency_code"] = $array["symbol"];
    return api_json($array);
}

function api_search(){
	$array = api_check(["query", "type", "exchange", "limit"]);
    $symbol = $array["symbol"];
    $exchangeClass = "\\ccxt\\".strtolower($exchangeName);
    $exchange = new $exchangeClass  ([
        "verbose" => false,
        "timeout" => 30000,
    ]);
    $result = $exchange->fetch_ticker ($symbol);
    $array["symbol"] = $result["info"]["symbol"];
    $array["full_name"] = $result["symbol"];
    $array["description"] = $result["info"]["symbol"];
    $array["exchange"] = $exchangeName;
    $array["type"] = "crypto";
    return api_json($array);
}

function api_history(){
	$array = api_check(["symbol", "from", "to", "resolution"]);
    $resolution = $array["resolution"];
    if($resolution == "D" || $resolution == "W" || $resolution == "M"){
        $resolution = "1".strtolower($resolution);
    }
    $limit = ($array["to"]*1 - $array["from"]*1) / 60;
    if($resolution == "1d"){
        $limit = ($array["to"] - $array["from"]) / 60 / 60 / 24;
    }
    if($resolution == "1w"){
        $limit = ($array["to"] - $array["from"]) / 60 / 60 / 24 / 7;
    }
    if($resolution == "1m"){
        $limit = ($array["to"] - $array["from"]) / 60 / 60 / 24 / 7 / 4;
    }
    $exchangeClass = "\\ccxt\\".strtolower(api_get_exchange_name($array["symbol"]));
    $exchange = new $exchangeClass ([
        "enableRateLimit" => true,
        //"verbose" => true,
    ]);
    $markets = $exchange->load_markets();
    $ohlcv = $exchange->fetchOHLCV(api_get_symbol_name($array["symbol"]), $resolution, $array["from"], 10);
    $array = $exchange->convert_ohlcv_to_trading_view($ohlcv);
    $array["s"] = "ok";
    return api_json($array);
}

function api_quotes(){
	$array = api_check(["symbols"]);
    $symbols = explode(",", $array["symbols"]);
    $symbol = $array["symbol"];

    $exchangeClass = "\\ccxt\\".strtolower($exchangeName);
    $exchange = new $exchangeClass  ([
        "verbose" => true,
        "timeout" => 30000,
    ]);
    $symbols = [];
    foreach($symbols as $key => $symbol){
        $result = $exchange->fetch_ticker($symbol);
        $symbols[$key]["s"] = "ok";
        $symbols[$key]["n"] = "$exchangeName:".$result["info"]["symbol"];
        if(strpos((string)$result["change"], "-") === false){
            $change = "+".$result["change"];
        }else{
            $change = $result["change"];
        }
        $symbols[$key]["v"] = [
            "ch" => $change,
            "chp" => $result["percentage"],
            "short_name" => $result["info"]["symbol"],
            "exchange" => $exchangeName,
            "description" => $result["info"]["symbol"],
            "lp" => $result["info"]["lowPrice"],
            "ask" => $result["ask"],
            "bid" => $result["bid"],
            "open_price" => $result["open"],
            "high_price" => $result["high"],
            "low_price" => $result["low"],
            "prev_close_price" => $result["previousClose"],
            "volume" => $result["info"]["volume"],
        ];
    }
    $array["s"] = "ok";
    $array["d"] = $symbols;
    return api_json($array);
}

function api_time(){
	echo time();
	die();
}

function api_timescale_marks(){
	return api_error('[Implement] api_timescale_marks Not implemented');
}

function api_symbol_info(){
	return api_error('[Implement] api_symbol_info Not implemented');
}

function api_marks(){
	return api_error('[Implement] api_marks Not implemented');
}

function api_json(array $array = [], $success = true){
    $array["success"] = $success;
	die(json_encode($array));
}

function api_error($message){
    return api_json(["message" => $message], false);
}

function api_check(array $expected = [])
{
    $diff = array_diff_key(array_flip($expected), $_REQUEST);
    if(count($diff) <= 0) return $_REQUEST;
        return api_error("Missing variables: ".implode(",",array_flip($diff)).".");
}
