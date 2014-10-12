<?php
namespace Lavoisier\Hydrators;

use \Lavoisier\IHydrator;

/**
 * @author Olivier LEQUEUX
 * hydrate parsing lavoisier XML format after a CSV file conversion
 */
class CSVasXMLHydrator implements IHydrator
{

    function hydrate($str)
    {
        $rows = simplexml_load_string($str);

        $result = new \ArrayObject();
        foreach ($rows as $row) {
            foreach ($row as  $row) {
                $tmp_col = new \ArrayObject();
                foreach ($row as $column) {
                    $col_attr = $column->attributes();
                    $tmp_col[strval($col_attr['label'])] = strval($column);
                }
                $result->append($tmp_col);
            }

        }

        return $result;
    }
}