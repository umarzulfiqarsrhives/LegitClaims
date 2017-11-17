<?php


namespace Espo\Modules\Advanced\Core;

use Espo\Core\Utils\Json;
use Espo\Core\Utils\Util;

class WorkflowManager
{
    private $container;

    private $conditionManager;

    private $actionManager;

    private $data;

    protected $cacheFile = 'data/cache/advanced/workflows.php';

    const AFTER_RECORD_SAVED = 'afterRecordSaved';
    const AFTER_RECORD_CREATED = 'afterRecordCreated';

    protected $entityListToIgnore = array();

    public function __construct(\Espo\Core\Container $container)
    {
        $this->container = $container;
        $this->conditionManager = new Workflow\ConditionManager($this->container);
        $this->actionManager = new Workflow\ActionManager($this->container);

        $this->entityListToIgnore = $this->container->get('metadata')->get('entityDefs.Workflow.entityListToIgnore');
    }

    protected function getContainer()
    {
        return $this->container;
    }

    protected function getConditionManager()
    {
        return $this->conditionManager;
    }

    protected function getActionManager()
    {
        return $this->actionManager;
    }

    protected function getConfig()
    {
        return $this->container->get('config');
    }

    protected function getEntityManager()
    {
        return $this->container->get('entityManager');
    }

    protected function getFileManager()
    {
        return $this->container->get('fileManager');
    }

    protected function getData($entityName = null, $workflowType = null, $returns = null)
    {
        if (!isset($this->data)) {
            $this->loadWorkflows();
        }

        if (isset($entityName) && isset($workflowType)) {
            if (isset($this->data[$workflowType] [$entityName])) {
                return $this->data[$workflowType] [$entityName];
            } else {
                return $returns;
            }
        }

        if (isset($workflowType)) {
            if (isset($this->data[$workflowType])) {
                return $this->data[$workflowType];
            } else {
                return $returns;
            }
        }

        if (isset($entityName)) {
            return $returns;
        }

        return $this->data;
    }

    /**
     * Run workflow rule
     *
     * @param  \Espo\Orm\Entity $entity
     * @param  string           $workflowType
     * @return void
     */
    public function process(\Espo\Orm\Entity $entity, $workflowType)
    {
        $entityName = $entity->getEntityName();
        if (in_array($entityName, $this->entityListToIgnore)) {
            return;
        }

        //skip this workflow to avoid loops
        if (isset($entity->skipHooks) && $entity->skipHooks) {
            $entity->skipHooks = false;
            return;
        }

        $data = $this->getData($entityName, $workflowType);

        if (isset($data) && is_array($data)) {

            $GLOBALS['log']->debug('WorkflowManager: Start workflow ['.$workflowType.'] for Entity ['.$entityName.', '.$entity->id.'].');

            $conditionManager = $this->getConditionManager();
            $actionManager = $this->getActionManager();

            foreach ($data as $workflowId => $workflowData) {

                $GLOBALS['log']->debug('WorkflowManager: Start workflow rule ['.$workflowId.'].');

                $conditionManager->setInitData($workflowId, $entity);

                $result = true;
                if (isset($workflowData['conditionsAll'])) {
                    $result &= $conditionManager->compareConditionsAll($workflowData['conditionsAll']);
                }
                if (isset($workflowData['conditionsAny'])) {
                    $result &= $conditionManager->compareConditionsAny($workflowData['conditionsAny']);
                }

                $GLOBALS['log']->debug('WorkflowManager: Condition result ['.(bool) $result.'] for workflow rule ['.$workflowId.'].');

                if ($result && isset($workflowData['actions'])) {

                    $GLOBALS['log']->debug('WorkflowManager: Start running Actions for workflow rule ['.$workflowId.'].');

                    $actionManager->setInitData($workflowId, $entity);

                    try {
                        $actionResult = $actionManager->runActions($workflowData['actions']);
                    } catch (\Exception $e) {
                        $GLOBALS['log']->error('Workflow: failed action execution for workflow [' . $workflowId . ']. Details: '. $e->getMessage());
                    }

                    $GLOBALS['log']->debug('WorkflowManager: End running Actions for workflow rule ['.$workflowId.'].');
                }

                $GLOBALS['log']->debug('WorkflowManager: End workflow rule ['.$workflowId.'].');
            }

            $GLOBALS['log']->debug('WorkflowManager: End workflow ['.$workflowType.'] for Entity ['.$entityName.', '.$entity->id.'].');
        }
    }

    /**
     * Load workflows
     *
     * @return void
     */
    public function loadWorkflows($reload = false)
    {
        if (!$reload && $this->getConfig()->get('useCache') && file_exists($this->cacheFile)) {
            $this->data = $this->getFileManager()->getPhpContents($this->cacheFile);
            return;
        }

        $this->data = $this->getWorkflowData();

        if ($this->getConfig()->get('useCache')) {
            $this->getFileManager()->putPhpContents($this->cacheFile, $this->data);
        }
    }

    /**
     * Get all workflows from database and save into cache
     *
     * @return array
     */
    protected function getWorkflowData()
    {
        $requiredFields = array(
            'conditions_all',
            'conditions_any',
            'actions',
        );

        $data = array();

        $pdo = $this->getEntityManager()->getPDO();

        $sql = "SELECT * FROM `workflow` WHERE `is_active` = 1 AND `deleted` = 0 ";
        $sth = $pdo->prepare($sql);
        $sth->execute();

        $records = $sth->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($records as $row) {

            $rowData = array();
            foreach ($requiredFields as $fieldName) {

                if (isset($row[$fieldName])) {

                    $ccFieldName = Util::toCamelCase($fieldName);

                    $fieldValue = $row[$fieldName];
                    if (Json::isJSON($fieldValue)) {
                        $fieldValue = Json::decode($fieldValue, true);
                    }
                    if (!empty($fieldValue)) {
                        $rowData[$ccFieldName] = $fieldValue;
                    }
                }
            }

            $data[$row['type']] [$row['entity_type']] [$row['id']] = $rowData;
        }

        return $data;
    }
}