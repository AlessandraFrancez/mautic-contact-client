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
use Mautic\CoreBundle\Helper\Chart\ChartQuery;
use Mautic\CoreBundle\Helper\Chart\LineChart;
use Mautic\CoreBundle\Helper\TemplatingHelper;
use Mautic\CoreBundle\Model\FormModel;
use Mautic\LeadBundle\Model\LeadModel as ContactModel;
use Mautic\LeadBundle\Entity\Lead as Contact;
use Mautic\PageBundle\Model\TrackableModel;
use MauticPlugin\MauticContactClientBundle\Entity\ContactClient;
use MauticPlugin\MauticContactClientBundle\Entity\Stat;
use MauticPlugin\MauticContactClientBundle\Entity\Event as EventEntity;
use MauticPlugin\MauticContactClientBundle\Event\ContactClientEvent;
use MauticPlugin\MauticContactClientBundle\Event\ContactClientTimelineEvent;
use MauticPlugin\MauticContactClientBundle\ContactClientEvents;
use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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
    protected $contactModel;

    /**
     * ContactClientModel constructor.
     *
     * @param \Mautic\FormBundle\Model\FormModel $formModel
     * @param TrackableModel $trackableModel
     * @param TemplatingHelper $templating
     * @param EventDispatcherInterface $dispatcher
     * @param ContactModel $contactModel
     */
    public function __construct(
        \Mautic\FormBundle\Model\FormModel $formModel,
        TrackableModel $trackableModel,
        TemplatingHelper $templating,
        EventDispatcherInterface $dispatcher,
        ContactModel $contactModel
    ) {
        $this->formModel = $formModel;
        $this->trackableModel = $trackableModel;
        $this->templating = $templating;
        $this->dispatcher = $dispatcher;
        $this->contactModel = $contactModel;
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
     * @param $type
     * @param null|Contact $contact
     * @param int $attribution
     */
    public function addStat(ContactClient $contactClient, $type, $contact = null, $attribution = 0)
    {
        $stat = new Stat();
        $stat->setContactClient($contactClient)
            ->setDateAdded(new \DateTime())
            ->setType($type)
            ->setAttribution($attribution);
        if ($contact) {
            $stat->setContact($contact);
        }
        if ($attribution) {
            $stat->setAttribution($attribution);
        }

        $this->getStatRepository()->saveEntity($stat);
    }

    /**
     * Add transactional log in contactclient_events
     *
     * @param ContactClient $contactClient
     * @param $type
     * @param null $contact
     * @param null $logs
     * @param null $message
     * @param null $integration_entity_id
     */
    public function addEvent(ContactClient $contactClient, $type, $contact = null, $logs = null, $message = null, $integration_entity_id = null)
    {
        $event = new EventEntity();
        $event->setContactClient($contactClient)
            ->setDateAdded(new \DateTime())
            ->setType($type);
        if ($contact) {
            $event->setContact($contact);
        }
        if ($logs) {
            $event->setLogs($logs);
        }
        if ($message) {
            $event->setMessage($message);
        }
        if ($integration_entity_id) {
            $event->setIntegrationEntityId($integration_entity_id);
        }

        $this->getEventRepository()->saveEntity($event);
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
     * {@inheritdoc}
     *
     * @return \MauticPlugin\MauticContactClientBundle\Entity\StatRepository
     */
    public function getEventRepository()
    {
        return $this->em->getRepository('MauticContactClientBundle:Event');
    }

    /**
     * @param ContactClient $contactClient
     * @param                $unit
     * @param \DateTime|null $dateFrom
     * @param \DateTime|null $dateTo
     *
     * @param null $dateFormat
     * @param bool $canViewOthers
     *
     * @return array
     */
    public function getStats(
        ContactClient $contactClient,
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
            ['contactclient_id' => $contactClient->getId()]
        );
        if (!$canViewOthers) {
            $this->limitQueryToCreator($q);
        }
        $stat = new Stat();
        foreach ($stat->getAllTypes() as $type) {

            $q = $query->prepareTimeDataQuery('contactclient_stats', 'date_added', ['type' => $type]);
            if (!$canViewOthers) {
                $this->limitQueryToCreator($q);
            }
            $data = $query->loadAndBuildTimeData($q);
            foreach ($data as $val) {
                if ($val != 0) {
                    $chart->setDataset($this->translator->trans('mautic.contactclient.graph.' . $type), $data);
                    break;
                }
            }
        }

        // Add revenue to the chart.
        // @todo - This should really be in it's own chart in the future.
        $q = $query->prepareTimeDataQuery('contactclient_stats', 'date_added', ['type' => Stat::TYPE_SUCCESS]);
        if (!$canViewOthers) {
            $this->limitQueryToCreator($q);
        }
        $q->select('SUM(t.attribution) AS count');
        $data = $query->loadAndBuildTimeData($q);
        foreach ($data as $val) {
            if ($val != 0) {
                $chart->setDataset($this->translator->trans('mautic.contactclient.graph.revenue'), $data);
                break;
            }
        }

        return $chart->render();
    }

    /**
     * Get timeline/engagement data.
     *
     * @param ContactClient|null $contactClient
     * @param array $filters
     * @param null $orderBy
     * @param int $page
     * @param int $limit
     * @param bool $forTimeline
     * @return array
     */
    public function getEngagements(ContactClient $contactClient = null, $filters = [], $orderBy = null, $page = 1, $limit = 25, $forTimeline = true)
    {
        $event = $this->dispatcher->dispatch(
            ContactClientEvents::TIMELINE_ON_GENERATE,
            new ContactClientTimelineEvent($contactClient, $filters, $orderBy, $page, $limit, $forTimeline, $this->coreParametersHelper->getParameter('site_url'))
        );

        if (!isset($filters['search'])) {
            $filters['search'] = null;
        }
        $payload = [
            'events'   => $event->getEvents(),
            'filters'  => $filters,
            'order'    => $orderBy,
            'types'    => $event->getEventTypes(),
            'total'    => $event->getEventCounter()['total'],
            'page'     => $page,
            'limit'    => $limit,
            'maxPages' => $event->getMaxPage(),
        ];

        return ($forTimeline) ? $payload : [$payload, $event->getSerializerGroups()];
    }

    /**
     * @return array
     */
    public function getEngagementTypes()
    {
        $event = new ContactClientTimelineEvent();
        $event->fetchTypesOnly();

        $this->dispatcher->dispatch(ContactClientEvents::TIMELINE_ON_GENERATE, $event);

        return $event->getEventTypes();
    }

    /**
     * Get engagement counts by time unit.
     *
     * @param Contact         $contact
     * @param \DateTime|null  $dateFrom
     * @param \DateTime|null  $dateTo
     * @param string          $unit
     * @param ChartQuery|null $chartQuery
     *
     * @return array
     */
    public function getEngagementCount(Contact $contact, \DateTime $dateFrom = null, \DateTime $dateTo = null, $unit = 'm', ChartQuery $chartQuery = null)
    {
        $event = new ContactClientTimelineEvent($contact);
        $event->setCountOnly($dateFrom, $dateTo, $unit, $chartQuery);

        $this->dispatcher->dispatch(ContactClientEvents::TIMELINE_ON_GENERATE, $event);

        return $event->getEventCounter();
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
     * @return bool|ContactClientEvent
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
