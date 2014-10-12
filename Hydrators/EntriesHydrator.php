<?php

namespace Lavoisier\Hydrators;

use \Lavoisier\IHydrator;
use \Lavoisier\IEntries;
use \Lavoisier\Entries;

class EntriesHydrator implements IHydrator
{
    private $value_as_key;
    private $rootBinding;
    private $keyBinding;
    private $defaultBinding;

    public function __construct()
    {
        $this->value_as_key = false;
        $this->rootBinding = '\Lavoisier\Entries';
        $this->keyBinding = array();
        $this->defaultBinding = '\Lavoisier\Entries';
    }

    public function setValueAsKey($value_as_key)
    {
        $this->value_as_key = $value_as_key;
    }

    public function setRootBinding($class) {
        $this->rootBinding = $class;
    }

    public function setDefaultBinding($class) {
        $this->defaultBinding = $class;
    }

    public function setKeyBinding(array $keyClassMap) {
        $this->keyBinding = $keyClassMap;
    }


    public function hydrate($str)
    {
        $sxObject = new \SimpleXMLIterator($str, 0, false, 'e', true);
        if ($sxObject === false) {
            throw new \Exception('Unable to parse XML');
        }
        $res = $this->sxiToArray(
            $sxObject,
            new $this->rootBinding);

        return $res;
    }

    protected function createEntriesInstance($key)
    {
        if(is_string($key) && isset($this->keyBinding[$key])) {
            $class = $this->keyBinding[$key];
            return new $class;
        }
        else {
            return new $this->defaultBinding;
        }
    }

    public function sxiToArray($sxi, IEntries $a)
    {
        for ($sxi->rewind(); $sxi->valid(); $sxi->next()) {
            $key = $this->getAttributeKey($sxi, $a);
            if ($sxi->hasChildren()) {
                $obj = $this->sxiToArray($sxi->current(), $this->createEntriesInstance($key));
                $obj->init();
                if ($key === null) {
                    $a[] = $obj;
                } else {
                    $a[$key] = $obj;
                }
            } else {
                $str = strval($sxi->current());
                if ($this->value_as_key == true) {
                    $key = $str;
                }
                if ($key === null) {
                    $a[] = $str;
                } else {
                    $a[$key] = $str;
                }
            }
        }
        $a->init();
        return $a;
    }

    private function getAttributeKey(\SimpleXMLElement $sxi, $a)
    {
        $attr = $sxi->current()->attributes();
        $prefixed_attr = $sxi->current()->attributes('e', true);
        $key = $sxi->key();
        if (isset($attr['key'])) {
            $key = strval($attr['key']);
        } else {

            if (isset($prefixed_attr['key'])) {
                $key = strval($prefixed_attr['key']);
            } else {
                if (!array_key_exists($sxi->key(), $a)) {
                    $key = null;
                }
            }
        }
        return $key;
    }

}