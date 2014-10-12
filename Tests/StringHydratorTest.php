<?php

require_once dirname(__FILE__) . '/../IHydrator.php';
require_once dirname(__FILE__) . '/../Hydrators/StringHydrator.php';

use \Lavoisier\Query;
use \Lavoisier\Hydrators\StringHydrator;


class StringHydratorTest extends \PHPUnit_Framework_TestCase
{
    public function testHydrate()
    {
        $input = '<e:entries xmlns:e="http://software.in2p3.fr/lavoisier/entries.xsd">
    <e:entry>ngi-contact@france-grilles.fr</e:entry>
</e:entries>';

        $lh = new StringHydrator();
        $this->assertEquals('ngi-contact@france-grilles.fr', $lh->hydrate($input));
    }
}