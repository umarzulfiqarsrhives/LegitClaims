<?php

namespace Espo\Modules\RealEstate\Services;

use \Espo\Core\Exceptions\Forbidden;
use \Espo\Core\Exceptions\BadRequest;
use \Espo\Core\Exceptions\NotFound;

class RealEstateProperty extends \Espo\Core\Templates\Services\Base
{
    public function findEntities($params)
    {
        if (!empty($params['where']) && is_array($params['where'])) {
            foreach ($params['where'] as $i => $item) {
                if (!is_array($item)) continue;
                if (empty($item['field']) || $item['field'] !== 'matchingRequestId') continue;
                unset($params['where'][$i]);
                if (empty($item['type']) || empty($item['value'])) continue;
                if ($item['type'] == 'equals') {
                    return $this->getServiceFactory()->create('RealEstateRequest')->findLinkedEntitiesMatchingProperties($item['value'], $params, true);
                }

            }
        }

        return parent::findEntities($params);
    }

    public function findLinkedEntitiesMatchingRequests($id, $params, $customOrder = false)
    {
        $entity = $this->getRepository()->get($id);

        $this->loadAdditionalFields($entity);

        $pdo = $this->getEntityManager()->getPDO();

        $selectManager = $this->getSelectManager('RealEstateRequest');
        $selectParams = $selectManager->getSelectParams($params, true);

        $locationId = $entity->get('locationId');
        if ($locationId) {
            $selectParams['joins'][] = 'locations';
            $selectParams['customJoin'] .= "
                JOIN real_estate_location_path AS `realEstateLocationPathLeft` ON realEstateLocationPathLeft.descendor_id = locationsMiddle.real_estate_location_id
                JOIN real_estate_location_path AS `realEstateLocationPathRight` ON realEstateLocationPathRight.ascendor_id = locationsMiddle.real_estate_location_id
            ";
            $selectParams['whereClause'][] = array(
                'OR' => [
                    array('realEstateLocationPathRight.descendorId' => $locationId),
                    array('realEstateLocationPathLeft.ascendorId' => $locationId)
                ]
            );
            $selectParams['distinct'] = true;
        } else {
            $selectParams['leftJoins'][] = 'locations';
            $selectParams['distinct'] = true;
            $selectParams['whereClause'][] = array(
                'locationsMiddle.id' => null
            );
        }

        $selectParams['customWhere'] .= " AND real_estate_request.id NOT IN (SELECT request_id FROM opportunity WHERE property_id = ".$pdo->quote($entity->id)." AND deleted = 0)";

        $selectParams['customJoin'] .= "
            LEFT JOIN real_estate_property_real_estate_request AS requestsMiddle
            ON
            requestsMiddle.real_estate_request_id = real_estate_request.id AND
            requestsMiddle.deleted = 0 AND
            requestsMiddle.real_estate_property_id = ".$pdo->quote($id)."
        ";
        $selectParams['additionalSelectColumns'] = array(
            'requestsMiddle.interest_degree' => 'interestDegree'
        );

        $primaryFilter = null;
        switch ($entity->get('requestType')) {
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

        if ($entity->get('type')) {
            $selectParams['whereClause']['propertyType'] = $entity->get('type');
        }

        if ($entity->get('square') !== null) {
            $selectParams['whereClause'][] = array(
                'OR' => [
                    array(
                        'fromSquare!=' => null,
                        'toSquare!=' => null,
                        'fromSquare<=' => $entity->get('square'),
                        'toSquare>=' => $entity->get('square')
                    ),
                    array(
                        'fromSquare!=' => null,
                        'toSquare=' => null,
                        'fromSquare<=' => $entity->get('square'),
                    ),
                    array(
                        'fromSquare=' => null,
                        'toSquare!=' => null,
                        'toSquare>=' => $entity->get('square'),
                    ),
                    array(
                        'fromSquare=' => null,
                        'toSquare=' => null
                    )
                ]
            );
        } else {
            $selectParams['whereClause'][] = array(
                'fromSquare=' => null,
                'toSquare=' => null
            );
        }

        if ($entity->get('yearBuilt') !== null) {
            $selectParams['whereClause'][] = array(
                'OR' => [
                    array(
                        'fromYearBuilt!=' => null,
                        'toYearBuilt!=' => null,
                        'fromYearBuilt<=' => $entity->get('yearBuilt'),
                        'toYearBuilt>=' => $entity->get('yearBuilt')
                    ),
                    array(
                        'fromYearBuilt!=' => null,
                        'toYearBuilt=' => null,
                        'fromYearBuilt<=' => $entity->get('yearBuilt'),
                    ),
                    array(
                        'fromYearBuilt=' => null,
                        'toYearBuilt!=' => null,
                        'toYearBuilt>=' => $entity->get('yearBuilt'),
                    ),
                    array(
                        'fromYearBuilt=' => null,
                        'toYearBuilt=' => null
                    )
                ]
            );
        } else {
            $selectParams['whereClause'][] = array(
                'fromYearBuilt=' => null,
                'toYearBuilt=' => null
            );
        }

        if ($entity->get('floor') !== null) {
            $selectParams['whereClause'][] = array(
                'OR' => [
                    array(
                        'fromFloor!=' => null,
                        'toFloor!=' => null,
                        'fromFloor<=' => $entity->get('floor'),
                        'toFloor>=' => $entity->get('floor')
                    ),
                    array(
                        'fromFloor!=' => null,
                        'toFloor=' => null,
                        'fromFloor<=' => $entity->get('floor'),
                    ),
                    array(
                        'fromFloor=' => null,
                        'toFloor!=' => null,
                        'toFloor>=' => $entity->get('floor'),
                    ),
                    array(
                        'fromFloor=' => null,
                        'toFloor=' => null
                    )
                ]
            );
        } else {
            $selectParams['whereClause'][] = array(
                'fromFloor=' => null,
                'toFloor=' => null
            );
        }

        if ($entity->get('floorCount') !== null) {
            $selectParams['whereClause'][] = array(
                'OR' => [
                    array(
                        'fromFloorCount!=' => null,
                        'toFloorCount!=' => null,
                        'fromFloorCount<=' => $entity->get('floorCount'),
                        'toFloorCount>=' => $entity->get('floorCount')
                    ),
                    array(
                        'fromFloorCount!=' => null,
                        'toFloorCount=' => null,
                        'fromFloorCount<=' => $entity->get('floorCount'),
                    ),
                    array(
                        'fromFloorCount=' => null,
                        'toFloorCount!=' => null,
                        'toFloorCount>=' => $entity->get('floorCount'),
                    ),
                    array(
                        'fromFloorCount=' => null,
                        'toFloorCount=' => null
                    )
                ]
            );
        } else {
            $selectParams['whereClause'][] = array(
                'fromFloorCount=' => null,
                'toFloorCount=' => null
            );
        }

        if ($entity->get('bedroomCount') !== null) {
            $selectParams['whereClause'][] = array(
                'OR' => [
                    array(
                        'fromBedroomCount!=' => null,
                        'toBedroomCount!=' => null,
                        'fromBedroomCount<=' => $entity->get('bedroomCount'),
                        'toBedroomCount>=' => $entity->get('bedroomCount')
                    ),
                    array(
                        'fromBedroomCount!=' => null,
                        'toBedroomCount=' => null,
                        'fromBedroomCount<=' => $entity->get('bedroomCount'),
                    ),
                    array(
                        'fromBedroomCount=' => null,
                        'toBedroomCount!=' => null,
                        'toBedroomCount>=' => $entity->get('bedroomCount'),
                    ),
                    array(
                        'fromBedroomCount=' => null,
                        'toBedroomCount=' => null
                    )
                ]
            );
        } else {
            $selectParams['whereClause'][] = array(
                'fromBedroomCount=' => null,
                'toBedroomCount=' => null
            );
        }

        if ($entity->get('bathroomCount') !== null) {
            $selectParams['whereClause'][] = array(
                'OR' => [
                    array(
                        'fromBathroomCount!=' => null,
                        'toBathroomCount!=' => null,
                        'fromBathroomCount<=' => $entity->get('bathroomCount'),
                        'toBathroomCount>=' => $entity->get('bathroomCount')
                    ),
                    array(
                        'fromBathroomCount!=' => null,
                        'toBathroomCount=' => null,
                        'fromBathroomCount<=' => $entity->get('bathroomCount'),
                    ),
                    array(
                        'fromBathroomCount=' => null,
                        'toBathroomCount!=' => null,
                        'toBathroomCount>=' => $entity->get('bathroomCount'),
                    ),
                    array(
                        'fromBathroomCount=' => null,
                        'toBathroomCount=' => null
                    )
                ]
            );
        } else {
            $selectParams['whereClause'][] = array(
                'fromBathroomCount=' => null,
                'toBathroomCount=' => null
            );
        }


        if ($entity->get('price') !== null) {
            $defaultCurrency = $this->getConfig()->get('defaultCurrency');

            $price = $entity->get('price');
            $priceCurrency = $entity->get('priceCurrency');
            if ($defaultCurrency !== $priceCurrency) {
                $rates = $this->getConfig()->get('currencyRates');
                $rate1 = $this->getConfig()->get('currencyRates.' . $priceCurrency, 1.0);

                $rate1 = 1.0;
                if (!empty($rates[$priceCurrency])) {
                    $rate1 = $rates[$priceCurrency];
                }
                $rate2 = 1.0;
                if (!empty($rates[$defaultCurrency])) {
                    $rate2 = $rates[$defaultCurrency];
                }
                $price = $price * ($rate1);
                $price = $price / ($rate2);
            }

            $selectParams['whereClause'][] = array(
                'OR' => [
                    array(
                        'fromPrice!=' => null,
                        'toPrice!=' => null,
                        'fromPriceConverted<=' => $price,
                        'toPriceConverted>=' => $price
                    ),
                    array(
                        'fromPrice!=' => null,
                        'toPrice=' => null,
                        'fromPriceConverted<=' => $price,
                    ),
                    array(
                        'fromPrice=' => null,
                        'toPrice!=' => null,
                        'toPriceConverted>=' => $price,
                    ),
                    array(
                        'fromPrice=' => null,
                        'toPrice=' => null
                    )
                ]
            );

        } else {
            $selectParams['whereClause'][] = array(
                'fromPrice=' => null,
                'toPrice=' => null
            );
        }

        if (!$customOrder) {
            $selectParams['orderBy'] = [
                ['requestsMiddle.interest_degree'],
                ['LIST:status:New,Assigned,In Process'],
                ['createdAt', 'DESC']
            ];
        }

        $collection = $this->getEntityManager()->getRepository('RealEstateRequest')->find($selectParams);
        $recordService = $this->getRecordService('RealEstateRequest');

        foreach ($collection as $e) {
            $recordService->loadAdditionalFieldsForList($e);
            $recordService->prepareEntityForOutput($e);
        }

        $total = $this->getEntityManager()->getRepository('RealEstateRequest')->count($selectParams);

        return array(
            'total' => $total,
            'collection' => $collection
        );
    }

    public function setNotIntereseted($propertyId, $requestId)
    {
        $property = $this->getEntity($propertyId);
        if (!$property) {
            throw new NotFound();
        }
        if (!$this->getAcl()->check($property, 'edit')) {
            throw new Forbidden();
        }
        return $this->getEntityManager()->getRepository('RealEstateProperty')->relate($property, 'requests', $requestId, array(
            'interestDegree' => 0
        ));
    }

    public function unsetNotIntereseted($propertyId, $requestId)
    {
        $property = $this->getEntity($propertyId);
        if (!$property) {
            throw new NotFound();
        }
        if (!$this->getAcl()->check($property, 'edit')) {
            throw new Forbidden();
        }
        return $this->getEntityManager()->getRepository('RealEstateProperty')->unrelate($property, 'requests', $requestId);
    }
}
