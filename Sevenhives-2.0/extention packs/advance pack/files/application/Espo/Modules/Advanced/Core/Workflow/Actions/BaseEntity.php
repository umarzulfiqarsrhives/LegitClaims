<?php


namespace Espo\Modules\Advanced\Core\Workflow\Actions;

use Espo\Core\Exceptions\Error;
use Espo\Modules\Advanced\Core\Workflow\Utils;

use Espo\ORM\Entity;

abstract class BaseEntity extends Base
{
    /**
     * Default fields, use only if not defined in a rule
     *
     * @var array
     */
    protected $defaultFields = array(
        'assignedUser',
        'teams',
    );

    /**
     * Get value of a field by $fieldName
     *
     * @param  string $fieldName
     * @param  \Espo\Orm\Entity $filledEntity
     * @return mixed
     */
    protected function getValue($fieldName, \Espo\Orm\Entity $filledEntity = null)
    {
        $actionData = $this->getActionData();
        $entity = $this->getEntity();

        if (isset($actionData['fields'][$fieldName])) {
            $fieldParams = $actionData['fields'][$fieldName];

            if (isset($filledEntity)) {
                $filledFieldType = Utils::getFieldType($filledEntity, $fieldName);
            }

            switch ($fieldParams['subjectType']) {
                case 'value':
                    $fieldValue = $fieldParams['attributes'];
                    if (isset($fieldParams['attributes']) && is_array($fieldParams['attributes'])) {

                        if (isset($fieldParams['attributes'][$fieldName])) {
                            $fieldValue = $fieldParams['attributes'][$fieldName];
                            break;
                        }

                        $filledEntity = isset($filledEntity) ? $filledEntity : $entity;

                        $normalizedFieldName = Utils::normalizeFieldName($filledEntity, $fieldName);
                        if (!is_array($normalizedFieldName)) {
                            $normalizedFieldName = (array) $normalizedFieldName;
                        }

                        $fieldValue = array();
                        foreach ($normalizedFieldName as $name) {
                            if (isset($fieldParams['attributes'][$name])) {
                                $fieldValue[$name] = $fieldParams['attributes'][$name];
                            }
                        }
                    }
                    break;

                case 'field':
                    $fieldValue = Utils::getFieldValue($entity, $fieldParams['field'], false, $this->getEntityManager());

                    if (isset($fieldParams['shiftDays'])) { //the field is date
                        $fieldValue = Utils::shiftDays($fieldParams['shiftDays'], $fieldValue, $filledFieldType);
                        break;
                    }

                    $normalizedFieldName = Utils::normalizeFieldName($entity, $fieldName);
                    if (!is_array($normalizedFieldName) && $fieldParams['field'] != $normalizedFieldName) {
                        $fieldValue = array(
                            $normalizedFieldName => $fieldValue,
                        );
                    }
                    break;

                case 'today':
                    return Utils::shiftDays($fieldParams['shiftDays'], null, $filledFieldType);
                    break;

                default:
                    throw new Error('Workflow['.$this->getWorkflowId().']: Unknown fieldName for a field [' . $fieldName . ']');
            }
        }

        return $fieldValue;
    }

    /**
     * Fill data into entity
     *
     * @param  array $fields
     * @param  \Espo\Orm\Entity $entity
     * @return \Espo\Orm\Entity
     */
    protected function fillData(\Espo\Orm\Entity $entity, array $fields)
    {
        if (empty($fields)) {
            return $entity;
        }

        foreach ($fields as $fieldName => $fieldParams) {

            $isSave = false;
            if ($entity->hasRelation($fieldName)) { //relation

                $fieldValue = $this->getValue($fieldName, $entity);
                $isSave = true;

            } else if ($entity->hasField($fieldName)){ //field

                $fieldValue = $this->getValue($fieldName, $entity);
                $isSave = true;
            }

            if ($isSave) {
               $res = is_array($fieldValue) ? $entity->set($fieldValue) : $entity->set($fieldName, $fieldValue);
            }
        }

        //set default values
        $parentEntity = $this->getEntity();

        foreach ($this->defaultFields as $defaultFieldName) {

             if (!isset($fields[$defaultFieldName])) {

                $parentFieldValue = Utils::getFieldValue($parentEntity, $defaultFieldName);
                if (!isset($parentFieldValue)) {
                    continue;
                }

                $normalizedFieldName = Utils::normalizeFieldName($entity, $defaultFieldName);
                if (is_array($normalizedFieldName) && is_array($parentFieldValue)) {
                    $entity->set($parentFieldValue);
                    continue;
                }

                $entity->set($normalizedFieldName, $parentFieldValue);
            }
        }
        //END: set default values

        return $entity;
    }
}