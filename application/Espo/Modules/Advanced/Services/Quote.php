<?php


namespace Espo\Modules\Advanced\Services;

use \Espo\ORM\Entity;

use \Espo\Core\Exceptions\Error;
use \Espo\Core\Exceptions\NotFound;
use \Espo\Core\Exceptions\Forbidden;

class Quote extends \Espo\Services\Record
{
    protected function init()
    {
        parent::init();

        $this->addDependency('language');
    }

    public function loadAdditionalFields(Entity $entity)
    {
        parent::loadAdditionalFields($entity);

        $itemList = $this->getEntityManager()->getRepository('QuoteItem')->where(array(
            'quoteId' => $entity->id
        ))->order('order')->find();

        $entity->set('itemList', $itemList->toArray());

        $data = $entity->toArray();
    }

    public function getAttributesFromOpportunity($opportunityId)
    {
        $opportunity = $this->getEntityManager()->getEntity('Opportunity', $opportunityId);

        if (!$opportunity) {
            throw new NotFound();
        }

        if (!$this->getAcl()->check($opportunity, 'read')) {
            throw new Forbidden();
        }

        $opportunityItemList = $this->getEntityManager()->getRepository('OpportunityItem')->where(array(
            'opportunityId' => $opportunity->id
        ))->order('order')->find();

        $itemList = [];

        foreach ($opportunityItemList as $item) {
            $data = array(
                'name' => $item->get('name'),
                'productId' => $item->get('productId'),
                'productName' => $item->get('productName'),
                'unitPrice' => $item->get('unitPrice'),
                'unitPriceCurrency' =>$item->get('unitPriceCurrency'),
                'amount' => $item->get('amount'),
                'amountCurrency' => $item->get('amountCurrency'),
                'quantity' => $item->get('quantity'),
                'taxRate' => 0,
                'listPrice' => $item->get('unitPrice'),
                'listPriceCurrency' => $item->get('amountCurrency')
            );
            $productId = $item->get('productId');
            if ($productId) {
                $product = $this->getEntityManager()->getEntity('Product', $productId);
                if ($product) {
                    $listPrice = $product->get('listPrice');
                    $listPriceCurrency = $product->get('listPriceCurrency');
                    if ($listPriceCurrency != $opportunity->get('amountCurrency')) {
                        $rates = $this->getConfig()->get('currencyRates', array());
                        $targetCurrency = $opportunity->get('amountCurrency');

                        $value = $listPrice;

                        $rate1 = 1.0;
                        if (array_key_exists($listPriceCurrency, $rates)) {
                            $rate1 = $rates[$listPriceCurrency];
                        }
                        $rate2 = 1.0;
                        if (array_key_exists($targetCurrency, $rates)) {
                            $rate2 = $rates[$targetCurrency];
                        }
                        $value = $value * ($rate1);
                        $value = $value / ($rate2);

                        $listPrice = round($value, 2);
                        $listPriceCurrency = $targetCurrency;
                    }

                    $data['listPrice'] = $listPrice;
                    $data['listPriceCurrency'] = $listPriceCurrency;
                }
            }
            $itemList[] = $data;
        }

        $opportunity->loadLinkMultipleField('teams');

        $attributes = array(
            'name' => $opportunity->get('name'),
            'teamsIds' => $opportunity->get('teamsIds'),
            'teamsNames' => $opportunity->get('teamsNames'),
            'opportunityId' => $opportunityId,
            'itemList' => $itemList,
            'amount' => $opportunity->get('amount'),
            'amountCurrency' => $opportunity->get('amountCurrency'),
            'preDiscountedAmountCurrency' => $opportunity->get('amountCurrency'),
            'taxAmountCurrency' => $opportunity->get('amountCurrency'),
            'grandTotalAmountCurrency' => $opportunity->get('amountCurrency'),
            'discountAmountCurrency' => $opportunity->get('amountCurrency'),
            'shippingCostCurrency' => $opportunity->get('amountCurrency')
        );

        $amount = $opportunity->get('amount');
        if (empty($amount)) {
            $amount = 0;
        }

        $preDiscountedAmount = 0;
        foreach ($itemList as $item) {
            $preDiscountedAmount += $item['listPrice'] * ($item['quantity']);
        }
        $preDiscountedAmount = round($preDiscountedAmount, 2);
        $attributes['preDiscountedAmount'] = $preDiscountedAmount;

        $attributes['taxAmount'] = 0;
        $attributes['shippingCost'] = 0;

        $discountAmount = $preDiscountedAmount - $amount;
        $attributes['discountAmount'] = $discountAmount;

        $grandTotalAmount = $amount + $attributes['taxAmount'] + $attributes['shippingCost'];
        $attributes['grandTotalAmount'] = $grandTotalAmount;

        $accountId = $opportunity->get('accountId');
        if ($accountId) {
            $attributes['accountId'] = $accountId;
            $attributes['accountName'] = $opportunity->get('accountName');

            $account = $this->getEntityManager()->getEntity('Account', $accountId);
            if ($account) {
                $attributes['billingAddressStreet'] = $account->get('billingAddressStreet');
                $attributes['billingAddressCity'] = $account->get('billingAddressCity');
                $attributes['billingAddressState'] = $account->get('billingAddressState');
                $attributes['billingAddressCountry'] = $account->get('billingAddressCountry');
                $attributes['billingAddressPostalCode'] = $account->get('billingAddressPostalCode');
                $attributes['shippingAddressStreet'] = $account->get('shippingAddressStreet');
                $attributes['shippingAddressCity'] = $account->get('shippingAddressCity');
                $attributes['shippingAddressState'] = $account->get('shippingAddressState');
                $attributes['shippingAddressCountry'] = $account->get('shippingAddressCountry');
                $attributes['shippingAddressPostalCode'] = $account->get('shippingAddressPostalCode');
            }
        }

        return $attributes;
    }

    public function getAttributesFromEmail($quoteId, $templateId)
    {
        $quote = $this->getEntityManager()->getEntity('Quote', $quoteId);
        $template = $this->getEntityManager()->getEntity('Template', $templateId);
        if (!$quote || !$template) {
            throw new NotFound();
        }

        if (!$this->getAcl()->checkEntity($quote, 'read') || !$this->getAcl()->checkEntity($template, 'read')) {
            throw new Forbidden();
        }

        $attributes = array();

        $attributes['name'] = $template->get('name') . ': ' . $quote->get('name');

        $attributes['nameHash'] = (object) [];

        if ($quote->get('accountId')) {
            $attributes['parentId'] = $quote->get('accountId');
            $attributes['parentType'] = 'Account';
            $attributes['parentName'] = $quote->get('accountName');

            $account = $this->getEntityManager()->getEntity('Account', $quote->get('accountId'));

            if ($account && $account->get('emailAddress')) {
                $emailAddress = $account->get('emailAddress');
                $attributes['to'] = $emailAddress;
                $attributes['nameHash']->$emailAddress = $account->get('name');
            }
        }

        if ($quote->get('billingContactId')) {
            $contact = $this->getEntityManager()->getEntity('Contact', $quote->get('billingContactId'));
            if ($contact && $contact->get('emailAddress')) {
                $emailAddress = $contact->get('emailAddress');
                $attributes['to'] = $emailAddress;
                $attributes['nameHash']->$emailAddress = $contact->get('name');
            }
        }

        $contents = $this->getServiceFactory()->create('Pdf')->buildFromTemplate($quote, $template);

        $attachment = $this->getEntityManager()->getEntity('Attachment');
        $attachment->set(array(
            'name' => \Espo\Core\Utils\Util::sanitizeFileName($template->get('name') . ' ' . $quote->get('name')) . '.pdf',
            'type' => 'application/pdf',
            'role' => 'Attachment',
            'contents' => $contents
        ));

        $this->getEntityManager()->saveEntity($attachment);

        $attributes['attachmentsIds'] = [$attachment->id];
        $attributes['attachmentsNames'] = array(
            $attachment->id => $attachment->get('name')
        );

        return $attributes;
    }
}

