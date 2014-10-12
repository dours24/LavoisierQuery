<?php

require_once dirname(__FILE__) . '/../../Lavoisier/IEntries.php';
require_once dirname(__FILE__) . '/../Entries.php';
require_once dirname(__FILE__) . '/../IHydrator.php';
require_once dirname(__FILE__) . '/../Hydrators/EntriesHydrator.php';

require_once dirname(__FILE__) . '/../../TicketingSystem/Ticket/ArrayFields.php';
require_once dirname(__FILE__) . '/../../TicketingSystem/Ticket/GgusFields.php';


use \Lavoisier\Entries;


class EntriesTest extends \PHPUnit_Framework_TestCase
{


    public function testasXmlEntries()
    {
        $input = array(
            array(
                "HOSTNAME" => "cream1.farm.particle.cz",
                "GOCDB_PORTAL_URL" => "https://goc.egi.eu/portal/index.php?Page_Type=Service&amp;id=751",
                "SERVICE_TYPE" => "APEL",
                "IN_PRODUCTION" => "Y",
                "NODE_MONITORED" => "Y"
            ),
            array(
                "HOSTNAME" => "cream1.farm.particle.cz",
                "GOCDB_PORTAL_URL" => "https://goc.egi.eu/portal/index.php?Page_Type=Service&amp;id=751",
                "SERVICE_TYPE" => "APEL",
                "IN_PRODUCTION" => "Y",
                "NODE_MONITORED" => "Y"
            )
        );
        $output_expected = file_get_contents(dirname(__FILE__) . '/Resources/entries_test.xml');
        $lh = new Entries($input);
        $ouput_obtained = $lh->asXmlEntries();
        $this->assertXmlStringEqualsXmlString($output_expected, $ouput_obtained);


    }


    public function testTicketHydratation()
    {

        $input = file_get_contents(dirname(__FILE__) . '/Resources/tickets.xml');
        $hydrator = new \Lavoisier\Hydrators\EntriesHydrator("\TicketingSystem\Ticket\GgusFields");
        $coll = $hydrator->hydrate($input);

        print_r($coll);

    }

}