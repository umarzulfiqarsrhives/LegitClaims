<?php


namespace Espo\Modules\Advanced\Core\Workflow\Conditions;

class After extends Base
{
    protected function compare($fieldValue)
    {
        $subjectValue = $this->getSubjectValue();

        $fieldDate = new \DateTime($fieldValue);
        $subjectDate = new \DateTime($subjectValue);

        return ($fieldDate > $subjectDate);
    }
}