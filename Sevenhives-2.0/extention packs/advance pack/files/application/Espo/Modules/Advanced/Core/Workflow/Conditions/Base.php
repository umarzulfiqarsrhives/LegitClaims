<?php


namespace Espo\Modules\Advanced\Core\Workflow\Conditions;

use Espo\Core\Exceptions\Error;
use Espo\Modules\Advanced\Core\Workflow\Utils;

abstract class Base
{
    protected $container;

    private $workflowId;

    protected $entity;

    protected $condition;

    public function __construct(\Espo\Core\Container $container)
    {
        $this->container = $container;
    }

    protected function getContainer()
    {
        return $this->container;
    }

    public function setWorkflowId($workflowId)
    {
        $this->workflowId = $workflowId;
    }

    protected function getWorkflowId()
    {
        return $this->workflowId;
    }

    protected function getEntity()
    {
        return $this->entity;
    }

    protected function getCondition()
    {
        return $this->condition;
    }

    public function process($entity, $condition)
    {
        $this->entity = $entity;
        $this->condition = $condition;

        if (!empty($condition['fieldValueMap'])) {
            return $this->compareComplex($entity, $condition);
        } else {
            $fieldName = $this->getFieldName();
            if (isset($fieldName)) {
                return $this->compare($this->getFieldValue());
            }
        }

        return false;
    }

    protected function compareComplex()
    {
        return false;
    }

    abstract protected function compare($fieldValue);

    /**
     * Get field name based on fieldToCompare value
     *
     * @return string
     */
    protected function getFieldName()
    {
        $condition = $this->getCondition();

        if (isset($condition['fieldToCompare'])) {
            $entity = $this->getEntity();
            $fieldName = $condition['fieldToCompare'];

            $normalizeFieldName = Utils::normalizeFieldName($entity, $fieldName);
            if (is_array($normalizeFieldName)) { //if field is parent
                return reset($normalizeFieldName);
            }

            return $normalizeFieldName;
        }
    }

    /**
     * Get value of fieldToCompare field
     *
     * @return mixed
     */
    protected function getFieldValue()
    {
        $entity = $this->getEntity();
        $condition = $this->getCondition();

        $fieldValue = Utils::getFieldValue($entity, $condition['fieldToCompare']);

        if (!is_array($fieldValue)) {
            return Utils::strtolower($fieldValue);
        }

        return $fieldValue;
    }

    /**
     * Get value of subject field
     *
     * @return mixed
     */
    protected function getSubjectValue()
    {
        $entity = $this->getEntity();
        $condition = $this->getCondition();

        switch ($condition['subjectType']) {
            case 'value':
                $subjectValue = $condition['value'];
                break;

            case 'field':
                $subjectValue = Utils::getFieldValue($entity, $condition['field']);

                if (isset($condition['shiftDays'])) {
                    return Utils::shiftDays($condition['shiftDays'], $subjectValue);
                }
                break;

            case 'today':
                return Utils::shiftDays($condition['shiftDays']);
                break;

            default:
                throw new Error('Workflow['.$this->getWorkflowId().']: Unknown object type [' . $condition['subjectType'] . '].');
        }

        return Utils::strtolower($subjectValue);
    }
}