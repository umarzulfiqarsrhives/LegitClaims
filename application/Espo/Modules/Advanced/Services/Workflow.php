<?php
namespace Espo\Modules\Advanced\Services;

use Espo\Core\Exceptions\Error;
use Espo\ORM\Entity;

class Workflow extends \Espo\Services\Record
{
    protected function init()
    {
        $this->dependencies[] = 'mailSender';
        $this->dependencies[] = 'workflowHelper';
    }

    protected function getMailSender()
    {
        return $this->getInjection('mailSender');
    }

    protected function getWorkflowHelper()
    {
        return $this->getInjection('workflowHelper');
    }

    /**
     * Send email defined in workflow
     *
     * @param  array $data  See validateSendEmailData method
     * @return bool
     */
    public function sendEmail(array $data)
    {
        if (!$this->validateSendEmailData($data)) {
            throw new Error('Workflow['.$data['workflowId'].'][sendEmail]: Email data is broken.');
        }

        $entityManager = $this->getEntityManager();

        $entity = $entityManager->getEntity($data['entityName'], $data['entityId']);
        if (!isset($entity)) {
            throw new Error('Workflow['.$data['workflowId'].'][sendEmail]: Entity is empty.');
        }

        $toEmail = $this->getEmailAddress($data['to']);
        $fromEmail = $this->getEmailAddress($data['from']);
        if (empty($toEmail) || empty($fromEmail)) {
            throw new Error('Workflow['.$data['workflowId'].'][sendEmail]: Email address is empty.');
        }

        $this->loadLinkMultipleFields($entity);
        $this->loadParentNameFields($entity);

        $entityHash = array(
            $data['entityName'] => $entity,
        );

        if (isset($data['to']['entityName'])) {
            $toEntity = $data['to']['entityName'];
            $entityHash[$toEntity] = $entityManager->getEntity($toEntity, $data['to']['entityId']);
        }

        if (isset($data['from']['entityName']) && $data['from']['entityName'] == 'User') {
            $entityHash['User'] = $entityManager->getEntity('User', $data['from']['entityId']);
            $fromName = $entityHash['User']->get('name');
        }

        $emailTemplateParams = array(
            'entityHash' => $entityHash,
            'emailAddress' => $toEmail,
        );
        if ($entity->hasField('parentId') && $entity->hasField('parentType')) {
            $emailTemplateParams['parentId'] = $entity->get('parentId');
            $emailTemplateParams['parentType'] = $entity->get('parentType');
        }

        $emailTemplateService = $this->getServiceFactory()->create('EmailTemplate');
        $emailTemplate = $emailTemplateService->parse($data['emailTemplateId'], $emailTemplateParams, true);

        $emailData = array(
            'from' => $fromEmail,
            'to' => $toEmail,
            'subject' => $emailTemplate['subject'],
            'body' => $emailTemplate['body'],
            'isHtml' => $emailTemplate['isHtml'],
            'parentId' => $entity->id,
            'parentType' => $entity->getEntityName(),
        );

        if (isset($fromName)) {
            $emailData['fromName'] = $fromName;
        }

        $email = $entityManager->getEntity('Email');
        $email->set($emailData);

        if (!empty($emailTemplate['attachmentsIds'])) {
            $email->set('attachmentsIds', $emailTemplate['attachmentsIds']);
            $entityManager->saveEntity($email);
        }

        $sendExceptionMessage = null;
        try {
            $result = $this->getMailSender()->send($email);
            $entityManager->saveEntity($email);
        } catch (\Exception $e) {
            $sendExceptionMessage = $e->getMessage();
        }

        if (isset($sendExceptionMessage)) {
            throw new Error('Workflow['.$data['workflowId'].'][sendEmail]: '.$sendExceptionMessage.'.');
        }

        return isset($result) ? $result : false;
    }

    /**
     * Validate sendEmail data
     *
     * @param  array  $data
     * @return bool
     */
    protected function validateSendEmailData(array $data)
    {
        $requiredParams = array(
            'entityId',
            'entityName',
            'emailTemplateId',
            'to',
            'from',
        );

        foreach ($requiredParams as $name) {
            if (!isset($data[$name])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get email address depends on inputs
     * @param  array $data
     * @return string
     */
    protected function getEmailAddress(array $data)
    {
        if (isset($data['email'])) {
            return $data['email'];
        }

        if (isset($data['entityName']) && isset($data['entityId'])) {
            $entity = $this->getEntityManager()->getEntity($data['entityName'], $data['entityId']);
        }

        if (isset($data['type'])) {
            $workflowHelper = $this->getWorkflowHelper();

            switch ($data['type']) {
                case 'specifiedTeams':
                    $userIds = $workflowHelper->getUserIdsByTeamIds($data['teamsIds']);
                    return implode('; ', $workflowHelper->getUsersEmailAddress($userIds));
                    break;

                case 'teamUsers':
                    $entity->loadLinkMultipleField('teams');
                    $userIds = $workflowHelper->getUserIdsByTeamIds($entity->get('teamsIds'));
                    return implode('; ', $workflowHelper->getUsersEmailAddress($userIds));
                    break;

                case 'followers':
                    $userIds = $workflowHelper->getFollowerUserIds($entity);
                    return implode('; ', $workflowHelper->getUsersEmailAddress($userIds));
                    break;
            }
        }

        if ($entity instanceof Entity && $entity->hasField('emailAddress')) {
            return $entity->get('emailAddress');
        }
    }
}

