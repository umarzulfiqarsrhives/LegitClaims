<?php


namespace Espo\Modules\Advanced\Core\Workflow\Conditions;

class IsEmpty extends Base
{
    protected function compare($fieldValue)
    {
        if (empty($fieldValue)) {
            return true;
        }

        return false;
    }
}