<?php


namespace Espo\Modules\Advanced\Hooks\Common;

use Espo\Modules\Advanced\Core\WorkflowManager;

class Workflow extends \Espo\Core\Hooks\Base
{
    public static $order = 9;

    protected function init()
    {
        $this->dependencies[] = 'workflowManager';
    }

    protected function getWorkflowManager()
    {
        return $this->getInjection('workflowManager');
    }

    public function afterSave(\Espo\Orm\Entity $entity)
    {
        $workflowManager = $this->getWorkflowManager();

        if (!$entity->isFetched()) {
            $workflowManager->process($entity, WorkflowManager::AFTER_RECORD_CREATED);
        }

        $workflowManager->process($entity, WorkflowManager::AFTER_RECORD_SAVED);
    }
}