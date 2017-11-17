<?php


namespace Espo\Modules\RealEstate\SelectManagers;

class RealEstateRequest extends \Espo\Core\SelectManagers\Base
{

    protected function filterActualSale(&$result)
    {
        $result['whereClause'][] = array(
            'type' => 'Sale',
            'status!=' => ['Completed', 'Canceled', 'Lost']
        );
    }

    protected function filterActualRent(&$result)
    {
        $result['whereClause'][] = array(
            'type' => 'Rent',
            'status!=' => ['Completed', 'Canceled', 'Lost']
        );
    }

    protected function filterActual(&$result)
    {
        $result['whereClause'][] = array(
            'status!=' => ['Completed', 'Canceled', 'Lost']
        );
    }

    protected function filterCompleted(&$result)
    {
        $result['whereClause'][] = array(
            'status=' => 'Completed'
        );
    }
}

