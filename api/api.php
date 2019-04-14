<?php

date_default_timezone_set ('UTC');
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(E_ALL|E_STRICT);
date_default_timezone_set('UTC');
header('Access-Control-Allow-Origin: *');

require_once('php/apiFunctions.php');
require_once('ccxt/ccxt.php');

$currentUser = wp_get_current_user();

$responseAr = array();
$responseAr['success'] = true;

if (isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];
} else {
    returnError('missing_action');
}

switch ($action) {
    case 'ping':
        $responseAr['ip'] = $_SERVER['REMOTE_ADDR'];
        $responseAr['x-forwarded-for'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
        $responseAr['http-client-ip'] = $_SERVER['HTTP_CLIENT_IP'];
        break;
    case 'config':
        $responseAr['supported_resolutions'] = ['1', '5', '15', '30', '60', '1D', '1W', '1M'];
        $responseAr['supports_group_request'] = false;
        $responseAr['supports_marks'] = false;
        $responseAr['supports_search'] = true;
        $responseAr['supports_timescale_marks'] = false;
        break;
    case 'symbols':
        $exchangeName = 'Binance';
        $requestAr = requestCheck(['symbol']);
        $symbol = $requestAr['symbol'];

        $exchangeStr = '\\ccxt\\'.strtolower($exchangeName);
        $exchange = new $exchangeStr  ([
            'verbose' => false,
            'timeout' => 30000,
        ]);
        try {
            $result = $exchange->fetch_ticker ($symbol);
            $responseAr['name'] = $result['info']['symbol'];
            $responseAr['ticker'] = $result['symbol'];
            $responseAr['description'] = $result['info']['symbol'];
            $responseAr['type'] = 'crypto';
            $responseAr['session'] = '24x7';
            $responseAr['exchange'] = $exchangeName;
            $responseAr['listed_exchange'] = $exchangeName;
            $responseAr['timezone'] = 'Etc/UTC';
            $responseAr['minmov'] = "";
            $responseAr['pricescale'] = "";
            $responseAr['minmove2'] = "";
            $responseAr['fractional'] = "";
            $responseAr['has_intraday'] = false;
            $responseAr['supported_resolutions'] = ['1', '5', '15', '30', '60', '1D', '1W', '1M'];
            $responseAr['intraday_multipliers'] = [];
            $responseAr['has_seconds'] = false;
            $responseAr['seconds_multipliers'] = [];
            $responseAr['has_daily'] = true;
            $responseAr['has_weekly_and_monthly'] = true;
            $responseAr['has_empty_bars'] = true;
            $responseAr['force_session_rebuild'] = true;
            $responseAr['has_no_volume'] = false;
            $responseAr['volume_precision'] = 0;
            $responseAr['data_status'] = 'streaming';
            $responseAr['expired'] = false;
            //$responseAr['expiration_date'] = '';
            $responseAr['industry'] = 'crypto';
            $responseAr['currency_code'] = $result['info']['symbol'];

            //$responseAr['test'] = $result['change'];
            //$responseAr['result'] = $result;
            //$responseAr['symbol'] = $result;
        } catch (\ccxt\NetworkError $e) {
            echo '[Network Error] ' . $e->getMessage () . "\n";
        } catch (\ccxt\ExchangeError $e) {
            echo '[Exchange Error] ' . $e->getMessage () . "\n";
        } catch (Exception $e) {
            echo '[Error] ' . $e->getMessage () . "\n";
        }

        break;
    case 'symbol_info':
        //
        break;
    case 'search':
        $requestAr = requestCheck(['query', 'type', 'exchange', 'limit']);
        $exchangeName = 'Binance';
        $symbol = $requestAr['symbol'];

        $exchangeStr = '\\ccxt\\'.strtolower($exchangeName);
        $exchange = new $exchangeStr  ([
            'verbose' => false,
            'timeout' => 30000,
        ]);
        try {
            $result = $exchange->fetch_ticker ($symbol);
            $responseAr['symbol'] = $result['info']['symbol'];
            $responseAr['full_name'] = $result['symbol'];
            $responseAr['description'] = $result['info']['symbol'];
            $responseAr['exchange'] = $exchangeName;
            $responseAr['type'] = 'crypto';
        } catch (\ccxt\NetworkError $e) {
            echo '[Network Error] ' . $e->getMessage () . "\n";
        } catch (\ccxt\ExchangeError $e) {
            echo '[Exchange Error] ' . $e->getMessage () . "\n";
        } catch (Exception $e) {
            echo '[Error] ' . $e->getMessage () . "\n";
        }
        break;
    case 'history':
        $requestAr = requestCheck(['symbol', 'from', 'to', 'resolution']);
        $symbol = $requestAr['symbol'];
        $resolution = $requestAr['resolution'];
        if($resolution == 'D' || $resolution == 'W' || $resolution == 'M'){
            $resolution = '1'.strtolower($resolution);
        }

        $exchangeStr = '\\ccxt\\'.strtolower($exchangeName);
        $exchange = new $exchangeStr ([
            'enableRateLimit' => true,
            //'verbose' => true,
                ]);
        $markets = $exchange->load_markets();
        $ohlcv = $exchange->fetchOHLCV($symbol, $resolution, $requestAr['from'], 360);
        $ohlcvTv = $exchange->convert_ohlcv_to_trading_view($ohlcv);
        $tAr = [];
        $oAr = [];
        $hAr = [];
        $lAr = [];
        $cAr = [];
        $vAr = [];
        foreach ($ohlcvTv as $candle) {
            $tAr[] = $candle[0];
            $oAr[] = $candle[1];
            $hAr[] = $candle[2];
            $lAr[] = $candle[3];
            $cAr[] = $candle[4];
            $vAr[] = $candle[5];
        }
        $responseAr['s'] = 'ok';
        $responseAr['t'] = $tAr;
        $responseAr['o'] = $oAr;
        $responseAr['h'] = $hAr;
        $responseAr['l'] = $lAr;
        $responseAr['c'] = $cAr;
        $responseAr['v'] = $vAr;
        break;
    case 'marks':
        //
        break;
    case 'timescale_marks':
        //
        break;
    case 'time':
        echo time();
        die();
    case 'quotes':
        $requestAr = requestCheck(['symbols']);
        $symbols = explode(',', $requestAr['symbols']);

        $exchangeName = 'Binance';
        $symbol = $requestAr['symbol'];

        $exchangeStr = '\\ccxt\\'.strtolower($exchangeName);
        $exchange = new $exchangeStr  ([
            'verbose' => false,
            'timeout' => 30000,
        ]);
        $symbolAr = [];
        foreach($symbols as $key => $symbol){
            try {
                $result = $exchange->fetch_ticker ($symbol);
                $symbolAr[$key]['s'] = 'ok';
                $symbolAr[$key]['n'] = "$exchangeName:".$result['info']['symbol'];
                if(strpos((string)$result['change'], '-') === false){
                    $change = '+'.$result['change'];
                }else{
                    $change = $result['change'];
                }
                $symbolAr[$key]['v'] = [
                    'ch' => $change,
                    'chp' => $result['percentage'],
                    'short_name' => $result['info']['symbol'],
                    'exchange' => $exchangeName,
                    'description' => $result['info']['symbol'],
                    'lp' => $result['info']['lowPrice'],
                    'ask' => $result['ask'],
                    'bid' => $result['bid'],
                    'open_price' => $result['open'],
                    'high_price' => $result['high'],
                    'low_price' => $result['low'],
                    'prev_close_price' => $result['previousClose'],
                    'volume' => $result['info']['volume'],
                ];
            } catch (\ccxt\NetworkError $e) {
                echo '[Network Error] ' . $e->getMessage () . "\n";
            } catch (\ccxt\ExchangeError $e) {
                echo '[Exchange Error] ' . $e->getMessage () . "\n";
            } catch (Exception $e) {
                echo '[Error] ' . $e->getMessage () . "\n";
            }
        }
        $responseAr['s'] = 'ok';
        $responseAr['d'] = $symbolAr;
        break;
    default:
        returnError('invalid_action');
}

echo json_encode($responseAr);
