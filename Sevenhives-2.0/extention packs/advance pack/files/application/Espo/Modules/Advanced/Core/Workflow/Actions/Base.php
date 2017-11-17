<?php


namespace Espo\Modules\Advanced\Core\Workflow\Actions;

use Espo\Core\Exceptions\Error;

use Espo\ORM\Entity;

abstract class Base
{
    private $container;

    private $entityManager;

    private $workflowId;

    protected $entity;

    protected $action;

    public function __construct(\Espo\Core\Container $container)
    {
        $this->container = $container;
        $this->entityManager = $container->get('entityManager');
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

    protected function getEntityManager()
    {
        return $this->entityManager;
    }

    protected function getServiceFactory()
    {
        return $this->container->get('serviceFactory');
    }

    protected function getMetadata()
    {
        return $this->container->get('metadata');
    }

    protected function getUser()
    {
        return $this->container->get('user');
    }

    protected function getEntity()
    {
        return $this->entity;
    }

    protected function getActionData()
    {
        return $this->action;
    }

    protected function getHelper()
    {
        return $this->container->get('workflowHelper');
    }

    public function process($entity, $action)
    {
        $this->entity = $entity;
        $this->action = $action;

        $GLOBALS['log']->debug('Workflow\Actions: Start ['.$action['type'].'] with cid ['.$action['cid'].'] for entity ['.$entity->getEntityType().', '.$entity->id.'].');

        $result = $this->run($entity, $action);

        $GLOBALS['log']->debug('Workflow\Actions: End ['.$action['type'].'] with cid ['.$action['cid'].'] for entity ['.$entity->getEntityType().', '.$entity->id.'], result ['.(bool) $result.'].');

        if (!$result) {
            throw new Error('Workflow['.$this->getWorkflowId().']: Action failed [' . $action['type'] . '] with cid [' . $action['cid'] . '].');
        }
    }

    abstract protected function run(Entity $entity, array $actionData);
}