<?php


namespace Espo\Modules\Advanced\Core\Workflow\Conditions;

class WasNotEqual extends WasEqual
{
    protected function compare($fieldValue)
    {
        return !(parent::compare($fieldValue));
    }

}