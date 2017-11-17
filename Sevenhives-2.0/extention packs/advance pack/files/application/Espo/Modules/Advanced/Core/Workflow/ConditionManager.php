<?php


namespace Espo\Modules\Advanced\Core\Workflow;

use Espo\Core\Exceptions\Error;

class ConditionManager extends BaseManager
{
    protected $dirName = 'Conditions';

    protected $requiredOptions = array(
        'comparison',
        'fieldToCompare',
    );

    /**
     * Check conditions "Any"
     *
     * @param  array  $conditions
     * @return bool
     */
    public function compareConditionsAny(array $conditions)
    {
        if (!isset($conditions)) {
            return true;
        }

        foreach ($conditions as $condition) {
            if ($this->compare($condition)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check conditions "Any"
     *
     * @param  array  $conditions
     * @return bool
     */
    public function compareConditionsAll(array $conditions)
    {
        if (!isset($conditions)) {
            return true;
        }

        foreach ($conditions as $condition) {
            if (!$this->compare($condition)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Compare a single condition
     *
     * @param  Entity $entity
     * @param  array $conditions
     * @return bool
     */
    protected function compare(array $condition)
    {
        $entity = $this->getEntity();
        $entityName = $entity->getEntityName();

        if (!$this->validate($condition)) {
            $GLOBALS['log']->warning('Workflow['.$this->getWorkflowId().']: Condition data is broken for the Entity ['.$entityName.'].');
            return false;
        }

        $compareClass = $this->getClass($condition['comparison']);
        if (isset($compareClass)) {
            return $compareClass->process($entity, $condition);
        }

        return false;
    }
}