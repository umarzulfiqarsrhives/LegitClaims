<?php


namespace Espo\Modules\Advanced\Core\MailChimp;

use \Espo\ORM\Entity;

class BaseRecipientHook extends \Espo\Core\Hooks\Base
{
    public static $order = 15;

    public function beforeSave(Entity $entity)
    {
        $integration = $this->getEntityManager()->getEntity("Integration", "MailChimp");

        if (empty($integration) || !$integration->get('enabled')) {
            return;
        }

        if (!$entity->isNew() && $entity->isFieldChanged('emailAddressData')) {
            $entity->loadLinkMultipleField('targetLists');
            $targetListsIds = $entity->get('targetListsIds');
            if (empty($targetListsIds)) {
                return;
            }
            $now = new \DateTime("NOW", new \DateTimeZone('UTC'));
            $executeTime = $now->format("Y-m-d H:i:s");

            $oldEntity = $this->getEntityManager()->getEntity($entity->getEntityName(), $entity->id);
            $mcHelper = new \Espo\Modules\Advanced\Core\MailChimp\RecipientHelper($this->getEntityManager(), $this->getMetadata());

            if ($entity->isFetched('emailAddress') &&
                $entity->get('emailAddress') &&
                $entity->getFetched('emailAddress')) {

                $data = array();
                $data['listIds'] = $targetListsIds;
                $recipient = $oldEntity->toArray();
                $recipient['scope'] = $oldEntity->getEntityName();
                $parsedRecipient = $mcHelper->prepareRecipientToMailChimp($recipient);
                $parsedRecipient['newEmailAddress'] = $entity->get('emailAddress');
                $data['data'] = $mcHelper->formatSubscriber($parsedRecipient, true);
                $job = $this->getEntityManager()->getEntity('Job');
                $job->set('serviceName', 'MailChimp');
                $job->set('method', 'updateMailChimpRecipientEmailAddress');
                $job->set('executeTime', $executeTime);
                $job->set('data', json_encode($data));
                $this->getEntityManager()->saveEntity($job);
            }
        }
    }

    public function beforeRemove(Entity $entity)
    {
        $integration = $this->getEntityManager()->getEntity("Integration", "MailChimp");
        $jobMethodName = 'deleteMailChimpRecipientFromList';
        if (empty($integration) || !$integration->get('enabled')) {
            return;
        }

        if ($entity->get('emailAddress')) {
        
            $entity->loadLinkMultipleField('targetLists');
            $targetListsIds = $entity->get('targetListsIds');
            if (empty($targetListsIds)) {
                return;
            }
            $existingJobs = $this->getEntityManager()->getRepository('Job')->where(array(
                'method' => $jobMethodName,
                'status' => 'Pending',
                'data*' => "%" . $entity->get('emailAddress') ."%",
            ))->find();

            if (is_object($existingJobs) && count($existingJobs->toArray()) > 0) {
                return;
            }
            $now = new \DateTime("NOW", new \DateTimeZone('UTC'));
            $executeTime = $now->format("Y-m-d H:i:s");


            $data = array(
                'listIds' => $targetListsIds,
                'emailAddress' => $entity->get('emailAddress')
            );
            $job = $this->getEntityManager()->getEntity('Job');
            $job->set('serviceName', 'MailChimp');
            $job->set('method', $jobMethodName);
            $job->set('executeTime', $executeTime);
            $job->set('data', json_encode($data));
            $this->getEntityManager()->saveEntity($job);
        }
    }
}
