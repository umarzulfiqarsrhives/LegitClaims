<?php


namespace Espo\Modules\Advanced\Controllers;

use \Espo\Core\Exceptions\BadRequest;
use \Espo\Core\Exceptions\Error;

class Quote extends \Espo\Core\Controllers\Record
{

    public function actionGetAttributesFromOpportunity($params, $data, $request)
    {
        $opportunityId = $request->get('opportunityId');
        if (empty($opportunityId)) {
            throw new BadRequest();
        }

        return $this->getRecordService()->getAttributesFromOpportunity($opportunityId);
    }

    public function postActionGetAttributesFromEmail($params, $data)
    {
        if (empty($data['quoteId']) || empty($data['templateId'])) {
            throw new BadRequest();
        }

        return $this->getRecordService()->getAttributesFromEmail($data['quoteId'], $data['templateId']);
    }
}
