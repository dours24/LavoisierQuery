<?php

namespace Lavoisier;

/**
 * Created by JetBrains PhpStorm.
 * User: Olivier LEQUEUX
 * Date: 22/10/13
 */

use \Lavoisier\IHydrator;
use \Lavoisier\Hydrators\DefaultHydrator;
use \Lavoisier\Exceptions\HTTPStatusException;
use \Lavoisier\Exceptions\cURLException;

class Query
{
    static private $queryTemplates;

    private $is_secure;
    private $schema;
    private $hostname;
    private $port;
    private $operation;
    private $view;
    private $path;
    private $accept;

    private $hydrator;
    private $method; //can be set to 'GET' or 'POST'
    private $post_fields;
    private $connection_timeout_ms;
    private $timeout_ms;

    public static $HTTP_STATUS_MAP = array(
        '401' => '[HTTP 401] Lavoisier authentication problem',
        '403' => '[HTTP 403] Lavoisier authorization problem',
        '404' => '[HTTP 404] Lavoisier execution failed'

    );

    public function __construct($hostname, $view = '', $operation = 'lavoisier', $accept='xml')
    {
        $this->setIsSecure(false);
        $this->hostname = $hostname;
        $this->port = '8080';
        $this->operation = $operation;
        $this->view = $view;
        $this->path = '';
        $this->accept = $accept;

        $this->query_field = false;
        $this->method = 'GET';

        self::$queryTemplates = array(
            'lavoisier' => "%s%s:%s/%s/%s%s%saccept=%s",
            'lavoisier_base' => "%s%s:%s/%s/%s%s",
            'notify' => "%s%s:%s/%s/%s",
            'resources' => "%s%s:%s/%s/%s"
        );

        $this->hydrator = new DefaultHydrator();
        $this->timeout_ms = 500;
        $this->connect_timeout_ms = 500;
        $this->post_fields = array();


    }

    public function setIsSecure($is_secure)
    {
        $this->is_secure = $is_secure;
        if ($this->is_secure === true) {
            $this->schema = 'https://';
        } else {
            $this->schema = 'http://';
        }
    }

    public function setPort($port)
    {
        $this->port = $port;
    }

    public function setOperation($operation)
    {
        $this->operation = $operation;
    }

    public function setView($view)
    {
        $this->view = $view;
    }

    public function setPath($str)
    {
        $this->path = $str;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setMethod($method)
    {
        if (($method === 'GET') or ($method === 'POST')) {
            $this->method = $method;

        } else {
            throw new \Exception ("Please use 'GET' or 'POST' method, the '$method' method is unknown");
        }
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function setHydrator(IHydrator $hydrator)
    {
        $this->hydrator = $hydrator;
    }

    public function setConnectionTimeoutMS($connect_timeout_ms)
    {
        $this->connect_timeout_ms = $connect_timeout_ms;
    }

    public function setTimeoutMS($connect_timeout_ms)
    {
        $this->timeout_ms = $connect_timeout_ms;
    }

    public function setPostFields(array $post_fields)
    {
        $this->post_fields = $post_fields;
    }

    public function getPostFields()
    {
        return $this->post_fields;
    }

    static public function getQueryTemplate($operation = null)
    {
        if ($operation === null) {
            return self::$queryTemplates;
        }
        if (!isset(self::$queryTemplates[$operation])) {
            throw new \Exception("$operation 'operation' is not recognized by Lavoisier service");
        } else {
            return self::$queryTemplates[$operation];
        }
    }

    public function curl()
    {
        // init curl session
        $ch = curl_init(($this->getUrl(true)));
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true); // no cache, force new connection

        // timeout values
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout_ms);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connection_timeout_ms);

        // header
        curl_setopt($ch, CURLOPT_HEADER, false); // no header in string returned
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // get result from request as string (otherwise through browser)

        if ($this->method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, self::urlify($this->post_fields));
        }

        $content = curl_exec($ch);
        $response = curl_getinfo($ch);
        $response['last_curl_error'] = curl_error($ch);
        $response['content'] = $content;

        curl_close($ch); // close  cURL session

        return $response;

    }

    static private function sprintfPredicate($key, $value, $entriesMode = null)
    {

        if ($entriesMode !== null) {
            return sprintf("%s='%s'", $key, $value);
        } else {
            if (is_array($value)) {
                $string = '';
                $counter = 0;
                foreach ($value as $subValue) {
                    if ($counter != 0) {
                        $string .= ' or ';
                    }
                    $string .= sprintf("(@key='%s' and text()='%s')", $key, $subValue);
                    $counter++;
                }
                return $string;
            } else {
                return sprintf("(@key='%s' and text()='%s')", $key, $value);
            }
        }

    }

    static private function genericBuildPredicate(array $values, $uniqueKey = null, $operator = 'or')
    {
        $predicate = 'true()';
        $compute = true;
        foreach ($values as $key => $value) {
            if(!empty($value)) {
                if ($uniqueKey !== null) $key = $uniqueKey;
                if (is_array($value)) $compute = (count($value) > 0 ? true : false);
                if (($predicate === 'true()') && ($compute)) {
                    $predicate = self::sprintfPredicate($key, $value, $uniqueKey);
                } else {
                    if ($compute) {
                        $predicate = sprintf(" %s %s %s",
                            $predicate, $operator, self::sprintfPredicate($key, $value, $uniqueKey));
                    }
                }
            }
        }
        $predicate = trim($predicate);

        return $predicate;
    }

    static public function buildPredicate($test, array $values, $operator = 'or')
    {
        return self::genericBuildPredicate($values, $test, $operator);
    }

    static public function buildEntriesPredicate(array $whereClauses, $operator = 'or')
    {
        return self::genericBuildPredicate($whereClauses, null, $operator);
    }

    /**
     * convert map to &key=value url style
     * @static
     * @param array $fields, tuple of key/values to convert
     */
    static public function urlify(array $fields)
    {
        $fields_string = array();
        foreach ($fields as $key => $value) {
            if(!empty($value)) {
                $fields_string[] = sprintf('%s=%s', $key, $value);
            }
        }
        return implode('&', $fields_string);
    }

    public function getUrl($urlencode = false, $template_name = null)
    {
        $url_chunks = array(
            $this->schema,
            $this->hostname,
            $this->port,
            $this->operation,
            $this->view,
        );

        $path = $this->path;
        if ($urlencode === true) {
            $path = rawurlencode($this->path);
        }

        if ($this->operation == 'lavoisier') {
            $url_chunks = array_merge($url_chunks, array(
                    $path,
                    (($this->query_field === true) ? '&' : '?'),
                    $this->accept)
            );
        }

        if(!$template_name) {
            $template = self::getQueryTemplate($this->operation);
        }
        else {
            $template = self::getQueryTemplate($template_name);
        }

        $qString = vsprintf(
            $template,
            $url_chunks
        );


        return $qString;
    }

    public function execute()
    {
        $resCURL = $this->curl();
        if ((isset($resCURL['http_code']) && isset(self::$HTTP_STATUS_MAP[$resCURL['http_code']]))) {
            throw new HTTPStatusException($this, $resCURL['http_code'], $resCURL['content']);
        }

        if (isset($resCURL['last_curl_error']) && (!empty($resCURL['last_curl_error']))) {
            throw new cURLException($this, $resCURL['last_curl_error']);
        }

        if (($this->operation === 'lavoisier')) {
            $resHYDRATED = $this->hydrator->hydrate($resCURL['content']);
            return $resHYDRATED;
        } else {
            return $resCURL['content'];
        }
    }

    public function dump() {
        return array(
            'url' => $this->getUrl(),
            'post' => $this->getPostFields()
        );
    }

}