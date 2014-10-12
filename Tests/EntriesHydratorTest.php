<?php

require_once dirname(__FILE__) . '/../../Lavoisier/IEntries.php';
require_once dirname(__FILE__) . '/../Entries.php';
require_once dirname(__FILE__) . '/../IHydrator.php';
require_once dirname(__FILE__) . '/../Hydrators/EntriesHydrator.php';

use \Lavoisier\Query;
use \Lavoisier\Entries;
use \Lavoisier\Hydrators\EntriesHydrator;


class EntriesHydratorTest extends \PHPUnit_Framework_TestCase
{
    public function testHydrate()
    {
        $input = file_get_contents(dirname(__FILE__) . '/Resources/site_names.xml');
        $output_expected = unserialize(file_get_contents(dirname(__FILE__) . "/Resources/site_names.array"));
        $lh = new EntriesHydrator();
        $ouput_obtained = $lh->hydrate($input)->getArrayCopy();
        $this->assertEquals($output_expected, $ouput_obtained);
    }

    public function testMap()
    {
        $input = file_get_contents(dirname(__FILE__) . '/Resources/entries.xml');
        $output_expected = array(
            "HOSTNAME" => 'cream1.farm.particle.cz',
            "GOCDB_PORTAL_URL" => 'https://goc.egi.eu/portal/index.php?Page_Type=Service&id=751',
            "SERVICE_TYPE" => 'APEL',
            "IN_PRODUCTION" => 'Y',
            "NODE_MONITORED" => 'Y');
        $lh = new EntriesHydrator();
        $ouput_obtained = $lh->hydrate($input)->getArrayCopy();
        $this->assertEquals($output_expected, $ouput_obtained);


        $input = file_get_contents(dirname(__FILE__) . '/Resources/entries_collection.xml');
        $output_expected = array(
        'info' => array(
            'PRIMARY_KEY' => '306G0',
            'CERTDN' => "/O=GRID-FR/C=FR/O=CNRS/OU=CC-IN2P3/CN=Foo Bar",
            'CN' => 'Foo Bar',
            'EMAIL' => 'foo.bar@cc.in2p3.fr'),
        '0' => array(
            "entity_type" => "site",
            "entity_name" => "IN2P3-CC",
            "role_label" => "Site Operations Manager"
        ),
        '1' =>array(
            "entity_type" => "project",
            "entity_name" => "EGI",
            "role_label" => "CIC Staff"
        ));
        $lh = new EntriesHydrator();
        $ouput_obtained = $lh->hydrate($input)->getArrayCopy();
        $this->assertEquals($output_expected, $ouput_obtained);

    }

}