<?php


namespace Espo\Modules\Advanced\Repositories;

use \Espo\ORM\EntityManager;
use \Espo\ORM\EntityFactory;

class MailChimp extends \Espo\Core\ORM\Repositories\RDB
{

    protected $campaignFields = array(
        'mailChimpCampaignId',
        'mailChimpCampaignName',
        'mailChimpCampaignWebId',
        'mailChimpCampaignStatus',
    );
    protected $targetListFields = array(
        'mailChimpListId',
        'mailChimpListName',
        'mcListGroupingId',
        'mcListGroupId',
        'mcListGroupingName',
        'mcListGroupName',
    );

    public function __construct($entityName, EntityManager $entityManager, EntityFactory $entityFactory)
    {
        $this->entityName = $entityName;
        $this->entityFactory = $entityFactory;
        $this->entityManager = $entityManager;
        $this->init();
    }

    protected function init()
    {
        $this->dependencies[] = 'acl';
        $this->dependencies[] = 'user';
    }

    protected function getMapper()
    {
        if (empty($this->mapper)) {
            $this->mapper = $this->getEntityManager()->getMapper('RDB');
        }
        return $this->mapper;
    }

    protected function getAcl()
    {
        return $this->injections['acl'];
    }

    protected function getUser()
    {
        return $this->getInjection('user');
    }

    protected function getPDO()
    {
        return $this->getEntityManager()->getPDO();
    }

    public function saveRelations($data)
    {
        switch ($data['foreignEntity']) {
            case "Campaign": return $this->saveCampaignRelations($data);
            case "TargetList": return $this->saveTargetListRelations($data);
        }
        return false;
    }

    private function saveCampaignRelations($data)
    {
        $idsUpdated = array();
        $campaignId = $data['id'];
        $entity = $this->getEntityManager()->getEntity('Campaign', $campaignId);
        if (!empty($entity)) {
            if ($this->getAcl()->check($entity, 'edit')) {
                foreach($this->campaignFields as $fieldName) {
                    if ($entity->hasField($fieldName)){
                        $entity->set($fieldName, $data[$fieldName]);
                    }
                }
                if ($this->getEntityManager()->saveEntity($entity)) {
                    $idsUpdated[] = $campaignId;
                }
            }
        }

        foreach ($data as $name => $value) {
            if (strpos($name, '_') !== false) {
                list($id, $field) = explode('_', $name);
                $entity = $this->getEntityManager()->getEntity('TargetList', $id);
                if (isset($entity->id) && $entity->id && in_array($field, $this->targetListFields) && $entity->get($field) != $value) {
                    if ($this->getAcl()->check($entity, 'edit')) {
                        $entity->set($field, $value);
                        if ($this->getEntityManager()->saveEntity($entity) && !in_array($id, $idsUpdated)) {
                            $idsUpdated[] = $id;
                        }
                    } 
                }
            }
        }

        return $idsUpdated;
    }

    private function saveTargetListRelations($data)
    {
        $idsUpdated = array();
        $targetListId = $data['id'];
        $entity = $this->getEntityManager()->getEntity('TargetList', $targetListId);

        if (!empty($entity)) {
            if ($this->getAcl()->check($entity, 'edit')) {
                foreach($this->targetListFields as $fieldName) {
                    if ($entity->hasField($fieldName)){
                        $entity->set($fieldName, $data[$fieldName]);
                    }
                }
                if ($this->getEntityManager()->saveEntity($entity)) {
                    $idsUpdated[] = $targetListId;
                }
            }
        }
        return $idsUpdated;
    }

    public function loadRelations($id)
    {
        $result = array(
            'id' => $id,
            'scope' => 'MailChimp',
            'syncIsRunning' => false
        ); 

        $campaign = $this->getEntityManager()->getEntity('Campaign', $id);
        if (!empty($campaign)) {
            if ($this->getAcl()->check($campaign, 'read')) {
                foreach ($this->campaignFields as $fieldName) {
                    $result[$fieldName] = $campaign->get($fieldName);
                }
                $result['syncIsRunning'] = $campaign->get('mailChimpManualSyncRun');
            } else {
                throw new Forbidden();
            }

            $campaign->loadLinkMultipleField('targetLists');

            $targetListsIds = $campaign->get('targetListsIds');
            $result['targetListsIds'] = array();
            foreach ($targetListsIds as $targetListId) {
                $targetList = $this->getEntityManager()->getEntity('TargetList', $targetListId);

                if (!empty($targetList)) {
                    if ($this->getAcl()->check($targetList, 'read')) {
                        $targetListData = array();
                        $targetListData['id'] = $targetList->id;
                        $targetListData['name'] = $targetList->get('name');

                        foreach ($this->targetListFields as $fieldName) {
                            $targetListData[$fieldName] = $targetList->get($fieldName);
                        }

                        if (!empty($targetListData['mcListGroupingId']) && empty($targetListData['mcListGroupId'])) {
                            $targetListData['mcListGroupId'] = $targetListData['mcListGroupingId'];
                            $targetListData['mcListGroupName'] = $targetListData['mcListGroupingName'];
                            $targetListData['mcListGroupingId'] = '';
                            $targetListData['mcListGroupingName'] = '';
                        }
                        foreach($targetListData as $field => $value) {
                            $result[$targetList->id . '_' . $field] = $value;
                        }
                        //$result['targetListsData'][$targetListId] = $targetListData;
                        $result['targetListsIds'][] = $targetListId;
                    }
                }
            }
        } else {
            $targetList = $this->getEntityManager()->getEntity('TargetList', $id);
            if (!empty($targetList)) {
                if ($this->getAcl()->check($targetList, 'read')) {

                    foreach ($this->targetListFields as $fieldName) {
                        $result[$fieldName] = $targetList->get($fieldName);
                    }
                    $result['syncIsRunning'] = $targetList->get('syncIsRunning');

                    if (!empty($result['mcListGroupingId']) && empty($result['mcListGroupId'])) {
                        $result['mcListGroupId'] = $result['mcListGroupingId'];
                        $result['mcListGroupName'] = $result['mcListGroupingName'];
                        $result['mcListGroupingId'] = '';
                        $result['mcListGroupingName'] = '';
                    }

                } else {
                    throw new Forbidden();
                }
            }
        }
        return $result;
    }

    public function checkManualSyncs()
    {
        $currentUser = $this->getUser();
        $pdo = $this->getEntityManager()->getPDO();
        $result = array();

        $activeSyncs = $this->getEntityManager()->getRepository('MailChimpManualSync')
            ->where(array(
                'assignedUserId' => $currentUser->id,
                'completed' => false
            ))
            ->find(array(
                'orderBy' => 'createdAt'
            ));

        if (empty($activeSyncs)) {
            return array();
        }
        foreach ($activeSyncs as $sync) {
            $jobIds = $sync->get('jobs');
            $completed = true;
            $failed = false;
            $executeTime = '';
            foreach ($jobIds as $jobId) {
                $job = $this->getEntityManager()->getEntity('Job', $jobId);
                if (!empty($job)) {
                    $status = $job->get('status');
                    if (in_array($status, array('Pending', 'Running'))) {
                        $completed = false;
                        break;
                    }
                    if (!$failed && $status == "Failed") {
                        $failed = true;
                    }
                    $executeTime = ($job->get('executeTime') > $executeTime) ? $job->get('executeTime') : $executeTime;
                }
            }
            if ($completed) {
                $sync->set('completed', true);
                $this->getEntityManager()->saveEntity($sync);
                $parent = $this->getEntityManager()->getEntity($sync->get('parentType'), $sync->get('parentId'));
                $data = null;
                if (!empty($parent)) {
                    if (empty($executeTime)) {
                        $executeTime = $campaign->get('mailChimpLastSuccessfulUpdating');
                    }
                    $parent->set('mailChimpManualSyncRun', false);
                    $this->getEntityManager()->saveEntity($parent);

                    $data = array(
                        'entityType' => $parent->getEntityName(),
                        'entityName' => $parent->get('name'),
                        'id' => $parent->id,
                        'lastSynced' => $executeTime,
                        'failed' => $failed
                    );
                }
                $result[] = array(
                    'id' => $sync->id,
                    'data' => $data
                );
            }
        }
        return $result;
    }

    public function addSyncJob($entityName, $id)
    {
        $methodName = '';
        $fieldName = '';
        $job = null;
        if ($entityName == 'Campaign') {
            $methodName = 'updateCampaignLogFromMailChimp';
            $fieldName = 'campaignId';
        } else if ($entityName == 'TargetList') {
            $methodName = 'updateMCListRecipients';
            $fieldName = 'targetListId';
        }

        if (empty($methodName)) {
            throw \Error('Unknown entity type in scheduling MailChimp Sync');
        }

        $pdo = $this->getEntityManager()->getPDO();

        $sql = " SELECT id
            FROM job
            WHERE method=" . $pdo->quote($methodName). " AND
                service_name='MailChimp' AND
                status='Pending' AND
                deleted = 0 AND
                data LIKE " . $pdo->quote('%' .$fieldName. '%') . " AND
                data LIKE " .  $pdo->quote('%' .$id. '%') . "
            ORDER BY execute_time DESC
            ";

        $sth = $pdo->prepare($sql);
        $sth->execute();
        $row = $sth->fetch();
        if (!empty($row) && !empty($row['id'])) {
            $job = $this->getEntityManager()->getEntity('Job', $row['id']);
        }
        if (empty($job)) {
            $now = new \DateTime("NOW", new \DateTimeZone('UTC'));
            $job = $this->getEntityManager()->getEntity('Job');
            $job->set( array(
                    'method' => $methodName,
                    'serviceName' => 'MailChimp',
                    'executeTime' => $now->format("Y-m-d H:i:s"),
                    'data' => json_encode(array($fieldName => $id)),
                )
            );
            $this->getEntityManager()->saveEntity($job);
        }
        return $job;
    }

}
