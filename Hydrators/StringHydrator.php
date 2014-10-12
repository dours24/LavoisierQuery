<?php
/*
 * @todo check if this class is use full when http code return will be properly managed in Lavoisier/Query
 * @todo ?accept=txt should be sufficient
*/

namespace Lavoisier\Hydrators;

use \Lavoisier\IHydrator;

class StringHydrator implements IHydrator{

    public function hydrate($str) {

        $sxObject = new \SimpleXMLElement($str, 0, false, 'e', true);
        if ($sxObject === false) {
            throw new \Exception('Unable to parse XML');
        }
        return (strval($sxObject->entry));
    }
}