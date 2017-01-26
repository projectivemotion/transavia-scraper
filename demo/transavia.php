<?php
/**
 * Project: transavia-scraper
 *
 * @author Amado Martinez <amado@projectivemotion.com>
 */
if(!isset($argv))
    die("Run from command line.");

// copied this from doctrine's bin/doctrine.php
$autoload_files = array( __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../../autoload.php');

foreach($autoload_files as $autoload_file)
{
    if(!file_exists($autoload_file)) continue;
    require_once $autoload_file;
}
// end autoloader finder


if($argc < 5)
    die("Usage:\n\t$argv[0] origin destination outbound-date inbound-date\n" .
        "Example:\n\t$argv[0] BCN MUC 2017-11-21 2017-11-25\n\n");


$origin = $argv[1];
$destination = $argv[2];
$departure_date = $argv[3];
$return_date = $argv[4];

echo "Using Parameters: $origin - $destination / $departure_date - $return_date\n\n";

$query = new \projectivemotion\TransaviaScraper\Query();
$query->setAdults(1);
$query->setOutboundDate(date_create($departure_date));
$query->setInboundDate(date_create($return_date));
$query->setOrigin($origin);
$query->setDestination($destination);


$scraper    =   new \projectivemotion\TransaviaScraper\Scraper();

$result = $scraper->getFlights($query);
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

