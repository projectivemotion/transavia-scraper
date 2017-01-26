<?php
/**
 * Project: transavia-scraper
 *
 * @author Amado Martinez <amado@projectivemotion.com>
 */

namespace projectivemotion\TransaviaScraper;


use GuzzleHttp\Client;

class Scraper
{
    const date_format = 'Y-m-d';

    /**
     * @var Client
     */
    protected $client;

    protected $tokens    =   [];

    protected $errors = [];

    public function setClient($client)
    {
        $this->client = $client;
    }

    public function getClient()
    {
        if(!$this->client){
            $this->setClient(new Client(
                [
                    'base_uri' => 'https://www.transavia.com',
                    'cookies' => true
                ]
            ));
        }
        return $this->client;
    }

    public function setTokens($body)
    {
        libxml_use_internal_errors(true);
            $dom = new \DOMDocument();
            $dom->loadHTML($body);
        libxml_clear_errors();
        $xpath = new \DOMXPath($dom);
        $tags = $xpath->query('//input[@name="__RequestVerificationToken"]');

        if($tags === false || $tags->length == 0)
        {
            $this->setError("Did not find RequestVerificationToken");
            throw new Exception();
        }
        $this->tokens['search'] =   $tags[0]->getAttribute('value');
        return true;
    }

    public function setError($msg)
    {
        $this->errors[] =   $msg;
    }

    public function loadSession()
    {
        $body   =   $this->api('/en-EU/book-a-flight/flights/search/');
        $this->setTokens($body);
    }

    public function getSessionToken()
    {
        if(empty($this->tokens['search']))
        {
            $this->loadSession();
        }

        return $this->tokens['search'];
    }

    public function api($url, $post = null)
    {
        if($post)
            $res    =   $this->getClient()->post($url, ['form_params' => $post ]);
        else
            $res    =   $this->getClient()->get($url);

//        if(strpos($res->getHeader('Content-Type'), 'text/html') === 0)
//            $this->setTokens($res->getResponseBody());

        if(strpos($res->getHeader('Content-Type')[0], 'application/json') === 0)
            return json_decode($res->getBody());

        return (string)$res->getBody();
    }

    public function getFlights(Query $flightQuery)
    {
        $result = [
            'outbound' => [],
            'inbound'   =>  []
        ];
        $avail = $this->getMultiAvailability($flightQuery);
        $outbound_datekey   =   $flightQuery->getOutboundDate()->format(self::date_format);
        $inbound_datekey = $flightQuery->getInboundDate()->format(self::date_format);

        if(isset($avail['Outbound'][$outbound_datekey]))
        {
            $this->getSingleAvailability($flightQuery->getOutboundDate(), $avail['outbound_token'], 'OutboundFlight', $result['outbound']);
        }

        if($flightQuery->isInbound() && isset($avail['Inbound'][$inbound_datekey]))
        {
            $this->getSingleAvailability($flightQuery->getInboundDate(), $avail['inbound_token'], 'InboundFlight', $result['inbound']);
        }
        return $result;
    }

    /**
     * @param \DateTime $date
     * @param $token
     * @param string $journeyType OutboundFlight|InboundFlight
     * @return array
     */
    public function getSingleAvailability(\DateTime $date, $token, $journeyType, &$flightlist)
    {
        $post = [
            'selectSingleDayAvailability.JourneyType' => $journeyType,
            'selectSingleDayAvailability.Date.DateToParse' => $date->format('Y-m-d'),
            '__RequestVerificationToken' => $token
        ];

        $json_response  =   $this->api('/en-EU/book-a-flight/flights/SingleDayAvailability/', $post);
        $this->parseSingleAvailability($json_response, $flightlist);
    }

    public function parseSingleAvailability($json, &$flightlist)
    {
        $body = '';
        if(!empty($json->SingleDayOutbound))
        {
            $body = $json->SingleDayOutbound;
        }elseif(!empty($json->SingleDayInbound))
        {
            $body = $json->SingleDayInbound;
        }else{
            $this->setError('Unable to load single day availability');
        }

        libxml_use_internal_errors(true);
            $dom = new \DOMDocument('1.0', 'UTF-8');
            $dom->loadHTML($body);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);
        $flight_nodes = $xpath->query('//button[@name="selectFlight.MarketFareKey"]');
        foreach($flight_nodes as $flight_n){
            $flight_info    =   [
                'flight_number' =>  '',
                'departure_time' => '',
                'arrival_time' => '',
                'currency'    => '',
                'price' =>  ''
            ];
            foreach($xpath->query('//time', $flight_n) as $time_el){
                $flight_info[$time_el->getAttribute('class') . '_time'] = trim($time_el->textContent);
            }
            $flight_info['flight_number']   =   $xpath->query('//li[@class="flight-number"]', $flight_n)[0]->lastChild->textContent;
            $foo = $xpath->query('.//div[contains(@class, "price")]', $flight_n);
            $flight_info['price']   =   preg_replace('#[^0-9\.]#', '', $foo[0]->textContent);
            $flight_info['currency']   =   utf8_decode($xpath->query('//span[@class="currency"]', $flight_n)[0]->textContent);

            $flightlist[$flight_info['flight_number']] = $flight_info;
        }
    }

    public function getMultiAvailability(Query $flightQuery)
    {
        $search_post    =   $flightQuery->getPost();
        $search_post['__RequestVerificationToken']  =   $this->getSessionToken();

        $multi_avail_json = $this->api('/en-EU/book-a-flight/flights/multidayavailability/', $search_post);
        if(!$multi_avail_json)
            $this->setError('Expected json object.');

        $avail  =   $this->parseMultiAvailability($multi_avail_json);
        return $avail;
    }

    /**
     * Separated for testing purposes.
     *
     * @param $json
     * @return array
     */
    public function parseMultiAvailability($json)
    {
        $availability   =   [
            'outbound_token'    => '',
            'inbound_token'     =>  ''
        ];
        $availability['Outbound']   =  $this->getAvailableDates($json->multiDayAvailabilityOutbound, $availability['outbound_token']);
        $availability['Inbound']   =  $this->getAvailableDates($json->multiDayAvailabilityInbound, $availability['inbound_token']);
        return $availability;
    }

    public function getAvailableDates($multiAvailabilityResponse, &$tokenvalue)
    {
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML($multiAvailabilityResponse);
        libxml_clear_errors();

        $date_prices    =   [];
        $xpath = new \DOMXPath($dom);

        $avail_dates = $xpath->query("//div[contains(@class, 'day-with-availability')]");
        foreach($avail_dates as $div){
            $price = $xpath->query(".//span[@class='price']", $div);
            if(!$price || $price->length != 1)
                $this->setError('Unable to find price in multiavailabilityresponse');
            $date_str   =   date_create($div->getAttribute('data-date'))->format(self::date_format);
            $date_prices[$date_str] =   $price[0]->textContent;
        }

        if($date_prices) {
            $token = $xpath->query('//input[@name="__RequestVerificationToken"]');
            if (!$token || $token->length != 3)
                $this->setError('Unable to find token.');
            $tokenvalue = $token[2]->getAttribute('value');
        }

        return $date_prices;
    }
}