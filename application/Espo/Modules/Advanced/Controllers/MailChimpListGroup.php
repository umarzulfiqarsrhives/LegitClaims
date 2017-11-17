<?php


namespace Espo\Modules\Advanced\Controllers;

use \Espo\Core\Exceptions\BadRequest;

class MailChimpListGroup extends \Espo\Core\Templates\Controllers\CategoryTree
{

    public static $defaultAction = 'listTree';
    
    public function actionListTree($params, $data, $request)
    {
        
        if (!$this->getAcl()->check('MailChimp')) {
            throw new Forbidden();
        }
        
        $where = $request->get('where');
        $listId = '';
        foreach($where as $condition) {
            if ($condition['field'] == 'listId') {
                $listId = $condition['value'];
                break;
            }
        }
        return array(
            'list' => $this->getService('MailChimp')->getGroupTree($listId),
            'path' => array()
        );
    }
    
    public function actionCreate($params, $data)
    {
        if (!$this->getAcl()->check('MailChimp', 'edit')) {
            throw new Forbidden();
        }

        $service = $this->getService('MailChimp');

        if ($listGroup = $service->createListGroup($data)) {
            //return $entity->toArray();
            return $listGroup;
        }

        throw new Error();
    }
}
