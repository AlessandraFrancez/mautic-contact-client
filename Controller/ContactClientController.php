<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic, Inc
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticContactClientBundle\Controller;

use Mautic\CoreBundle\Controller\FormController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ContactClientController.
 */
class ContactClientController extends FormController
{
    use ContactClientDetailsTrait;

    public function __construct()
    {
        $this->setStandardParameters(
            'contactclient',
            'plugin:contactclient:items',
            'mautic_contactclient',
            'mautic_contactclient',
            'mautic.contactclient',
            'MauticContactClientBundle:ContactClient',
            null,
            'contactclient'
        );
    }

    /**
     * @param int $page
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function indexAction($page = 1)
    {
        return parent::indexStandard($page);
    }

    /**
     * Generates new form and processes post data.
     *
     * @return array|\Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     *
     * @throws \Exception
     */
    public function newAction()
    {
        return parent::newStandard();
    }

    /**
     * Generates edit form and processes post data.
     *
     * @param      $objectId
     * @param bool $ignorePost
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     *
     * @throws \Exception
     */
    public function editAction($objectId, $ignorePost = false)
    {
        return parent::editStandard($objectId, $ignorePost);
    }

    /**
     * Displays details on a ContactClient.
     *
     * @param $objectId
     *
     * @return array|\Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function viewAction($objectId)
    {
        return parent::viewStandard($objectId, 'contactclient', 'plugin.contactclient');
    }

    /**
     * Clone an entity.
     *
     * @param int $objectId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function cloneAction($objectId)
    {
        return parent::cloneStandard($objectId);
    }

    /**
     * Deletes the entity.
     *
     * @param int $objectId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($objectId)
    {
        return parent::deleteStandard($objectId);
    }

    /**
     * Deletes a group of entities.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function batchDeleteAction()
    {
        return parent::batchDeleteStandard();
    }

    /**
     * @param $args
     * @param $view
     *
     * @return array
     */
    public function customizeViewArguments($args, $view)
    {
        if ('view' == $view) {
            /** @var \MauticPlugin\MauticContactClientBundle\Entity\ContactClient $item */
            $item = $args['viewParameters']['item'];

            // For line graphs in the view
            $chartFilterValues = $this->request->get('chartfilter', []);
            $chartFilterForm   = $this->get('form.factory')->create(
                'chartfilter',
                $chartFilterValues,
                [
                    'action' => $this->generateUrl(
                        'mautic_contactclient_action',
                        [
                            'objectAction' => 'view',
                            'objectId'     => $item->getId(),
                        ]
                    ),
                ]
            );

            /** @var \MauticPlugin\MauticContactClientBundle\Model\ContactClientModel $model */
            $model = $this->getModel('contactclient');

            if (in_array($chartFilterForm->get('type')->getData(), ['All Events', null])) {
                $stats = $model->getStats(
                    $item,
                    null,
                    new \DateTime($chartFilterForm->get('date_from')->getData()),
                    new \DateTime($chartFilterForm->get('date_to')->getData())
                );
            } else {
                $stats = $model->getStatsBySource(
                    $item,
                    null,
                    $chartFilterForm->get('type')->getData(),
                    new \DateTime($chartFilterForm->get('date_from')->getData()),
                    new \DateTime($chartFilterForm->get('date_to')->getData())
                );
            }

            $args['viewParameters']['auditlog']        = $this->getAuditlogs($item);
            $args['viewParameters']['files']           = $this->getFiles($item);
            $args['viewParameters']['stats']           = $stats;
            $args['viewParameters']['events']          = $model->getEngagements($item);
            $args['viewParameters']['chartFilterForm'] = $chartFilterForm->createView();
            $args['viewParameters']['tableData']       = $this->convertChartStatsToDatatable($stats);
        }

        return $args;
    }

    /**
     * @param array $args
     * @param       $action
     *
     * @return array
     */
    protected function getPostActionRedirectArguments(array $args, $action)
    {
        $updateSelect = ('POST' == $this->request->getMethod())
            ? $this->request->request->get('contactclient[updateSelect]', false, true)
            : $this->request->get(
                'updateSelect',
                false
            );
        if ($updateSelect) {
            switch ($action) {
                case 'new':
                case 'edit':
                    $passthrough             = $args['passthroughVars'];
                    $passthrough             = array_merge(
                        $passthrough,
                        [
                            'updateSelect' => $updateSelect,
                            'id'           => $args['entity']->getId(),
                            'name'         => $args['entity']->getName(),
                        ]
                    );
                    $args['passthroughVars'] = $passthrough;
                    break;
            }
        }

        return $args;
    }

    /**
     * @return array
     */
    protected function getEntityFormOptions()
    {
        $updateSelect = ('POST' == $this->request->getMethod())
            ? $this->request->request->get('contactclient[updateSelect]', false, true)
            : $this->request->get(
                'updateSelect',
                false
            );
        if ($updateSelect) {
            return ['update_select' => $updateSelect];
        }
    }

    /**
     * Return array of options update select response.
     *
     * @param string $updateSelect HTML id of the select
     * @param object $entity
     * @param string $nameMethod   name of the entity method holding the name
     * @param string $groupMethod  name of the entity method holding the select group
     *
     * @return array
     */
    protected function getUpdateSelectParams(
        $updateSelect,
        $entity,
        $nameMethod = 'getName',
        $groupMethod = 'getLanguage'
    ) {
        $options = [
            'updateSelect' => $updateSelect,
            'id'           => $entity->getId(),
            'name'         => $entity->$nameMethod(),
        ];

        return $options;
    }

    protected function convertChartStatsToDatatable($stats)
    {
        $tableData = [
            'labels' => [],
            'data'   => [],
        ];

        if (!empty($stats)) {
            $tableData['labels'][] = ['title' => 'Date'];
            $row                   =[];
            foreach ($stats[datasets] as $column => $dataset) {
                $tableData['labels'][] = ['title' => $dataset['label']];
                foreach ($dataset['data'] as $key => $data) {
                    $dateStr = $stats['labels'][$key];

                    $row[$key][0]           = date('Y-m-d', strtotime($dateStr));
                    $row[$key][$column + 1] = $data;
                }
            }
            $tableData['data'] = $row;
        }

        return $tableData;
    }
}
