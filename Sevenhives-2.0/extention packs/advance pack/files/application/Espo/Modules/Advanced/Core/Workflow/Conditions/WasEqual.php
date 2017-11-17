<?php


namespace Espo\Modules\Advanced\Core\Workflow\Conditions;

use Espo\Modules\Advanced\Core\Workflow\Utils;

class WasEqual extends Base
{
    protected function compare($fieldValue)
    {
        $entity = $this->getEntity();
        $fieldName = $this->getFieldName();

        $previousFieldValue = $entity->getFetched($fieldName);
        if (isset($previousFieldValue)) {
            $previousFieldValue = Utils::strtolower($previousFieldValue);
        }

        $subjectValue = $this->getSubjectValue();

        return ($subjectValue == $previousFieldValue);
    }

}