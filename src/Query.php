<?php
/**
 * Project: transavia-scraper
 *
 * @author Amado Martinez <amado@projectivemotion.com>
 */

namespace projectivemotion\TransaviaScraper;


class Query
{
    /**
     * Airport code
     *
     * @var string
     */
    protected $origin;

    /**
     * Airport code
     *
     * @var string
     */
    protected $destination;
    /**
     * @var \DateTime
     */
    protected $outbound_date;

    /**
     * @var \DateTime
     */
    protected $inbound_date = null;

    protected $adults   =   0;
    protected $infants  =   0;
    protected $children =   0;

    protected $flying_blue   =   false;


    public function setDestination($destination)
    {
        $this->destination = $destination;
    }

    public function setOrigin($origin)
    {
        $this->origin = $origin;
    }

    public function getOrigin()
    {
        return $this->origin;
    }

    public function getDestination()
    {
        return $this->destination;
    }

    public function isInbound()
    {
        return isset($this->inbound_date);
    }

    public function isFlyingBlue()
    {
        return $this->flying_blue;
    }

    public function setFlyingBlue($flying_blue)
    {
        $this->flying_blue = $flying_blue;
    }

    public function setChildren($children)
    {
        $this->children = $children;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function setInfants($infants)
    {
        $this->infants = $infants;
    }

    public function getInfants()
    {
        return $this->infants;
    }

    public function setAdults($adults)
    {
        $this->adults = $adults;
    }

    public function getAdults()
    {
        return $this->adults;
    }


    public function setOutboundDate(\DateTime $outbound_date)
    {
        $this->outbound_date = $outbound_date;
    }

    public function getOutboundDate()
    {
        return $this->outbound_date;
    }

    public function setInboundDate(\DateTime $inbound_date)
    {
        $this->inbound_date = $inbound_date;
    }

    public function getInboundDate()
    {
        return $this->inbound_date;
    }

    public function getPost()
    {
        $outdate    =   $this->getOutboundDate();
        $indate     =   $this->getInboundDate();

        $isReturnFlight = isset($indate);
        $arr = [
                '__RequestVerificationToken' => '',
                'routeSelection.DepartureStation' => $this->getOrigin(),
                'routeSelection.ArrivalStation' => $this->getDestination(),
                'dateSelection.OutboundDate.Day' => $outdate->format('d'),
                'dateSelection.OutboundDate.Month' => $outdate->format('m'),
                'dateSelection.OutboundDate.Year' => $outdate->format('Y'),
                'dateSelection.IsReturnFlight' => $isReturnFlight ? 'true' : 'false',
                'dateSelection.InboundDate.Day' => $isReturnFlight ? $indate->format('d') : '',
                'dateSelection.InboundDate.Month' => $isReturnFlight ? $indate->format('m') : '',
                'dateSelection.InboundDate.Year' =>  $isReturnFlight ? $indate->format('Y') : '',
                'selectPassengersCount.AdultCount' => $this->getAdults(),
                'selectPassengersCount.ChildCount' => $this->getChildren(),
                'selectPassengersCount.InfantCount' => $this->getInfants(),
                'flyingBlueSearch.FlyingBlueSearch' => $this->isFlyingBlue() ? 'true' : 'false',
            ];
        return $arr;
    }
}