<?php


namespace Espo\Modules\Crm\Jobs;

use \Espo\Core\Exceptions;

class ControlKnowledgeBaseArticleStatus extends \Espo\Core\Jobs\Base
{
    public function run()
    {
        $list = $this->getEntityManager()->getRepository('KnowledgeBaseArticle')->where(array(
            'expirationDate<=' => date('Y-m-d'),
            'status' => 'Published'
        ))->find();

        foreach ($list as $e) {
            $e->set('status', 'Archived');
            $this->getEntityManager()->saveEntity($e);
        }

        return true;
    }
}

