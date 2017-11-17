<?php

namespace Espo\Modules\Advanced\Hooks\Quote;

use Espo\ORM\Entity;

class QuoteItem extends \Espo\Core\Hooks\Base
{

    public function beforeSave(Entity $entity)
    {
        if (!$entity->has('itemList')) {
            return;
        }

        $itemList = $entity->get('itemList');

        if (!is_array($itemList)) {
            return;
        }

        if ($entity->has('amountCurrency')) {
            foreach ($itemList as $o) {
                $o->listPriceCurrency = $entity->get('amountCurrency');
                $o->unitPriceCurrency = $entity->get('amountCurrency');
                $o->amountCurrency = $entity->get('amountCurrency');
            }
        }

        if (count($itemList)) {
            $amount = 0.0;
            foreach ($itemList as $o) {
                $amount += $o->amount;
            }
            $amount = round($amount, 2);
            $entity->set('amount', $amount);
        }
    }

    public function afterSave(Entity $entity)
    {

        if (!$entity->has('itemList')) {
            return;
        }

        $itemList = $entity->get('itemList');

        if (!is_array($itemList)) {
            return;
        }

        $toCreateList = [];
        $toUpdateList = [];
        $toRemoveList = [];

        if (!$entity->isNew()) {
            $prevItemCollection = $this->getEntityManager()->getRepository('QuoteItem')->where(array(
                'quoteId' => $entity->id
            ))->order('order')->find();
            foreach ($prevItemCollection as $item) {
                $exists = false;
                foreach ($itemList as $data) {
                    if ($item->id === $data->id) {
                        $exists = true;
                    }
                }
                if (!$exists) {
                    $toRemoveList[] = $item;
                }
            }
        }

        $order = 0;
        foreach ($itemList as $o) {
            $order++;
            $exists = false;
            if (!$entity->isNew()) {
                foreach ($prevItemCollection as $item) {
                    if ($o->id === $item->id) {
                        $this->setItemWithData($item, $o);
                        $item->set('order', $order);
                        $item->set('quoteId', $entity->id);
                        $exists = true;
                        $toUpdateList[] = $item;
                        break;
                    }
                }
            }

            if (!$exists) {
                $item = $this->getEntityManager()->getEntity('QuoteItem');
                $this->setItemWithData($item, $o);
                $item->set('order', $order);
                $item->set('quoteId', $entity->id);
                $item->id = null;
                $toCreateList[] = $item;
            }
        }

        if ($entity->isNew()) {
            foreach ($toUpdateList as $item) {
                $item->id = null;
                $toCreateList[] = $item;
            }
            $toUpdateList = [];
        }

        foreach ($toRemoveList as $item) {
            $this->getEntityManager()->removeEntity($item);
        }

        foreach ($toUpdateList as $item) {
            $this->getEntityManager()->saveEntity($item);
        }

        foreach ($toCreateList as $item) {
            $this->getEntityManager()->saveEntity($item);
        }


        $itemCollection = $this->getEntityManager()->getRepository('QuoteItem')->where(array(
            'quoteId' => $entity->id
        ))->order('order')->find();

        $entity->set('itemList', $itemCollection->toArray());
    }

    protected function setItemWithData(Entity $item, \StdClass $o)
    {
        $item->set(array(
            'id' => $o->id,
            'name' => $o->name,
            'listPrice' => $o->listPrice,
            'listPriceCurrency' => $o->listPriceCurrency,
            'unitPrice' => $o->unitPrice,
            'unitPriceCurrency' => $o->unitPriceCurrency,
            'amount' => $o->amount,
            'amountCurrency' => $o->amountCurrency,
            'taxRate' => $o->taxRate,
            'productId' => $o->productId,
            'productName' => $o->productName,
            'quantity' => $o->quantity
        ));
    }

}

