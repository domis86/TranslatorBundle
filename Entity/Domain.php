<?php

namespace Domis86\TranslatorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Domis86\TranslatorBundle\Entity\Message as TMessage;

/**
 * Domain
 *
 * @ORM\Table(name="domis86_translator_domain")
 * @ORM\Entity(repositoryClass="Domis86\TranslatorBundle\Entity\DomainRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Domain
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
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
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
     * @var ArrayCollection messages
     *
     * @ORM\OneToMany(targetEntity="Message", mappedBy="domain")
     */
    protected $messages;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
    }

    /** @ORM\PrePersist */
    public function doStuffOnPrePersist()
    {
        if (!$this->getCreatedAt()) $this->setCreatedAt(date_create(date('Y-m-d H:i:s')));
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
     * @return Domain
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
     * @return Domain
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
     * @return Domain
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
     * Add messages
     *
     * @param TMessage $message
     * @return Domain
     */
    public function addMessage(TMessage $message)
    {
        $this->messages[] = $message;
        return $this;
    }

    /**
     * Remove messages
     *
     * @param TMessage $message
     */
    public function removeMessage(TMessage $message)
    {
        $this->messages->removeElement($message);
    }

    /**
     * Get messages
     *
     * @return ArrayCollection
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * __toString
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

}
