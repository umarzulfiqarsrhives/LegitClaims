<?php


namespace Espo\Modules\Advanced\Reports;

use \Espo\Core\Container;

abstract class Base
{
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    protected function getContainer()
    {
        return $this->container;
    }

    protected function getEntityManager()
    {
        return $this->getContainer()->get('entityManager');
    }


    protected function getMetadata()
    {
        return $this->getContainer()->get('metadata');
    }

    protected function getLanguage()
    {
        return $this->getContainer()->get('language');
    }

    abstract public function run($where = null, array $params = null);
}

