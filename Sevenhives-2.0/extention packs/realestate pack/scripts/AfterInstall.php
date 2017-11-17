<?php

class AfterInstall
{
    protected $container;

    public function run($container)
    {
        $this->container = $container;

        $config = $this->container->get('config');

        $tabList = $config->get('tabList', []);
        array_unshift($tabList, 'RealEstateRequest');
        array_unshift($tabList, 'RealEstateProperty');

        $quickCreateList = $config->get('quickCreateList', []);
        array_unshift($quickCreateList, 'RealEstateRequest');
        array_unshift($quickCreateList, 'RealEstateProperty');


        $globalSearchEntityList = $config->get('globalSearchEntityList', []);
        array_unshift($globalSearchEntityList, 'RealEstateRequest');
        array_unshift($globalSearchEntityList, 'RealEstateProperty');

        if (!in_array('Opportunity', $globalSearchEntityList)) {
            $globalSearchEntityList[] = 'Opportunity';
        }
        if (!in_array('Contact', $globalSearchEntityList)) {
            $globalSearchEntityList[] = 'Contact';
        }
        if (!in_array('Account', $globalSearchEntityList)) {
            $globalSearchEntityList[] = 'Account';
        }

        $config->set('tabList', $tabList);
        $config->set('quickCreateList', $quickCreateList);
        $config->set('globalSearchEntityList', $globalSearchEntityList);
        $config->set('saleMarkup', 5);
        $config->set('rentMarkup', 50);

        $config->save();

        $this->clearCache();
    }

    protected function clearCache()
    {
        try {
            $this->container->get('dataManager')->clearCache();
        } catch (\Exception $e) {}
    }
}
