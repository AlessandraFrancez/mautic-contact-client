<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic, Inc
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticContactClientBundle\Model;

use Doctrine\DBAL\Query\QueryBuilder;
use Mautic\CoreBundle\Event\TokenReplacementEvent;
use Mautic\CoreBundle\Helper\Chart\ChartQuery;
use Mautic\CoreBundle\Helper\Chart\LineChart;
use Mautic\CoreBundle\Helper\TemplatingHelper;
use Mautic\CoreBundle\Model\FormModel;
use Mautic\LeadBundle\Model\LeadModel;
use Mautic\PageBundle\Model\TrackableModel;
use MauticPlugin\MauticContactClientBundle\Entity\ContactClient;
use MauticPlugin\MauticContactClientBundle\Entity\Stat;
use MauticPlugin\MauticContactClientBundle\Event\ContactClientEvent;
use MauticPlugin\MauticContactClientBundle\ContactClientEvents;
use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

/**
 * Class ContactClientModel
 * @package MauticPlugin\MauticContactClientBundle\Model
 */
class ContactClientModel extends FormModel
{
    /**
     * @var ContainerAwareEventDispatcher
     */
    protected $dispatcher;

    /**
     * @var \Mautic\FormBundle\Model\FormModel
     */
    protected $formModel;

    /**
     * @var TrackableModel
     */
    protected $trackableModel;

    /**
     * @var TemplatingHelper
     */
    protected $templating;

    /**
     * @var
     */
    protected $leadModel;

    /**
     * ContactClientModel constructor.
     *
     * @param \Mautic\FormBundle\Model\FormModel $formModel
     * @param TrackableModel $trackableModel
     * @param TemplatingHelper $templating
     * @param EventDispatcherInterface $dispatcher
     * @param LeadModel $leadModel
     */
    public function __construct(
        \Mautic\FormBundle\Model\FormModel $formModel,
        TrackableModel $trackableModel,
        TemplatingHelper $templating,
        EventDispatcherInterface $dispatcher,
        LeadModel $leadModel
    ) {
        $this->formModel = $formModel;
        $this->trackableModel = $trackableModel;
        $this->templating = $templating;
        $this->dispatcher = $dispatcher;
        $this->leadModel = $leadModel;
    }

    /**
     * @return string
     */
    public function getActionRouteBase()
    {
        return 'contactclient';
    }

    /**
     * @return string
     */
    public function getPermissionBase()
    {
        return 'plugin:contactclient:items';
    }

    /**
     * {@inheritdoc}
     *
     * @param object $entity
     * @param \Symfony\Component\Form\FormFactory $formFactory
     * @param null $action
     * @param array $options
     *
     * @throws NotFoundHttpException
     */
    public function createForm($entity, $formFactory, $action = null, $options = [])
    {
        if (!$entity instanceof ContactClient) {
            throw new MethodNotAllowedHttpException(['ContactClient']);
        }

        if (!empty($action)) {
            $options['action'] = $action;
        }

        return $formFactory->create('contactclient', $entity, $options);
    }

    /**
     * {@inheritdoc}
     *
     * @param null $id
     *
     * @return ContactClient
     */
    public function getEntity($id = null)
    {
        if ($id === null) {
            return new ContactClient();
        }

        return parent::getEntity($id);
    }

    /**
     * {@inheritdoc}
     *
     * @param ContactClient $entity
     * @param bool|false $unlock
     */
    public function saveEntity($entity, $unlock = true)
    {
        parent::saveEntity($entity, $unlock);

        $this->getRepository()->saveEntity($entity);
    }

    /**
     * {@inheritdoc}
     *
     * @return \MauticPlugin\MauticContactClientBundle\Entity\ContactClientRepository
     */
    public function getRepository()
    {
        return $this->em->getRepository('MauticContactClientBundle:ContactClient');
    }

    /**
     * Add a stat entry.
     *
     * @param ContactClient $contactClient
     * @param       $type
     * @param null $data
     * @param null $contact
     */
    public function addStat(ContactClient $contactClient, $type, $data = null, $contact = null)
    {
        $typeId = null;
        if ($data) {
            $typeId = $data->getId();
        }

        $stat = new Stat();
        $stat->setContactClient($contactClient)
            ->setDateAdded(new \DateTime())
            ->setType($type)
            ->setTypeId($typeId)
            ->setContact($contact);

        $this->getStatRepository()->saveEntity($stat);
    }

    /**
     * {@inheritdoc}
     *
     * @return \MauticPlugin\MauticContactClientBundle\Entity\StatRepository
     */
    public function getStatRepository()
    {
        return $this->em->getRepository('MauticContactClientBundle:Stat');
    }

    /**
     * @param ContactClient $contactclient
     * @param                $unit
     * @param \DateTime|null $dateFrom
     * @param \DateTime|null $dateTo
     * @param null $dateFormat
     * @param bool $canViewOthers
     *
     * @return array
     */
    public function getStats(
        ContactClient $contactclient,
        $unit,
        \DateTime $dateFrom = null,
        \DateTime $dateTo = null,
        $dateFormat = null,
        $canViewOthers = true
    ) {
        $chart = new LineChart($unit, $dateFrom, $dateTo, $dateFormat);
        $query = new ChartQuery($this->em->getConnection(), $dateFrom, $dateTo, $unit);

        $q = $query->prepareTimeDataQuery(
            'contactclient_stats',
            'date_added',
            ['contactclient_id' => $contactclient->getId()]
        );
        if (!$canViewOthers) {
            $this->limitQueryToCreator($q);
        }
        $data = $query->loadAndBuildTimeData($q);
        $chart->setDataset($this->translator->trans('mautic.contactclient.graph.queued'), $data);

        foreach ([
                     // Stat::TYPE_QUEUED,
                     Stat::TYPE_DUPLICATE,
                     Stat::TYPE_EXCLUSIVE,
                     Stat::TYPE_FILTER,
                     Stat::TYPE_LIMITS,
                     Stat::TYPE_REVENUE,
                     Stat::TYPE_SCHEDULE,
                     Stat::TYPE_SUCCESS,
                     Stat::TYPE_REJECT,
                     Stat::TYPE_ERROR,
                 ] as $type) {

            $q = $query->prepareTimeDataQuery('contactclient_stats', 'date_added', ['type' => $type]);
            if (!$canViewOthers) {
                $this->limitQueryToCreator($q);
            }
            $data = $query->loadAndBuildTimeData($q);
            $chart->setDataset($this->translator->trans('mautic.contactclient.graph.' . $type), $data);
        }

        return $chart->render();
    }

    /**
     * Joins the email table and limits created_by to currently logged in user.
     *
     * @param QueryBuilder $q
     */
    public function limitQueryToCreator(QueryBuilder $q)
    {
        $q->join('t', MAUTIC_TABLE_PREFIX.'contactclient', 'm', 'e.id = t.contactclient_id')
            ->andWhere('m.created_by = :userId')
            ->setParameter('userId', $this->userHelper->getUser()->getId());
    }

    /**
     * {@inheritdoc}
     *
     * @return bool|ContactClientEvent|void
     *
     * @throws \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException
     */
    protected function dispatchEvent($action, &$entity, $isNew = false, Event $event = null)
    {
        if (!$entity instanceof ContactClient) {
            throw new MethodNotAllowedHttpException(['ContactClient']);
        }

        switch ($action) {
            case 'pre_save':
                $name = ContactClientEvents::PRE_SAVE;
                break;
            case 'post_save':
                $name = ContactClientEvents::POST_SAVE;
                break;
            case 'pre_delete':
                $name = ContactClientEvents::PRE_DELETE;
                break;
            case 'post_delete':
                $name = ContactClientEvents::POST_DELETE;
                break;
            default:
                return null;
        }

        if ($this->dispatcher->hasListeners($name)) {
            if (empty($event)) {
                $event = new ContactClientEvent($entity, $isNew);
                $event->setEntityManager($this->em);
            }

            $this->dispatcher->dispatch($name, $event);

            return $event;
        } else {
            return null;
        }
    }
}
