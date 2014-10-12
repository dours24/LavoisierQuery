<?php

namespace Lavoisier\Exceptions;

use \Lavoisier\Query;

class HTTPStatusException extends \Exception
{

    private $userMessage = null;

    function __construct(Query $q, $HTTPCode, $content)
    {
        $this->userMessage = Query::$HTTP_STATUS_MAP[$HTTPCode] . " => " . trim($content);


        if ($HTTPCode == '404') {
            $this->userMessage .= " with the following query : " . $q->getUrl();
            if ($q->getMethod() === 'POST') {
                $pFields = $q->getPostFields();
                $pFieldsAsString = '';
                foreach ($pFields as $key => $value) {
                    $pFieldsAsString .= " &$key=$value";
                }

                $this->userMessage .= " and the POSTed fields : " .$pFieldsAsString;

            }
        }
        parent::__construct($this->userMessage, 0);
    }


}