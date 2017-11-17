<?php


namespace Espo\Modules\Advanced\Core\Workflow\Conditions;

class True extends Base
{
    protected $permittedValues = array(
        true,
        'true',
        1,
        '1',
    );

    protected function compare($fieldValue)
    {
        return in_array($fieldValue, $this->permittedValues, true);
    }
}