<?php


namespace Espo\Modules\Advanced\SelectManagers;

class Quote extends \Espo\Core\SelectManagers\Base
{
    protected function filterActual(&$result)
    {
        $result['whereClause'][] = array(
            'status!=' => ['Approved', 'Rejected', 'Canceled']
        );
    }

    protected function filterApproved(&$result)
    {
        $result['whereClause'][] = array(
            'status' => 'Approved'
        );
    }

 }

