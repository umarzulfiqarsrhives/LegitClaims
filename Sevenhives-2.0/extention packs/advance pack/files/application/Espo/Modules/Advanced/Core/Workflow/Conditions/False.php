<?php


namespace Espo\Modules\Advanced\Core\Workflow\Conditions;

class False extends True
{
    protected function compare($fieldValue)
    {
        return !(parent::compare($fieldValue));
    }
}