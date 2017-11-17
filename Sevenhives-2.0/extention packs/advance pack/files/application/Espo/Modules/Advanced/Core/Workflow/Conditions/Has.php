<?php


namespace Espo\Modules\Advanced\Core\Workflow\Conditions;

class Has extends Base
{
    protected function compare($fieldValue)
    {
        $subjectValue = $this->getSubjectValue();

        return (in_array($subjectValue, $fieldValue));
    }

}