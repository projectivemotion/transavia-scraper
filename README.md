# Transavia-Scraper
Transavia Airline Flights Price Scraper
[![Build Status](https://travis-ci.org/projectivemotion/transavia-scraper.svg?branch=master)](https://travis-ci.org/projectivemotion/transavia-scraper)

Last Verified: 2017-01-25

## Use at your own risk!
* I am not responsible for your use of this software.
* Please do not abuse!
* Check out my other projects: [Wizzair-Scraper](https://github.com/projectivemotion/wizzair-scraper), [Hotelscom-Scraper](https://github.com/projectivemotion/hotelscom-scraper), [EasyJet-Scraper](https://github.com/projectivemotion/easyjet-scraper), [Planitour-Scraper](https://github.com/projectivemotion/planitour-scraper), [Xgbs-Soap Client](https://github.com/projectivemotion/xgbs-soap)

### Manual Installation
    git clone https://github.com/projectivemotion/transavia-scraper.git
    cd transavia-scraper && composer update
    
### Composer Installation
    composer require projectivemotion/transavia-scraper
    
### Requirements
    PHP 5.6

### Usage

See `demo/` directory. for an example

Usage: demo/transavia.php BCN MUC 2017-03-26 2017-03-30
```
$ php -f demo/transavia.php BCN MUC 2017-03-26 2017-03-30
Using Parameters: BCN - MUC / 2017-03-26 - 2017-03-30

{
    "outbound": {
        "HV9656": {
            "flight_number": "HV9656",
            "departure_time": "9:30",
            "arrival_time": "11:40",
            "currency": "€",
            "price": "46"
        }
    },
    "inbound": {
        "HV9655": {
            "flight_number": "HV9655",
            "departure_time": "13:00",
            "arrival_time": "15:05",
            "currency": "€",
            "price": "46"
        }
    }
}
```

# License
The MIT License (MIT)

Copyright (c) 2017 Amado Martinez

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
