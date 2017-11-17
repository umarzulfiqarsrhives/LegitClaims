<?php


namespace Espo\Modules\Advanced\Hooks\Workflow;

class ReloadWorkflows extends \Espo\Core\Hooks\Base
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
        $workflowManager->loadWorkflows(true);
    }

    public function afterRemove(\Espo\Orm\Entity $entity)
    {
        $workflowManager = $this->getWorkflowManager();
        $workflowManager->loadWorkflows(true);
    }

}