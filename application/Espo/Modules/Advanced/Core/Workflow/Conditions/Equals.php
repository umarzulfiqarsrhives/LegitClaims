<?php


namespace Espo\Modules\Advanced\Core\Workflow\Conditions;

class Equals extends Base
{
    protected function compare($fieldValue)
    {
        $subjectValue = $this->getSubjectValue();

        return ($fieldValue == $subjectValue);
    }

    protected function compareComplex($entity, $condition)
    {
        if (empty($condition['fieldValueMap'])) {
            return false;
        }
        $fieldValueMap = $condition['fieldValueMap'];

        foreach ($fieldValueMap as $field => $value) {
            if ($entity->get($field) !== $value) {
                return false;
            }
        }

        return true;
    }

}