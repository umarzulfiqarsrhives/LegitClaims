<?php

namespace Espo\Modules\RealEstate\Controllers;

use \Espo\Core\Exceptions\Forbidden;
use \Espo\Core\Exceptions\BadRequest;
use \Espo\Core\Exceptions\NotFound;

class RealEstateProperty extends \Espo\Core\Templates\Controllers\Base
{
    public function postActionSetNotInterested($params, $data, $request)
    {
        if (empty($data['requestId']) || empty($data['propertyId'])) {
            throw new BadRequest();
        }
        return $this->getRecordService()->setNotIntereseted($data['propertyId'], $data['requestId']);
    }

    public function postActionUnsetNotInterested($params, $data, $request)
    {
        if (empty($data['requestId']) || empty($data['propertyId'])) {
            throw new BadRequest();
        }
        return $this->getRecordService()->unsetNotIntereseted($data['propertyId'], $data['requestId']);
    }
}
