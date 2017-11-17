<?php


namespace Espo\Modules\Advanced\SelectManagers;

class Product extends \Espo\Core\SelectManagers\Base
{
    protected function filterAvailable(&$result)
    {
        $result['whereClause'][] = array(
            'status' => 'Available'
        );
    }

 }

