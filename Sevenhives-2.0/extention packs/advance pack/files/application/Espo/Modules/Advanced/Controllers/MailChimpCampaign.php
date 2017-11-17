<?php


namespace Espo\Modules\Advanced\Controllers;

use \Espo\Core\Exceptions\BadRequest;
use \Espo\Core\Exceptions\Forbidden;
use \Espo\Core\Exceptions\Error;

class MailChimpCampaign extends \Espo\Core\Controllers\Base
{

    public static $defaultAction = 'list';
    
    public function actionList($params, $data, $request)
    {
        
        if (!$this->getAcl()->check('MailChimp')) {
            throw new Forbidden();
        }

        $where = $request->get('where');
        $offset = $request->get('offset');
        $maxSize = $request->get('maxSize');
        $asc = $request->get('asc') === 'true';
        $sortBy = $request->get('sortBy');
        $q = $request->get('q');

        $nameFilter = '';
        if (!empty($q)) {
            $nameFilter = $q;
        } else if (!empty($where)) {
            $nameFilter = $where[0]['value'];
        }
    
        $result = $this->getService('MailChimp')->getCampaignsByOffset( array(
            'offset' => $offset,
            'maxSize' => $maxSize,
            'asc' => $asc,
            'sortBy' => $sortBy,
            'filter' => $nameFilter,
            //'q' => $q, 
            )
        );

        return $result;
    }
    
    public function actionCreate($params, $data)
    {
        if (!$this->getAcl()->check('MailChimp', 'edit')) {
            throw new Forbidden();
        }

        $service = $this->getService('MailChimp');

        if ($campaign = $service->createCampaign($data)) {
            return $campaign;
        }

        throw new Error();
    }
}
