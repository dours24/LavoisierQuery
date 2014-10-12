<?php

namespace Lavoisier\Hydrators;

use \Lavoisier\IHydrator;

class SimpleXMLHydrator implements IHydrator
{

    public function hydrate($str)
    {
        $sxObject = simplexml_load_string($str, '\SimpleXMLElement');
        if ($sxObject === false) {
            throw new \Exception('Unable to parse XML');
        }
        return $sxObject;
    }
}