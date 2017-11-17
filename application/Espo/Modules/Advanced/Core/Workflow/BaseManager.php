<?php


namespace Espo\Modules\Advanced\Core\Workflow;

use Espo\Core\Exceptions\Error;

abstract class BaseManager
{
    protected $dirName;

    private $container;

    private $processId;

    private $entityList;

    private $workflowIdList;

    private $objects;

    /**
     * Required option in condition/action data
     * @var array
     */
    protected $requiredOptions = array();

    public function __construct(\Espo\Core\Container $container)
    {
        $this->container = $container;
    }

    protected function getContainer()
    {
        return $this->container;
    }

    public function setInitData($workflowId, \Espo\Orm\Entity $entity)
    {
        $this->processId = $workflowId . '-'. $entity->id;

        $this->workflowIdList[$this->processId] = $workflowId;
        $this->entityList[$this->processId] = $entity;
    }

    protected function getProcessId()
    {
        if (empty($this->processId)) {
            throw new Error('Workflow['.__CLASS__.'], getProcessId(): Empty processId.');
        }

        return $this->processId;
    }

    protected function getWorkflowId($processId = null)
    {
        if (!isset($processId)) {
            $processId = $this->getProcessId();
        }

        if (empty($this->workflowIdList[$processId])) {
            throw new Error('Workflow['.__CLASS__.'], getWorkflowId(): Empty workflowId.');
        }

        return $this->workflowIdList[$processId];
    }

    protected function getEntity($processId = null)
    {
        if (!isset($processId)) {
            $processId = $this->getProcessId();
        }

        if (empty($this->entityList[$processId])) {
            throw new Error('Workflow['.__CLASS__.'], getEntity(): Empty Entity object.');
        }

        return $this->entityList[$processId];
    }

    /**
     * Get class by $name
     *
     * @param  string $name
     * @return object
     */
    protected function getClass($name, $processId = null)
    {
        $name = ucfirst($name);
        $name = str_replace("\\", "", $name);

        if (!isset($processId)) {
            $processId = $this->getProcessId();
        }

        $workflowId = $this->getWorkflowId($processId);

        if (!isset($this->objects[$processId][$name])) {
            $className = '\Espo\Modules\Advanced\Core\Workflow\\' . ucfirst($this->dirName) . '\\' . $name;
            if (!class_exists($className)) {
                throw new Error('Workflow['.$workflowId.']: Class ['.$className.'] does not exist.');
            }

            $class = new $className($this->getContainer());
            $this->objects[$processId][$name] = $class;
        }

        $this->objects[$processId][$name]->setWorkflowId($workflowId);

        return $this->objects[$processId][$name];
    }

    /**
     * Validate condition/action data
     *
     * @param  array $options
     * @return bool
     */
    protected function validate($options)
    {
        foreach ($this->requiredOptions as $optionName) {
            if (!array_key_exists($optionName, $options)) {
                return false;
            }
        }

        return true;
    }
}