<?php

namespace Domis86\TranslatorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Message
 *
 * @ORM\Table(name="domis86_translator_message",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="unique_domain_id_name", columns={
 *         "domain_id", "name"
 *     })}
 * )
 * @ORM\Entity(repositoryClass="Domis86\TranslatorBundle\Entity\MessageRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Message
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $domain_id
     *
     * @ORM\Column(name="domain_id", type="integer")
     */
    private $domain_id;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var \DateTime $created_at
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $created_at;

    /**
     * @var \DateTime $updated_at
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updated_at;

    /**
     * @ORM\ManyToOne(targetEntity="Domain", inversedBy="messages")
     * @ORM\JoinColumn(name="domain_id", referencedColumnName="id")
     */
    protected $domain;

    /**
     * @ORM\OneToMany(targetEntity="MessageTranslation", mappedBy="message")
     */
    protected $translations;

    /**
     * @ORM\OneToMany(targetEntity="MessageLocation", mappedBy="message")
     */
    protected $messageLocations;


    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->messageLocations = new ArrayCollection();
    }

    /**
     * @ORM\PrePersist
     */
    public function doStuffOnPrePersist()
    {
        if (!$this->getCreatedAt()) $this->setCreatedAt(date_create(date('Y-m-d H:i:s')));
        $this->doStuffOnPreUpdate();
    }

    /**
     * @ORM\PreUpdate
     */
    public function doStuffOnPreUpdate()
    {
        $this->setUpdatedAt(date_create(date('Y-m-d H:i:s')));
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return \Domis86\TranslatorBundle\Entity\Message
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return \Domis86\TranslatorBundle\Entity\Message
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;
        return $this;
    }

    /**
     * Get created_at
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set updated_at
     *
     * @param \DateTime $updatedAt
     * @return \Domis86\TranslatorBundle\Entity\Message
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updated_at = $updatedAt;
        return $this;
    }

    /**
     * Get updated_at
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * Add translation
     *
     * @param MessageTranslation $translation
     * @return \Domis86\TranslatorBundle\Entity\Message
     */
    public function addTranslation(MessageTranslation $translation)
    {
        $this->translations[] = $translation;
        return $this;
    }

    /**
     * Remove translation
     *
     * @param MessageTranslation $translation
     */
    public function removeTranslation(MessageTranslation $translation)
    {
        $this->translations->removeElement($translation);
    }

    /**
     * Get translations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * Get translation for given locale
     *
     * @param string $locale
     * @return MessageTranslation|bool
     */
    public function getTranslationForLocale($locale)
    {
        foreach ($this->translations as $t) {
            /** @var MessageTranslation $t */
            if ($t->getLocale() == $locale) return $t;
        }
        // no translation for given locale
        return false;
    }


    /**
     * Set domain_id
     *
     * @param integer $domainId
     * @return \Domis86\TranslatorBundle\Entity\Message
     */
    public function setDomainId($domainId)
    {
        $this->domain_id = $domainId;
        return $this;
    }

    /**
     * Get domain_id
     *
     * @return integer
     */
    public function getDomainId()
    {
        return $this->domain_id;
    }

    /**
     * Set domain
     *
     * @param Domain $domain
     * @return \Domis86\TranslatorBundle\Entity\Message
     */
    public function setDomain(Domain $domain = null)
    {
        $this->domain = $domain;
        return $this;
    }

    /**
     * Get domain
     *
     * @return Domain
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Export data as simple array
     *
     * @return array
     */
    public function exportAsArray()
    {
        $domain = $this->getDomain();
        $translations = array();
        foreach ($this->getTranslations() as $messageTranslation) {
            /** @var MessageTranslation $messageTranslation */
            $translations[$messageTranslation->getLocale()] = $messageTranslation->getTranslation();
        }

        return array(
            'id' => $this->getId(),
            'name' => $this->getName(),
            'domain' => array(
                'name' => $domain->getName()
            ),
            'translations' => $translations
        );
    }

    /**
     * @return mixed
     */
    public function getMessageLocations()
    {
        return $this->messageLocations;
    }

    /**
     * @return array
     */
    public function getArrayOfLocationVOs()
    {
        $messageLocations = $this->getMessageLocations();
        $locations = array();
        foreach ($messageLocations as $messageLocation) {
            /** @var MessageLocation $messageLocation */
            $locations[] = $messageLocation->getLocation();
        }
        return $locations;
    }
}