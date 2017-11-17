<?php


namespace Espo\Modules\Advanced\Core\Workflow\Conditions;

use Espo\Modules\Advanced\Core\Workflow\Utils;

class Changed extends Base
{
    protected function compare($fieldValue)
    {
        $entity = $this->getEntity();
        $fieldName = $this->getFieldName();

        if (!isset($fieldName)) {
            return false;
        }

        $fieldFetchedValue = $entity->getFetched($fieldName);
        $value = $entity->get($fieldName);

        if ($fieldFetchedValue != $value) {
            return $entity->isFieldChanged($fieldName);
        }

        return false;
    }
}