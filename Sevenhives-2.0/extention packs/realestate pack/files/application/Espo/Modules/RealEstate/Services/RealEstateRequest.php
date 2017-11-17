<?php

namespace Espo\Modules\RealEstate\Services;

use \Espo\Core\Exceptions\Forbidden;
use \Espo\Core\Exceptions\BadRequest;
use \Espo\Core\Exceptions\NotFound;

class RealEstateRequest extends \Espo\Core\Templates\Services\Base
{

    protected function afterCreate(Entity $entity, array $data = array())
    {
        parent::afterCreate($entity, $data);

        if (!$entity->get('name')) {
            $entity->set('name', $entity->get('number'));
        }
    }

    public function findEntities($params)
    {
        if (!empty($params['where']) && is_array($params['where'])) {
            foreach ($params['where'] as $i => $item) {
                if (!is_array($item)) continue;
                if (empty($item['field']) || $item['field'] !== 'matchingPropertyId') continue;
                unset($params['where'][$i]);
                if (empty($item['type']) || empty($item['value'])) continue;
                if ($item['type'] == 'equals') {
                    return $this->getServiceFactory()->create('RealEstateProperty')->findLinkedEntitiesMatchingRequests($item['value'], $params, true);
                }

            }
        }

        return parent::findEntities($params);
    }

    public function findLinkedEntitiesMatchingProperties($id, $params, $customOrder = false)
    {
        $entity = $this->getRepository()->get($id);

        $this->loadAdditionalFields($entity);

        $pdo = $this->getEntityManager()->getPDO();

        $selectManager = $this->getSelectManager('RealEstateProperty');
        $selectParams = $selectManager->getSelectParams($params, true);

        $locationIdList = $entity->get('locationsIds');
        if (!empty($locationIdList)) {
            $selectParams['customJoin'] .= " JOIN real_estate_location_path AS `realEstateLocationPath` ON realEstateLocationPath.descendor_id = real_estate_property.location_id ";
            $selectParams['whereClause']['realEstateLocationPath.ascendorId'] = $locationIdList;
            $selectParams['distinct'] = true;
        }

        $selectParams['customWhere'] .= " AND real_estate_property.id NOT IN (SELECT property_id FROM opportunity WHERE request_id = ".$pdo->quote($entity->id)." AND deleted = 0)";

        $selectParams['customJoin'] .= "
            LEFT JOIN real_estate_property_real_estate_request AS propertiesMiddle
            ON
            propertiesMiddle.real_estate_property_id = real_estate_property.id AND
            propertiesMiddle.deleted = 0 AND
            propertiesMiddle.real_estate_request_id = ".$pdo->quote($id)."
        ";
        $selectParams['additionalSelectColumns'] = array(
            'propertiesMiddle.interest_degree' => 'interestDegree'
        );

        $primaryFilter = null;
        switch ($entity->get('type')) {
            case 'Rent':
                $primaryFilter = 'actualRent';
                break;
            case 'Sale':
                $primaryFilter = 'actualSale';
                break;
        }
        if ($primaryFilter) {
            $selectManager->applyPrimaryFilter($primaryFilter, $selectParams);
        }

        if ($entity->get('propertyType')) {
            $selectParams['whereClause']['type'] = $entity->get('propertyType');
        }

        if ($entity->get('fromSquare') !== null) {
            $selectParams['whereClause']['square>='] = $entity->get('fromSquare');
        }
        if ($entity->get('toSquare') !== null) {
            $selectParams['whereClause']['square<='] = $entity->get('toSquare');
        }

        if ($entity->get('fromYearBuilt') !== null) {
            $selectParams['whereClause']['yearBuilt>='] = $entity->get('fromYearBuilt');
        }
        if ($entity->get('toYearBuilt') !== null) {
            $selectParams['whereClause']['yearBuilt<='] = $entity->get('toYearBuilt');
        }

        if ($entity->get('fromFloor') !== null) {
            $selectParams['whereClause']['floor>='] = $entity->get('fromFloor');
        }
        if ($entity->get('toFloor') !== null) {
            $selectParams['whereClause']['floor<='] = $entity->get('toFloor');
        }

        if ($entity->get('fromFloorCount') !== null) {
            $selectParams['whereClause']['floorCount>='] = $entity->get('fromFloorCount');
        }
        if ($entity->get('toFloorCount') !== null) {
            $selectParams['whereClause']['floor<='] = $entity->get('toFloorCount');
        }

        if ($entity->get('fromBedroomCount') !== null) {
            $selectParams['whereClause']['bedroomCount>='] = $entity->get('fromBedroomCount');
        }
        if ($entity->get('toBedroomCount') !== null) {
            $selectParams['whereClause']['bedroomCount<='] = $entity->get('toBedroomCount');
        }

        if ($entity->get('fromBathroomCount') !== null) {
            $selectParams['whereClause']['bathroomCount>='] = $entity->get('fromBathroomCount');
        }
        if ($entity->get('toBathroomCount') !== null) {
            $selectParams['whereClause']['bathroomCount<='] = $entity->get('toBathroomCount');
        }

        $defaultCurrency = $this->getConfig()->get('defaultCurrency');
        if ($entity->get('fromPrice') !== null) {
            $fromPrice = $entity->get('fromPrice');
            $fromPriceCurrency = $entity->get('fromPriceCurrency');

            $rates = $this->getConfig()->get('currencyRates');
            $rate1 = $this->getConfig()->get('currencyRates.' . $fromPriceCurrency, 1.0);

            $rate1 = 1.0;
            if (!empty($rates[$fromPriceCurrency])) {
                $rate1 = $rates[$fromPriceCurrency];
            }
            $rate2 = 1.0;
            if (!empty($rates[$defaultCurrency])) {
                $rate2 = $rates[$defaultCurrency];
            }
            $fromPrice = $fromPrice * ($rate1);
            $fromPrice = $fromPrice / ($rate2);

            $selectParams['whereClause']['priceConverted>='] = $fromPrice;
        }
        if ($entity->get('toPrice') !== null) {
            $toPrice = $entity->get('toPrice');
            $toPriceCurrency = $entity->get('toPriceCurrency');

            $rates = $this->getConfig()->get('currencyRates');
            $rate1 = $this->getConfig()->get('currencyRates.' . $toPriceCurrency, 1.0);

            $rate1 = 1.0;
            if (!empty($rates[$toPriceCurrency])) {
                $rate1 = $rates[$toPriceCurrency];
            }
            $rate2 = 1.0;
            if (!empty($rates[$defaultCurrency])) {
                $rate2 = $rates[$defaultCurrency];
            }
            $toPrice = $toPrice * ($rate1);
            $toPrice = $toPrice / ($rate2);

            $selectParams['whereClause']['priceConverted<='] = $toPrice;
        }

        if (!$customOrder) {
            $selectParams['orderBy'] = [
                ['propertiesMiddle.interest_degree'],
                ['LIST:status:New,Assigned,In Process'],
                ['createdAt', 'DESC']
            ];
        }

        $collection = $this->getEntityManager()->getRepository('RealEstateProperty')->find($selectParams);
        $recordService = $this->getRecordService('RealEstateProperty');

        foreach ($collection as $e) {
            $recordService->loadAdditionalFieldsForList($e);
            $recordService->prepareEntityForOutput($e);
        }

        $total = $this->getEntityManager()->getRepository('RealEstateProperty')->count($selectParams);

        return array(
            'total' => $total,
            'collection' => $collection
        );
    }

    public function setNotIntereseted($requestId, $propertyId)
    {
        $request = $this->getEntity($requestId);
        if (!$request) {
            throw new NotFound();
        }
        if (!$this->getAcl()->check($request, 'edit')) {
            throw new Forbidden();
        }
        return $this->getEntityManager()->getRepository('RealEstateRequest')->relate($request, 'properties', $propertyId, array(
            'interestDegree' => 0
        ));
    }

    public function unsetNotIntereseted($requestId, $propertyId)
    {
        $request = $this->getEntity($requestId);
        if (!$request) {
            throw new NotFound();
        }
        if (!$this->getAcl()->check($request, 'edit')) {
            throw new Forbidden();
        }
        return $this->getEntityManager()->getRepository('RealEstateRequest')->unrelate($request, 'properties', $propertyId);
    }
}
