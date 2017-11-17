<?php


namespace Espo\Modules\Advanced\Controllers;

use \Espo\Core\Exceptions\BadRequest;

class MailChimp extends \Espo\Core\Controllers\Record
{

    public function actionRead($params)
    {
        
        if (!$this->getAcl()->check('MailChimp') || !$this->getAcl()->check('Campaign', 'read') || !$this->getAcl()->check('TargetList', 'read')) {
            throw new Forbidden();
        }
        $id = $params['id'];
        return $this->getService('MailChimp')->loadRelations($id);
    }
    
    public function actionUpdate($params, $data)
    {
        if (!$this->getAcl()->check('MailChimp')) {
            throw new Forbidden();
        }
        return $this->getService('MailChimp')->saveRelation($params, $data);
    }
    
    public function actionScheduleSync($params)
    {
        if (!$this->getAcl()->check('MailChimp', 'read')) {
            throw new Forbidden();
        }
        $entity = $params['entity'];
        $id = $params['id'];
        return $this->getRecordService()->scheduleSync($entity, $id);
    }
    
    public function actionCheckSynchronization($params, $data)
    {
        return $this->getEntityManager()->getRepository('MailChimp')->checkManualSyncs();
    }
    
}
