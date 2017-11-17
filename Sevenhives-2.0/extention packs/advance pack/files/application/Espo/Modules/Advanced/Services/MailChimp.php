<?php


namespace Espo\Modules\Advanced\Services;

use \Espo\ORM\Entity;

use \Espo\Core\Exceptions\Error;
use \Espo\Core\Exceptions\NotFound;
use \Espo\Core\Exceptions\Forbidden;

class MailChimp extends \Espo\Services\Record
{

    protected function init()
    {
        $this->dependencies[] = 'language';
        $this->dependencies[] = 'container';
        $this->dependencies[] = 'acl';
    }

    protected function getLanguage()
    {
        return $this->injections['language'];
    }

    protected function getAcl()
    {
        return $this->injections['acl'];
    }

    protected function getContainer()
    {
        return $this->injections['container'];
    }
    
    protected function getMailChimpManager()
    {
        return new \Espo\Modules\Advanced\Core\MailChimp\MailChimpManager($this->getEntityManager(), $this->getMetadata(), $this->getConfig());
    }
    
    protected function getMailChimpSynchronizer()
    {
        return new \Espo\Modules\Advanced\Core\MailChimp\Synchronizer($this->getEntityManager(), $this->getMetadata(), $this->getConfig());
    }
    
    public function getCampaignsByOffset($params)
    {
        return $this->getMailChimpManager()->getCampaignsByOffset($params);
    }
    
    public function getListsByOffset($params)
    {
        return $this->getMailChimpManager()->getListsByOffset($params);
    }
    
    public function getListGroups($listId)
    {
        return $this->getMailChimpManager()->getListGroups($listId);
    }
    
    public function saveRelation($params, $data)
    {
        return $this->getRepository()->saveRelations($data);
    }
    
    public function loadRelations($campaignId)
    {
        return $this->getRepository()->loadRelations($campaignId);
    }
    
    public function scheduleSync($entityName, $id)
    {
        $entity = $this->getEntityManager()->getEntity($entityName, $id);
        if (empty($entity)) {
            throw NotFound();
        }
        $pdo = $this->getEntityManager()->getPDO();
        
        $sql = "UPDATE mail_chimp_manual_sync 
            SET completed=1 
            WHERE completed=0 AND 
                parent_type=" . $pdo->quote($entityName) . " AND 
                parent_id=" . $pdo->quote($id);
        
        $sth = $pdo->prepare($sql);
        $sth->execute();
    
        $jobIds = array();
        
        $job = $this->getRepository()->addSyncJob($entityName, $id);
        $jobIds[] = $job->id;
        if ($entityName == 'Campaign') {
            $entity->loadLinkMultipleField('targetLists');
            $targetListsIds = $entity->get('targetListsIds');
            foreach ($targetListsIds as $targetListId) {
                $job = $this->getRepository()->addSyncJob('TargetList', $targetListId);
                $jobIds[] = $job->id;
            }
        }    
        if (!empty($jobIds)) {
            $manSyncEntity = $this->getEntityManager()->getEntity('MailChimpManualSync');
            $manSyncEntity->set('assignedUserId', $this->getUser()->id);
            $manSyncEntity->set('parentType', $entityName);
            $manSyncEntity->set('parentId', $id);
            $manSyncEntity->set('jobs', json_encode($jobIds));
            $this->getEntityManager()->saveEntity($manSyncEntity);
            
            $entity->set('mailChimpManualSyncRun' , true);
            $this->getEntityManager()->saveEntity($entity);
        }
        return array();
    }
    
    public function updateMCListRecipients($data)
    {
        try {
            $targetListId = (isset($data['targetListId'])) ? $data['targetListId'] : '';
            if (!empty($targetListId)) {
                $targetList = $this->getEntityManager()->getEntity('TargetList', $targetListId);
                if (!empty($targetList)) {
                    return $this->getMailChimpSynchronizer()->updateMCRecipients($targetList);
                }    
            }
        } catch (\Exception $e) {
            $GLOBALS['log']->error('MailChimp (updateMCListRecipients) : ' . $e->getMessage());
            return false;
        } 
        return true;
    }
    
    public function updateCampaignLogFromMailChimp($data)
    {
        try {
            $campaignId = (isset($data['campaignId'])) ? $data['campaignId'] : '';
            if (!empty($campaignId)) {
                $campaign = $this->getEntityManager()->getEntity('Campaign', $campaignId);
                if (!empty($campaign)) {
                    return $this->getMailChimpSynchronizer()->loadLogDataForCampaign($campaign);
                }    
            }
        } catch (\Exception $e) {
            $GLOBALS['log']->error('MailChimp (updateCampaignLogFromMailChimp) : ' . $e->getMessage());
            return false;
        } 
        return true;
    }
    
    public function updateMailChimpRecipientEmailAddress($data)
    {
        try {
            $listIds = (isset($data['listIds'])) ? $data['listIds'] : '';
            if (is_array($listIds)) {
                foreach ($listIds as $listId) {
                    $targetList = $this->getEntityManager()->getEntity('TargetList', $listId);
                    if (!empty($targetList)) {
                        $mailChimpListId = $targetList->get('mailChimpListId');
                        if (empty($mailChimpListId) || empty($data['data'])) {
                            continue;
                        }
                        $this->getMailChimpManager()->updateMember($mailChimpListId, $data['data']);
                    }
                }
            }
        } catch (\Exception $e) {
            $GLOBALS['log']->error('MailChimp (updateMailChimpRecipientEmailAddress) : ' . $e->getMessage());
            return false;
        } 
        return true;
    }

    public function deleteMailChimpRecipientFromList($data)
    {
        try {
            $listIds = (isset($data['listIds'])) ? $data['listIds'] : '';
            if (is_array($listIds)) {
                foreach ($listIds as $listId) {
                    $targetList = $this->getEntityManager()->getEntity('TargetList', $listId);
                    if (!empty($targetList)) {
                        $mailChimpListId = $targetList->get('mailChimpListId');
                        if (empty($mailChimpListId) || empty($data['emailAddress'])) {
                            continue;
                        }
                        $this->getMailChimpManager()->deleteMember($mailChimpListId, $data['emailAddress']);
                    }
                }
            }
        } catch (\Exception $e) {
            $GLOBALS['log']->error('MailChimp (deleteMailChimpRecipientFromList ' . $data['emailAddress'] .') : ' . $e->getMessage());
            return false;
        } 
        return true;
    }

    public function scheduleAllSync()
    {
        $this->getMailChimpSynchronizer()->scheduleCampaignsSync();
        $this->getMailChimpSynchronizer()->scheduleTargetListsSync();
    }
    
    public function createCampaign($data)
    {
        return $this->getMailChimpManager()->createCampaign($data);
    }
    
    public function createList($data)
    {
        return $this->getMailChimpManager()->createList($data);
    }
    
    public function createListGrouping($data)
    {
        //return $this->getMailChimpManager()->createList($data);
    }
    
    public function createListGroup($data)
    {
        //return $this->getMailChimpManager()->createList($data);
    }
    
    public function getGroupTree($listId)
    {
        return $this->getMailChimpManager()->getListGroupsTree($listId);
    }

}
