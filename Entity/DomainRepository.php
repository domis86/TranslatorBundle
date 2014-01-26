<?php

namespace Domis86\TranslatorBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * DomainRepository
 */
class DomainRepository extends EntityRepository
{
    /**
     * @return array Array of objects from findAll() as array with $name as keys
     */
    public function findAllAsArray()
    {
        // TODO: convert to value object
        $objects = parent::findAll();
        $objects_by_name = array();
        foreach ($objects as $key => $object) {
            /** @var Domain $object */
            $objects_by_name[$object->getName()] = $objects[$key];
        }
        return $objects_by_name;
    }

    /**
     * @param string $name
     * @return Domain
     */
    public function findOneByName($name)
    {
        return $this->findOneBy(array('name' => $name));
    }
}
