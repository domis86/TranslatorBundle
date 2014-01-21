<?php

namespace Domis86\TranslatorBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Domis86\TranslatorBundle\Translation\LocationVO;

/**
 * MessageRepository
 *
 */
class MessageRepository extends EntityRepository
{
    /**
     * @param string $messageName
     * @param string $domainName
     * @return Message|bool
     */
    public function findOneByNameAndDomain($messageName, $domainName)
    {
        $qb = $this->createQueryBuilder('m')
            ->addSelect('d')
            ->innerJoin('m.domain', 'd', 'WITH', 'd.name = :domain_name')
            ->where("m.name = :message_name")
            ->setParameter('message_name', $messageName)
            ->setParameter('domain_name', $domainName);
        $query = $qb->getQuery();
        $result = $query->getResult();
        if (empty($result)) {
            return false;
        }
        return current($result);
    }

    /**
     * @param LocationVO $location
     * @return array Array of Messages
     */
    public function loadByLocation(LocationVO $location)
    {
        $qb = $this->createQueryBuilder('m')
            ->addSelect(array('d', 'mt'))
            ->innerJoin(
                'm.messageLocations',
                'ml',
                'WITH',
                'ml.bundle = :bundle AND ml.controller = :controller AND ml.action = :action'
            )
            ->innerJoin('m.domain', 'd')
            ->leftJoin('m.translations', 'mt')
            ->setParameter('bundle', $location->getBundleName())
            ->setParameter('controller', $location->getControllerName())
            ->setParameter('action', $location->getActionName());
        $query = $qb->getQuery();
        $result = $query->getResult();
        if (empty($result)) {
            return array();
        }
        return $result;
    }
}
