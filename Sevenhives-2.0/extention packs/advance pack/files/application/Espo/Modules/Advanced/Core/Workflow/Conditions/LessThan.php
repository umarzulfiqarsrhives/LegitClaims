<?php


namespace Espo\Modules\Advanced\Core\Workflow\Conditions;

class LessThan extends Base
{
    protected function compare($fieldValue)
    {
        $subjectValue = $this->getSubjectValue();

        return ($fieldValue < $subjectValue);
    }
}