<?php

namespace Lavoisier\Exceptions;

use \Lavoisier\Query;

class cURLException extends \Exception
{

    private $userMessage = null;
    function __construct(Query $q, $curlError)
    {
        $this->userMessage = "[ERROR] " . $curlError;
        parent::__construct($this->userMessage, 0);
    }
    

}