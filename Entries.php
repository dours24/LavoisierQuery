<?php

namespace Lavoisier;

/**
 * Created by JetBrains PhpStorm.
 * User: Olivier LEQUEUX
 * Date: 28/10/13
 */

use \Lavoisier\IEntries;

class Entries extends \ArrayObject implements IEntries
{
    public function init()
    {
    }

    public function pop()
    {
        $iterator = $this->getIterator();
        $iterator->rewind();
        $item = null;
        if ($iterator->valid()) {
            $item = $iterator->current();
        }
        return $item;
    }

    /**
     * deprecated, uses for tests
     * @return array
     */
    public
    function getArrayCopy()
    {

        $result = parent::getArrayCopy();
        foreach ($result as $key => $item) {
            if (is_object($item)) {
                $result[$key] = $result[$key]->getArrayCopy();
            }
        }

        return $result;

    }

    public
    function asXmlEntries()
    {
        return self::convertToEntries($this);
    }

    static protected function convertToEntries($data)
    {
        $xml = '<e:entries xmlns:e="http://software.in2p3.fr/lavoisier/entries.xsd">';
        foreach ($data as $key => $entry) {
            if (is_array($entry)) {
                $xml .= self::convertToEntries($entry);
            } else {
                $xml .= '<e:entry key ="' . $key . '">' . $entry . '</e:entry>';
            }
        }
        return $xml . '</e:entries>';
    }

    public function setValue($name, $value)
    {
        $this[$name] = $value;
    }

    public function getValue($name)
    {
        return $this[$name];
    }

}