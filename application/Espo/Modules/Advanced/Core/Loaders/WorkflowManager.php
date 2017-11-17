<?php


namespace  Espo\Modules\Advanced\Core\Loaders;

class WorkflowManager extends \Espo\Core\Loaders\Base
{
    public function load()
    {
        return new \Espo\Modules\Advanced\Core\WorkflowManager($this->getContainer());
    }
}