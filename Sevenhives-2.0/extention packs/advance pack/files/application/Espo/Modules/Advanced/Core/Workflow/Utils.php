<?php


namespace Espo\Modules\Advanced\Core\Workflow;

class Utils
{
    /**
     * String to lower case
     *
     * @param  string $str
     * @return string | null
     */
    public static function strtolower($str)
    {
        if (!empty($str) && !is_array($str)) {
            return mb_strtolower($str, 'UTF-8');
        }

        return $str;
    }

    /**
     * Shift date days
     *
     * @param  integer $shiftDays
     * @param  string  $time
     * @return string
     */
    public static function shiftDays($shiftDays = 0, $time = null, $format = 'Y-m-d')
    {
        try {
            $date = new \DateTime($time);
        } catch (\Exception $e) {
            $date = new \DateTime('@' . $time);
        }

        if (isset($shiftDays) && $shiftDays != 0) {
            $date->modify($shiftDays. ' days');
        }

        switch ($format) {
            case 'datetime':
                $format = 'Y-m-d H:i:s';
                break;

            case 'date':
            default:
                $format = 'Y-m-d';
                break;
        }

        return $date->format($format);
    }

    /**
     * Get field value for a field/related field. If this field has a relation, get value from the relation
     *
     * @param  string $entity
     * @param  string $fieldName | null
     * @param  bool $returnEntity
     *
     * @return mixed
     */
    public static function getFieldValue(\Espo\ORM\Entity $entity, $fieldName, $returnEntity = false, $entityManager = null)
    {
        //Get field names, 0 - field name or relation name, 1 - related field name
        if (strstr($fieldName, '.')) {
            list($entityFieldName, $relatedEntityFieldName) = explode('.', $fieldName);
            $relatedEntity = $entity->get($entityFieldName);

            //if $entity is just created and doesn't have added relations
            if (isset($entityManager) && !isset($relatedEntity) && $entity->hasRelation($entityFieldName)) {
                $relations = $entity->getRelations();
                $relation = $relations[$entityFieldName];

                $normalizedEntityFieldName = static::normalizeFieldName($entity, $entityFieldName);
                if (isset($relation['entity']) && $entity->hasField($normalizedEntityFieldName) && $entity->get($normalizedEntityFieldName)) {
                    $relatedEntity = $entityManager->getEntity($relation['entity'], $entity->get($normalizedEntityFieldName));
                }
            }

            if ($relatedEntity instanceof \Espo\ORM\Entity) {
                $entity = $relatedEntity;
                $fieldName = $relatedEntityFieldName;
            } else {
               $GLOBALS['log']->error('Workflow [Utils::getFieldValue]: The related field ['.$fieldName.'] entity ['.$entity->getEntityName().'] has unsupported instance ['.(isset($relatedEntity) ? get_class($relatedEntity) : var_export($relatedEntity, true)).'].');
                return null;
            }
        }

        if ($entity->hasRelation($fieldName)) {

            if ($entity->hasField($fieldName)) {
                $relatedEntity = $entity->get($fieldName);
            }

            if ($relatedEntity instanceof \Espo\ORM\Entity) {
                $foreignKey = Utils::getRelationOption($entity, 'foreignKey', $fieldName, 'id');
                return $returnEntity ? $relatedEntity : $relatedEntity->get($foreignKey);
            }

            if (!isset($relatedEntity)) {
                $normalizedFieldName = static::normalizeFieldName($entity, $fieldName);

                if (!$entity->isNew()) {
                    $entity->loadLinkMultipleField($fieldName);
                }

                $fieldValue = $returnEntity ? static::getParentEntity($entity, $fieldName, $entityManager) : static::getParentValue($entity, $normalizedFieldName);
                if (isset($fieldValue)) {
                    return $fieldValue;
                }
            }

            $entity->loadLinkMultipleField($fieldName);
            return $returnEntity ? $entity : $entity->get($fieldName . 'Ids');
        }

        $fieldDefs = $entity->getFields();
        switch ($fieldDefs[$fieldName]['type']) {
            case 'linkParent':
                $fieldName .= 'Id';
                break;
        }

        if ($returnEntity) {
            return $entity;
        }

        if ($entity->hasField($fieldName)) {
            return $entity->get($fieldName);
        }
    }

    /**
     * Get parent field value. Works for parent and regular fields
     *
     * @param  \Espo\ORM\Entity $entity
     * @param  string | array   $normalizedFieldName
     *
     * @return mixed
     */
    public static function getParentValue(\Espo\ORM\Entity $entity, $normalizedFieldName)
    {
        if (is_array($normalizedFieldName)) {
            $value = array();
            foreach ($normalizedFieldName as $fieldName) {
                if ($entity->hasField($fieldName)) {
                    $value[$fieldName] = $entity->get($fieldName);
                }
            }
            return $value;
        }

        if ($entity->hasField($normalizedFieldName)) {
            return $entity->get($normalizedFieldName);
        }
    }

    public static function getParentEntity(\Espo\ORM\Entity $entity, $fieldName, $entityManager)
    {
        if ($entity->hasRelation($fieldName)) {
            if ($entityManager instanceof \Espo\Core\ORM\EntityManager) {
                $normalizedFieldName = static::normalizeFieldName($entity, $fieldName);
                $fieldValue = static::getParentValue($entity, $normalizedFieldName);

                if (isset($fieldValue) && is_string($fieldValue)) {
                    $fieldEnittyMeta = $entityManager->getMetadata()->get($entity->getEntityName());
                    if (isset($fieldEnittyMeta['relations'][$fieldName]['entity'])) {
                        $fieldEnitty = $fieldEnittyMeta['relations'][$fieldName]['entity'];
                        return $entityManager->getEntity($fieldEnitty, $fieldValue);
                    }
                }
            }
        } else {
            return $entity;
        }
    }

    /**
     * Normalize field name for fields and relations
     *
     * @param  \Espo\Orm\Entity $entity
     * @param  string           $fieldName
     *
     * @return string
     */
    public static function normalizeFieldName(\Espo\Orm\Entity $entity, $fieldName)
    {
        if ($entity->hasRelation($fieldName)) {

            $relations = $entity->getRelations();
            $relation = $relations[$fieldName];
            switch ($relation['type']) {
                case 'hasChildren':
                    if (isset($relation['foreignKey'])) {
                        $fieldName = $relation['foreignKey'];
                    }
                    break;

                case 'belongsTo':
                    if (isset($relation['key'])) {
                        $fieldName = $relation['key'];
                    }
                    break;

                case 'belongsToParent':
                    $fieldName = array( //order of this array is important
                        $fieldName . 'Id',
                        $fieldName . 'Type',
                    );
                    break;

                case 'hasMany':
                case 'manyMany':
                    $fieldName .= 'Ids';
                    break;
            }
        }

        $fieldDefs = $entity->getFields();
        switch ($fieldDefs[$fieldName]['type']) {
            case 'linkParent':
                $fieldName .= 'Id';
                break;
        }

        return $fieldName;
    }

    /**
     * Get option value for the relation
     *
     * @param  string $optionName
     * @param  string $relationName
     * @param  \Espo\Orm\Entity  $entity
     * @param  mixed $returns
     *
     * @return mixed
     */
    public static function getRelationOption(\Espo\Orm\Entity $entity, $optionName, $relationName, $returns = null)
    {
        if (!$entity->hasRelation($relationName)) {
            return $returns;
        }

        $relations = $entity->getRelations();

        return isset($relations[$relationName][$optionName]) ? $relations[$relationName][$optionName] : $returns;
    }

    /**
     * Get field type
     *
     * @param  \Espo\Orm\Entity $entity
     * @param  string           $fieldName
     *
     * @return string | null
     */
    public static function getFieldType(\Espo\Orm\Entity $entity, $fieldName)
    {
        if (!$entity->hasField($fieldName)) {
            $fieldName = static::normalizeFieldName($entity, $fieldName);
        }

        $fieldList = $entity->getFields();
        if (isset($fieldList[$fieldName]) && isset($fieldList[$fieldName]['type'])) {
            return $fieldList[$fieldName]['type'];
        }
    }
}