<?php


namespace Espo\Modules\RealEstate\SelectManagers;

class RealEstateProperty extends \Espo\Core\SelectManagers\Base
{

    protected function filterActualSale(&$result)
    {
        $result['whereClause'][] = array(
            'requestType' => 'Sale',
            'status!=' => ['Completed', 'Canceled', 'Lost']
        );
    }

    protected function filterActualRent(&$result)
    {
        $result['whereClause'][] = array(
            'requestType' => 'Rent',
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

