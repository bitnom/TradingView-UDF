# TradingView-UDF
**What**

This is a [TradingView charting](https://github.com/tradingview/charting_library) library UDF implemented in PHP.

**Why**

I couldn't find one and so I made one. So, here you go.

**License**

You may use freely but I chose the GPL3 license which means if you modify this code, you need to share those modifications via a fork. Please do that. Use it in commercial applications if you want, just please maintain a fork repo of your UDF.

**How-To**

This UDF assumes that you're installing it under an `/api/` subdirectory on an Apache, Litespeed, or OpenLitespeed server. It will work on NGINX as well but you'll have to create your own rewrites in that case.

This UDF currently aims to act as a proxy between crypto exchanges and the TradingView charting library. This is accomplished using the [CCXT library](https://github.com/ccxt/ccxt).

Issues & pull requests are welcomed.
