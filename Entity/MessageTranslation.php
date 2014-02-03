<?php

namespace Domis86\TranslatorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MessageTranslation
 *
 * @ORM\Table(name="domis86_translator_message_translation")
 * @ORM\Entity(repositoryClass="Domis86\TranslatorBundle\Entity\MessageTranslationRepository")
 * @ORM\HasLifecycleCallbacks
 */
class MessageTranslation
{
    /**
     * @var integer $message_id
     *
     * @ORM\Column(name="message_id", type="integer")
     * @ORM\Id
     */
    private $message_id;

    /**
     * @var string $locale
     *
     * @ORM\Column(name="locale", type="string", length=6)
     * @ORM\Id
     */
    private $locale;

    /**
     * @var string $translation
     *
     * @ORM\Column(name="translation", type="text")
     */
    private $translation;

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
     * @ORM\ManyToOne(targetEntity="Message", inversedBy="translations", cascade={"persist"})
     * @ORM\JoinColumn(name="message_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $message;


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
     * Set locale
     *
     * @param string $locale
     * @return MessageTranslation
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * Get locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set message_id
     *
     * @param integer $messageId
     * @return MessageTranslation
     */
    public function setMessageId($messageId)
    {
        $this->message_id = $messageId;
        return $this;
    }

    /**
     * Get message_id
     *
     * @return integer
     */
    public function getMessageId()
    {
        return $this->message_id;
    }

    /**
     * Set translation
     *
     * @param string $translation
     * @return MessageTranslation
     */
    public function setTranslation($translation)
    {
        $this->translation = $translation;
        return $this;
    }

    /**
     * Get translation
     *
     * @return string
     */
    public function getTranslation()
    {
        return $this->translation;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return MessageTranslation
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
     * @return MessageTranslation
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
     * Set Message
     *
     * @param \Domis86\TranslatorBundle\Entity\Message $message
     * @return MessageTranslation
     */
    public function setMessage(Message $message = null)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Get Message
     *
     * @return \Domis86\TranslatorBundle\Entity\Message
     */
    public function getMessage()
    {
        return $this->message;
    }

    // ----------------------------------------------------------------------------------------------------------------

    /**
     * __toString
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getTranslation();
    }
}
