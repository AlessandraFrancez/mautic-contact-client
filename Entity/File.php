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

use Doctrine\ORM\Mapping as ORM;
use Mautic\ApiBundle\Serializer\Driver\ApiMetadataDriver;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;
use Mautic\CoreBundle\Entity\FormEntity;

/**
 * Class File.
 */
class File extends FormEntity
{
    /**
     * Indicates that we are processing contacts to create a file at this time.
     *
     * Contacts sent:   No
     */
    const STATUS_BUILDING = 'building';

    /**
     * Indicates that all attempts to upload/send this file failed.
     *
     * Contacts sent:   No (unable to confirm)
     */
    const STATUS_ERROR = 'error';

    /**
     * Indicates that we are building a list of contacts to send at the next appropriate time.
     * This is the default status.
     *
     * Contacts sent:   No
     */
    const STATUS_QUEUEING = 'queueing';

    /**
     * Indicates that the file has been built and is ready to be sent, either manually or automatically.
     *
     * Contacts sent:   No
     */
    const STATUS_READY = 'ready';

    /**
     * Indicates that the file has been successfully sent to the client.
     *
     * Contacts sent:   Yes
     */
    const STATUS_SENT = 'sent';

    /** @var string */
    private $csvDelimiter;

    /** @var string */
    private $csvEnclosure;

    /** @var string */
    private $csvEscape;

    /** @var string */
    private $csvTerminate;

    /** @var string */
    private $csvNull;

    /** @var bool */
    private $csvBom;

    /** @var int $id */
    private $id;

    /** @var string */
    private $name;

    /** @var string */
    private $type;

    /** @var bool */
    private $headers;

    /** @var ContactClient $contactClient */
    private $contactClient;

    /** @var string */
    private $compression;

    /** @var string */
    private $sha1;

    /** @var string */
    private $md5;

    /** @var string */
    private $crc32;

    /** @var string */
    private $tmp;

    /** @var string */
    private $location;

    /** @var int */
    private $count;

    /** @var string */
    private $logs;

    /** @var string */
    private $status;

    /** @var bool */
    private $test;

    /**
     * File constructor.
     */
    public function __construct()
    {
        // Default status for a new file is "queueing".
        $this->status  = self::STATUS_QUEUEING;
        $this->headers = true;
        $this->test    = false;
    }

    /**
     * Prepares the metadata for API usage.
     *
     * @param $metadata
     */
    public static function loadApiMetadata(ApiMetadataDriver $metadata)
    {
        $metadata
            ->addListProperties(
                [
                    'id',
                    'name',
                    'contactclient_id',
                ]
            )
            ->addProperties(
                [
                    'type',
                    'headers',
                    'test',
                    'compression',
                    'dateAdded',
                    'publishUp',
                    'publishDown',
                    'sha1',
                    'md5',
                    'crc32',
                    'tmp',
                    'location',
                    'count',
                    'status',
                    'logs',
                ]
            )
            ->build();
    }

    /**
     * @param ORM\ClassMetadata $metadata
     */
    public static function loadMetadata(ORM\ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);

        $builder->setTable('contactclient_file')
            ->setCustomRepositoryClass('MauticPlugin\MauticContactClientBundle\Entity\FileRepository');

        $builder->addId();

        $builder->createManyToOne('contactClient', 'ContactClient')
            ->addJoinColumn('contactclient_id', 'id', true, false, null)
            ->build();

        $builder->addNullableField('name');

        $builder->createField('type', 'string')
            ->columnName('type')
            ->length(10)
            ->nullable()
            ->build();

        $builder->createField('csvDelimiter', 'string')
            ->columnName('csv_delimiter')
            ->length(4)
            ->nullable()
            ->build();

        $builder->createField('csvEnclosure', 'string')
            ->columnName('csv_enclosure')
            ->length(4)
            ->nullable()
            ->build();

        $builder->createField('csvEscape', 'string')
            ->columnName('csv_escape')
            ->length(4)
            ->nullable()
            ->build();

        $builder->createField('csvTerminate', 'string')
            ->columnName('csv_terminate')
            ->length(4)
            ->nullable()
            ->build();

        $builder->createField('csvNull', 'string')
            ->columnName('csv_null')
            ->length(4)
            ->nullable()
            ->build();

        $builder->createField('csvBom', 'boolean')
            ->columnName('csv_bom')
            ->nullable()
            ->build();

        $builder->createField('headers', 'boolean')
            ->columnName('headers')
            ->build();

        $builder->createField('test', 'boolean')
            ->columnName('test')
            ->build();

        $builder->createField('compression', 'string')
            ->columnName('compression')
            ->length(4)
            ->nullable()
            ->build();

        $builder->createField('sha1', 'string')
            ->columnName('sha1')
            ->length(40)
            ->nullable()
            ->build();

        $builder->createField('md5', 'string')
            ->columnName('md5')
            ->length(32)
            ->nullable()
            ->build();

        $builder->createField('crc32', 'string')
            ->columnName('crc32')
            ->length(8)
            ->nullable()
            ->build();

        $builder->addNullableField('tmp');

        $builder->addNullableField('location');

        $builder->createField('count', 'integer')
            ->columnName('count')
            ->nullable()
            ->build();

        $builder->createField('status', 'string')
            ->columnName('status')
            ->length(10)
            ->nullable()
            ->build();

        $builder->addNamedField('logs', 'text', 'logs', true);

        // $builder->addIndex(
        //     ['id', 'file_id', 'contact_id'],
        //     'contactclient_queue_file_id'
        // );
    }

    /**
     * @return mixed
     */
    public function getContactClient()
    {
        return $this->contactClient;
    }

    /**
     * @param mixed $contactClient
     *
     * @return $this
     */
    public function setContactClient($contactClient)
    {
        $this->isChanged('contactClient', $contactClient);
        $this->contactClient = $contactClient;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param $status
     *
     * @return $this
     */
    public function setStatus($status)
    {
        $this->isChanged('status', $status);
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
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
        $this->isChanged('logs', $logs);
        $this->logs = $logs;

        return $this;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @param $count
     *
     * @return $this
     */
    public function setCount($count)
    {
        $this->isChanged('count', $count);
        $this->count = $count;

        return $this;
    }

    /**
     * @return string
     */
    public function getTmp()
    {
        return $this->tmp;
    }

    /**
     * @param $tmp
     *
     * @return $this
     */
    public function setTmp($tmp)
    {
        $this->isChanged('tmp', $tmp);
        $this->tmp = $tmp;

        return $this;
    }

    /**
     * @return string
     */
    public function getCrc32()
    {
        return $this->crc32;
    }

    /**
     * @param $crc32
     *
     * @return $this
     */
    public function setCrc32($crc32)
    {
        $this->isChanged('crc32', $crc32);
        $this->crc32 = $crc32;

        return $this;
    }

    /**
     * @return string
     */
    public function getMd5()
    {
        return $this->md5;
    }

    /**
     * @param $md5
     *
     * @return $this
     */
    public function setMd5($md5)
    {
        $this->isChanged('md5', $md5);
        $this->md5 = $md5;

        return $this;
    }

    /**
     * @return string
     */
    public function getSha1()
    {
        return $this->sha1;
    }

    /**
     * @param $sha1
     *
     * @return $this
     */
    public function setSha1($sha1)
    {
        $this->isChanged('sha1', $sha1);
        $this->sha1 = $sha1;

        return $this;
    }

    /**
     * @return string
     */
    public function getCompression()
    {
        return $this->compression;
    }

    /**
     * @param $compression
     *
     * @return $this
     */
    public function setCompression($compression)
    {
        $this->isChanged('compression', $compression);
        $this->compression = $compression;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param $location
     */
    public function setLocation($location)
    {
        $this->isChanged('location', $location);
        $this->location = $location;
    }

    /**
     * @return bool
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param $headers
     *
     * @return $this
     */
    public function setHeaders($headers)
    {
        $this->isChanged('headers', $headers);
        $this->headers = $headers;

        return $this;
    }

    /**
     * @return bool
     */
    public function getTest()
    {
        return $this->test;
    }

    /**
     * @param $test
     *
     * @return $this
     */
    public function setTest($test)
    {
        $this->isChanged('test', $test);
        $this->test = $test;

        return $this;
    }

    /**
     * @return bool
     */
    public function getCsvBom()
    {
        return $this->csvBom;
    }

    /**
     * @param $csvBom
     *
     * @return $this
     */
    public function setCsvBom($csvBom)
    {
        $this->isChanged('csvBom', $csvBom);
        $this->csvBom = $csvBom;

        return $this;
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
        $this->isChanged('type', $type);
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->isChanged('name', $name);
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCsvDelimiter()
    {
        return $this->csvDelimiter;
    }

    /**
     * @param $csvDelimiter
     *
     * @return $this
     */
    public function setCsvDelimiter($csvDelimiter)
    {
        $this->isChanged('csvDelimiter', $csvDelimiter);
        $this->csvDelimiter = $csvDelimiter;

        return $this;
    }

    /**
     * @return string
     */
    public function getCsvEnclosure()
    {
        return $this->csvEnclosure;
    }

    /**
     * @param $csvEnclosure
     *
     * @return $this
     */
    public function setCsvEnclosure($csvEnclosure)
    {
        $this->isChanged('csvEnclosure', $csvEnclosure);
        $this->csvEnclosure = $csvEnclosure;

        return $this;
    }

    /**
     * @return string
     */
    public function getCsvEscape()
    {
        return $this->csvEscape;
    }

    /**
     * @param $csvEscape
     *
     * @return $this
     */
    public function setCsvEscape($csvEscape)
    {
        $this->isChanged('csvEscape', $csvEscape);
        $this->csvEscape = $csvEscape;

        return $this;
    }

    /**
     * @return string
     */
    public function getCsvTerminate()
    {
        return $this->csvTerminate;
    }

    /**
     * @param $csvTerminate
     *
     * @return $this
     */
    public function setCsvTerminate($csvTerminate)
    {
        $this->isChanged('csvTerminate', $csvTerminate);
        $this->csvTerminate = $csvTerminate;

        return $this;
    }

    /**
     * @return string
     */
    public function getCsvNull()
    {
        return $this->csvNull;
    }

    /**
     * @param $csvNull
     *
     * @return $this
     */
    public function setCsvNull($csvNull)
    {
        $this->isChanged('csvBom', $csvNull);
        $this->csvNull = $csvNull;

        return $this;
    }
}
