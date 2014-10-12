<?php

require_once dirname(__FILE__) . '/../Query.php';
require_once dirname(__FILE__) . '/../IHydrator.php';
require_once dirname(__FILE__) . '/../Hydrators/DefaultHydrator.php';
require_once dirname(__FILE__) . '/../Hydrators/EntriesHydrator.php';

use \Lavoisier\Query;


class QueryTest extends \PHPUnit_Framework_TestCase
{
    public function testQuery()
    {
        $xlq = new Query('localhost', 'myView');

        $expected_query_string = "http://localhost:8080/lavoisier/myView/NGIs/NGI[@name='foo' or @name='bar']?accept=xml";
        $path = sprintf('/NGIs/NGI[%s]', Query::buildPredicate('@name', array('foo', 'bar')));
        $xlq->setPath($path);
        $this->assertEquals($expected_query_string, $xlq->getUrl());

        $expected_query_string = "http://localhost:9000/notify/yourView";
        $xlq->setPath($path);
        $xlq->setOperation('notify');
        $xlq->setPort('9000');
        $xlq->setView('yourView');
        $this->assertEquals($expected_query_string, $xlq->getUrl());
    }

    public function testHelpers()
    {

        $output_expected = "F1=V1&F2=V2";
        $fields_string = Query::urlify(array('F1' => 'V1', 'F2' => 'V2'));
        $this->assertEquals($output_expected, $fields_string);
    }

    public function testBuildPredicate()
    {

        $predicate = Query::buildPredicate('@key', array('IN2P3-CC', 'IN2P3-CC-T2', 'AM-02-SEUA', 'AEGIS01-IPB-SCL'));
        $this->assertEquals("@key='IN2P3-CC' or @key='IN2P3-CC-T2' or @key='AM-02-SEUA' or @key='AEGIS01-IPB-SCL'", $predicate);

        $predicate = Query::buildEntriesPredicate(
            array('GHD_Affected_Site' => 'IN2P3-CC', 'GHD_Status' => 'assigned'),
            'or', false);
        $this->assertEquals(
            "(@key='GHD_Affected_Site' and text()='IN2P3-CC') or (@key='GHD_Status' and text()='assigned')",
            $predicate);

        $predicate = Query::buildEntriesPredicate(
            array('GHD_Affected_Site' => array('IN2P3-CC', 'IN2P3-CC-T2'), 'GHD_Status' => 'assigned'),
            'or', false);
        $this->assertEquals(
            "(@key='GHD_Affected_Site' and text()='IN2P3-CC') or (@key='GHD_Affected_Site' and text()='IN2P3-CC-T2') or (@key='GHD_Status' and text()='assigned')",
            $predicate);

        $predicate = Query::buildEntriesPredicate(array(), 'or');
        $this->assertEquals('true()', $predicate);
        $predicate = Query::buildEntriesPredicate(array('EMPTY_ARRAY' => array()), 'or');
        $this->assertEquals('true()', $predicate);


    }

}