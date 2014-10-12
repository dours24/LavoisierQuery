<?php

namespace Lavoisier\Hydrators;

use \Lavoisier\IHydrator;

class DefaultHydrator implements IHydrator{

    public function hydrate($str) {
      return $str;
    }

}