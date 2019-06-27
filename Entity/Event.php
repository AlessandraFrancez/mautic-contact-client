<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Digital Media Solutions, LLC
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticContactClientBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;
use Mautic\LeadBundle\Entity\Lead as Contact;

/**
 * Class Event.
 */
class Event
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Contact
     */
    protected $contact;

    /**
     * @ORM\Column(name="type", type="string", length=50)
     */
    protected $type;

    /**
     * @var int
     */
    protected $contactClientId;

    /**
     * @var array
     */
    protected $logs;

    /**
     * @var string
     */
    protected $message;

    /**
     * @var string
     */
    protected $integration_entity_id;

    /**
     * @var DateTime
     */
    private $dateAdded;

    /**
     * Event constructor.
     */
    public function __construct()
    {
        $this->dateAdded = new DateTime();
        $this->type      = 'undefined';
    }

    /**
     * @param ORM\ClassMetadata $metadata
     */
    public static function loadMetadata(ORM\ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);

        $builder->setTable('contactclient_events')
            ->setCustomRepositoryClass('MauticPlugin\MauticContactClientBundle\Entity\EventRepository')
            ->addIndex(['type', 'contact_id'], 'type_contact')
            ->addIndex(['type', 'message'], 'type_message')
            ->addIndex(['contact_id'], 'contact_id')
            ->addIndex(['contactclient_id', 'date_added'], 'contactclient_id_date_added')
            ->addIndex(['id', 'contactclient_id', 'contact_id', 'date_added'], 'transaction_view')
            ->addIndex(['date_added'], 'date_added');

        $builder->addId();

        $builder->addNamedField('contactClientId', 'integer', 'contactclient_id', true);

        $builder->createField('type', 'string')
            ->columnName('type')
            ->length(50)
            ->build();

        $builder->createField('message', 'string')
            ->columnName('message')
            ->length(255)
            ->nullable()
            ->build();

        $builder->createField('integration_entity_id', 'string')
            ->columnName('integration_entity_id')
            ->length(255)
            ->nullable()
            ->build();

        $builder->addNamedField('logs', 'text', 'logs');

        $builder->addDateAdded();

        $builder->addNamedField('contact', 'integer', 'contact_id', true);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Contact
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @param Contact|int $contact
     *
     * @return $this
     */
    public function setContact($contact)
    {
        if ($contact instanceof Contact) {
            $contact = $contact->getId();
        }
        $this->contact = $contact;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getContactClientId()
    {
        return $this->contactClientId;
    }

    /**
     * @param int $contactClientId
     *
     * @return $this
     */
    public function setContactClientId($contactClientId)
    {
        $this->contactClientId = $contactClientId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLogs()
    {
        return $this->logs;
    }

    /**
     * @param $logs
     *
     * @return $this
     */
    public function setLogs($logs)
    {
        $this->logs = $logs;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param $message
     *
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return string
     */
    public function getIntegrationEntityId()
    {
        return $this->integration_entity_id;
    }

    /**
     * @param $integration_entity_id
     *
     * @return $this
     */
    public function setIntegrationEntityId($integration_entity_id)
    {
        $this->integration_entity_id = $integration_entity_id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDateAdded()
    {
        return $this->dateAdded;
    }

    /**
     * @param $dateAdded
     *
     * @return $this
     */
    public function setDateAdded($dateAdded)
    {
        $this->dateAdded = $dateAdded;

        return $this;
    }
}
