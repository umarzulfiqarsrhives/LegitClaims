<?php


namespace Espo\Modules\Advanced\Core\Workflow\Actions;

use Espo\Core\Exceptions\Error;
use Espo\Modules\Advanced\Core\Workflow\Utils;

use Espo\ORM\Entity;

class SendEmail extends Base
{
    /**
     * Main run method
     *
     * @param  array $actionData
     * @return string
     */
    protected function run(Entity $entity, array $actionData)
    {
        $jobData = array(
            'workflowId' => $this->getWorkflowId(),
            'entityId' => $this->getEntity()->get('id'),
            'entityName' => $this->getEntity()->getEntityType(),
            'from' => $this->getEmailAddress('from'),
            'to' => $this->getEmailAddress('to'),
            'emailTemplateId' => $actionData['emailTemplateId'],
        );

        $job = $this->getEntityManager()->getEntity('Job');
        $job->set(array(
            'serviceName' => 'Workflow',
            'method' => 'sendEmail',
            'data' => json_encode($jobData),
            'executeTime' => $this->getExecuteTime(),
        ));

        return $this->getEntityManager()->saveEntity($job);
    }

    /**
     * Get execute time defined in workflow
     *
     * @return string
     */
    protected function getExecuteTime()
    {
        $data = $this->getActionData();
        $execution = $data['execution'];

        $executeTime = date('Y-m-d H:i:s');

        switch ($execution['type']) {
            case 'immediately':
                break;

            case 'later':
                if (!empty($execution['field'])) {
                   $executeTime =  Utils::getFieldValue($this->getEntity(), $execution['field']);
                }
                if (!empty($execution['shiftDays'])) {
                    $executeTime = Utils::shiftDays($execution['shiftDays'], $executeTime, 'Y-m-d H:i:s');
                }
                break;

            default:
                throw new Error('Workflow['.$this->getWorkflowId().']: Unknown execution type [' . $execution['type'] . ']');
                break;
        }

        return $executeTime;
    }

    /**
     * Get email address defined in workflow
     *
     * @param  string $type
     * @return array
     */
    protected function getEmailAddress($type = 'to', $returns = null)
    {
        $data = $this->getActionData();
        $fieldValue = $data[$type];

        switch ($fieldValue) {
            case 'specifiedEmailAddress':
                $emailAddress = array('email' => $data[$type . 'Email']);
                break;

            case 'teamUsers':
            case 'followers':
                $entity = $this->getEntity();

                $emailAddress = array(
                    'entityName' => $entity->getEntityType(),
                    'entityId' => $entity->get('id'),
                    'type' => $fieldValue,
                );
                break;

            case 'specifiedTeams':
                $emailAddress = array(
                    'type' => $fieldValue,
                    'teamsIds' => $data['toSpecifiedTeamsIds'],
                );
                break;

            case 'currentUser':
                $emailAddress = array(
                    'entityName' => $this->getContainer()->get('user')->getEntityType(),
                    'entityId' => $this->getContainer()->get('user')->get('id'),
                );
                break;

            default:
                $fieldEntity = Utils::getFieldValue($this->getEntity(), $fieldValue, true, $this->getEntityManager());
                if ($fieldEntity instanceof \Espo\ORM\Entity) {
                    $emailAddress = array(
                        'entityName' => $fieldEntity->getEntityType(),
                        'entityId' => $fieldEntity->get('id'),
                    );
                }
                break;
        }

        return (isset($emailAddress)) ? $emailAddress : $returns;
    }
}