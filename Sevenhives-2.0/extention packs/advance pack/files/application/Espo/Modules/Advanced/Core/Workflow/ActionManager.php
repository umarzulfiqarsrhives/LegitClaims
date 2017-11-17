<?php


namespace Espo\Modules\Advanced\Core\Workflow;

class ActionManager extends BaseManager
{
    protected $dirName = 'Actions';

    protected $requiredOptions = array(
        'type',
    );

    public function runActions($actions)
    {
        if (!isset($actions)) {
            return true;
        }

        $GLOBALS['log']->debug('Workflow\ActionManager: Start workflow rule ID ['.$this->getWorkflowId().'].');

        $processId = $this->getProcessId();

        foreach ($actions as $action) {
            $this->runAction($action, $processId);
        }

        $GLOBALS['log']->debug('Workflow\ActionManager: End workflow rule ID ['.$this->getWorkflowId().'].');

        return (bool) $result;
    }

    protected function runAction($action, $processId)
    {
        $entity = $this->getEntity($processId);
        $entityName = $entity->getEntityName();

        if (!$this->validate($action)) {
            $GLOBALS['log']->warning('Workflow['.$this->getWorkflowId($processId).']: Action data is broken for the Entity ['.$entityName.'].');
            return false;
        }

        $actionClass = $this->getClass($action['type'], $processId);
        if (isset($actionClass)) {
            return $actionClass->process($entity, $action);
        }

        return false;
    }
}