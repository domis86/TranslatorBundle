<?php

namespace Domis86\TranslatorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Domis86\TranslatorBundle\Translation\LocationVO;

/**
 * MessageLocation
 *
 * @ORM\Table(name="domis86_translator_message_location",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="unique_message_id_bundle_controller_action", columns={
 *         "message_id", "bundle", "controller", "action"
 *     })}
 * )
 * @ORM\Entity(repositoryClass="Domis86\TranslatorBundle\Entity\MessageLocationRepository")
 * @ORM\HasLifecycleCallbacks
 */
class MessageLocation
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
     * @var string $message_id
     *
     * @ORM\Column(name="message_id", type="integer")
     */
    private $message_id;

    /**
     * @var string $bundle
     *
     * @ORM\Column(name="bundle", type="string", length=255)
     */
    private $bundle;

    /**
     * @var string $controller
     *
     * @ORM\Column(name="controller", type="string", length=255)
     */
    private $controller;

    /**
     * @var string $action
     *
     * @ORM\Column(name="action", type="string", length=255)
     */
    private $action;

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
     * @ORM\ManyToOne(targetEntity="Message", inversedBy="messageLocations")
     * @ORM\JoinColumn(name="message_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $message;


    /**
     * @ORM\PrePersist
     */
    public function doStuffOnPrePersist()
    {
        // TODO: maybe create parent class and move this method there
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
     * Set message_id
     *
     * @param integer $messageId
     * @return MessageLocation
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
     * Set bundle
     *
     * @param string $bundle
     * @return MessageLocation
     */
    public function setBundle($bundle)
    {
        $this->bundle = $bundle;

        return $this;
    }

    /**
     * Get bundle
     *
     * @return string
     */
    public function getBundle()
    {
        return $this->bundle;
    }

    /**
     * Set controller
     *
     * @param string $controller
     * @return MessageLocation
     */
    public function setController($controller)
    {
        $this->controller = $controller;

        return $this;
    }

    /**
     * Get controller
     *
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Set action
     *
     * @param string $action
     * @return MessageLocation
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return MessageLocation
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
     * @return MessageLocation
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
     * @param Message $message
     * @return MessageLocation
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

    /**
     * Set data from LocationVO value object
     *
     * @param LocationVO $locationOfMessages
     * @return MessageLocation
     */
    public function setLocation(LocationVO $locationOfMessages)
    {
        $this->setBundle($locationOfMessages->getBundleName());
        $this->setController($locationOfMessages->getControllerName());
        $this->setAction($locationOfMessages->getActionName());

        return $this;
    }

    /**
     * Get data as LocationVO value object
     *
     * @return LocationVO
     */
    public function getLocation()
    {
        return new LocationVO($this->getBundle(), $this->getController(), $this->getAction());
    }
}
