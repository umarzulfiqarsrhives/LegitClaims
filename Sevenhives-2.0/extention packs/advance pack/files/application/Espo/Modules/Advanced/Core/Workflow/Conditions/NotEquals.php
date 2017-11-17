<?php


namespace Espo\Modules\Advanced\Core\Workflow\Conditions;

class NotEquals extends Equals
{
    protected function compare($fieldValue)
    {
        return !(parent::compare($fieldValue));
    }

    protected function compareComplex($entity, $condition)
    {
        if (empty($condition['fieldValueMap'])) {
            return false;
        }
        $fieldValueMap = $condition['fieldValueMap'];

        $isEqual = true;
        foreach ($fieldValueMap as $field => $value) {
            if ($entity->get($field) !== $value) {
                $isEqual = false;
                break;
            }
        }

        return !$isEqual;
    }
}