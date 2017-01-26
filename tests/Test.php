<?php

namespace projectivemotion\TransaviaScraper\tests;

use projectivemotion\TransaviaScraper\Exception;
use projectivemotion\TransaviaScraper\Scraper;

/**
 * Project: transavia-scraper
 *
 * @author Amado Martinez <amado@projectivemotion.com>
 */
class Test extends \PHPUnit_Framework_TestCase
{
    public function testLoadTokensPass()
    {
        $scraper    =   new Scraper();
        $result =   $scraper->setTokens(<<<HDOC
<HTML><body><input name="__RequestVerificationToken" value="test" /></body></HTML>
HDOC
        );
        $this->assertTrue($result);
        $this->assertEquals('test', $scraper->getSessionToken());
    }

    /**
     * @expectedException Exception
     */
    public function testLoadTokensFail()
    {
        $scraper    =   new Scraper();
        $result =   $scraper->setTokens(<<<HDOC
<HTML><body><input name="nottoken" value="test" /></body></HTML>
HDOC
        );
    }

    public static function getJsonFile($file)
    {
        return \json_decode(file_get_contents(__DIR__ . '/' . $file));
    }

    public function testMultiAvailabilityResponseNone()
    {
        $json_no_availability   =   self::getJsonFile('multiavailability.json');
        $scraper    =   new Scraper();

        $availability = $scraper->parseMultiAvailability($json_no_availability);

        $this->assertEquals('vpW3qkKi6yfelcmjErqsgMocFG24CmDVMGDedYXK58CtcXLCrnWflckRT1-VJEDil-utx-FhirrwV7oUiguvIU69aHVrVJDNbYBL9BdOMzc1', $availability['outbound_token']);
        $this->assertEquals('1RAybeS7NGM0mltP5eERVRaNgOz5SGBZyskWXaOkX3J473cvrXijzFg0F68Gfzk049Jk7Whua4SJWGv9IoP1PCMz_uQI82hU1q-_4butCqw1',  $availability['inbound_token']);
        $this->assertEmpty($availability['Outbound']);
        $this->assertEmpty($availability['Inbound']);
    }

    public function testMultiAvailabilityResponseSome()
    {
        $json_no_availability   =   self::getJsonFile('multiavailability-dates.json');
        $scraper    =   new Scraper();

        $availability = $scraper->parseMultiAvailability($json_no_availability);

        $this->assertEquals('PlKOGseIf_hGSIuKc1ch5O9NS9fKuDqHJEwO8hmpfTno_2Iwf5VXynvGSV3KH1CmnJHcIYISWpE7UXPAqyN-gjuW3GBykZAaCAUB9jtrKf01', $availability['outbound_token']);
        $this->assertEquals('cPpEawGn_Ol3GCNWzewWI1HA3UDCze9wwjst2orv6u-3NDKvEjI3wc9kU452Pcpalll7McweKeA_VS56KlQne1gL9j-s47VGHXoNPqkSkiU1', $availability['inbound_token']);
        $this->assertNotEmpty($availability['Outbound']);
        $this->assertNotEmpty($availability['Inbound']);
    }

    public function testSingleAvailability()
    {
        $json_response  =   self::getJsonFile('singleavailability.json');
        $scraper    =   new Scraper();

        $flights = [];
        $scraper->parseSingleAvailability($json_response, $flights);
        $this->assertNotEmpty($flights);
    }
}
