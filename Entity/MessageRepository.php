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
            ->addSelect('d', 'mt')
            ->innerJoin('m.domain', 'd', 'WITH', 'd.name = :domain_name')
            ->leftJoin('m.translations', 'mt')
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
     * @param string $messageName
     * @param string $domainName
     * @return bool
     */
    public function hasMessage($messageName, $domainName)
    {
        $qb = $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->innerJoin('m.domain', 'd', 'WITH', 'd.name = :domain_name')
            ->where("m.name = :message_name")
            ->setParameter('message_name', $messageName)
            ->setParameter('domain_name', $domainName);
        $query = $qb->getQuery();
        $count = $query->getSingleScalarResult();
        return $count > 0;
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

    /**
     * @return array Array of Messages
     */
    public function loadAll()
    {
        $qb = $this->createQueryBuilder('m')
            ->addSelect(array('d', 'mt', 'ml'))
            ->innerJoin('m.domain', 'd')
            ->leftJoin('m.translations', 'mt')
            ->leftJoin('m.messageLocations', 'ml');
        $query = $qb->getQuery();
        $result = $query->getResult();
        if (empty($result)) {
            return array();
        }
        return $result;
    }

    /**
     * @param string $messageName
     * @param string $domainName
     * @param LocationVO $location
     * @return int|bool false(bool) if Message.location exists or Message.id when it needs to be added
     */
    public function checkIfMessageLocationNeedsToBeAdded($messageName, $domainName, LocationVO $location)
    {
        $qb = $this->createQueryBuilder('m')
            ->select(array('m.id', 'COUNT(ml.id) AS count_ml_id'))
            ->innerJoin('m.domain', 'd', 'WITH', 'd.name = :domain_name')
            ->leftJoin(
                'm.messageLocations',
                'ml',
                'WITH',
                'ml.bundle = :bundle AND ml.controller = :controller AND ml.action = :action'
            )
            ->where("m.name = :message_name")
            ->setParameter('message_name', $messageName)
            ->setParameter('domain_name', $domainName)
            ->setParameter('bundle', $location->getBundleName())
            ->setParameter('controller', $location->getControllerName())
            ->setParameter('action', $location->getActionName());
        $query = $qb->getQuery();
        $result = $query->getScalarResult();
        if ($result[0]['count_ml_id'] > 0) {
            return false;
        }
        return $result[0]['id'];
    }

    /**
     * @param $messageId
     * @return object
     */
    public function getMessageReference($messageId)
    {
        return $this->getEntityManager()->getReference($this->getClassName(), $messageId);
    }
}
