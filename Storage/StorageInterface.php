<?php

namespace Domis86\TranslatorBundle\Storage;

use Domis86\TranslatorBundle\Entity\Message;

interface StorageInterface
{
    /**
     * @param string $messageName
     * @param string $domainName
     * @return Message|bool
     */
    public function getMessage($messageName, $domainName);

    /**
     * @param array $missingMessagesList
     */
    public function addMissingMessages($missingMessagesList = array());

    /**
     * @param array $missingDomainNames Array of names of missing Domains
     */
    public function addMissingDomains($missingDomainNames = array());

}
