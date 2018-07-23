<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Digital Media Solutions, LLC
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticContactClientBundle\Controller;

use Mautic\CoreBundle\Controller\AjaxController as CommonAjaxController;
use Mautic\CoreBundle\Controller\AjaxLookupControllerTrait;
use Mautic\CoreBundle\Helper\InputHelper;
use Mautic\CoreBundle\Translation\Translator;
use Mautic\LeadBundle\Entity\Lead as Contact;
use MauticPlugin\MauticContactClientBundle\Helper\TokenHelper;
use MauticPlugin\MauticContactClientBundle\Integration\ClientIntegration;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AjaxController.
 */
class AjaxController extends CommonAjaxController
{
    use AjaxLookupControllerTrait;

    /**
     * Updates the timeline events and gets returns updated HTML.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    protected function updateTimelineAction(Request $request)
    {
        $dataArray              = ['success' => 0];
        $dateFrom               = InputHelper::clean($request->request->get('dateFrom', []));
        $dateTo                 = InputHelper::clean($request->request->get('dateTo', []));
        $search                 = InputHelper::clean($request->request->get('search'));
        $contactClientId        = (int) $request->request->get('contactClientId');

        if (!empty($leadId)) {
            //find the lead
            $model = $this->getModel('lead.lead');
            $lead  = $model->getEntity($leadId);

            if ($lead !== null) {
                $session = $this->get('session');

                $filter = [
                    'search'        => $search,
                    'dateFrom'      => $dateFrom,
                    'dateTo'        => $dateTo,
                ];

                $session->set('mautic.contactclient.'.$contactClientId.'.timeline.filters', $filter);

                // Trigger the TIMELINE_ON_GENERATE event to fetch the timeline events from subscribed bundles
                $dispatcher = $this->dispatcher;
                $event      = new LeadTimelineEvent($lead, $filter);
                $dispatcher->dispatch(LeadEvents::TIMELINE_ON_GENERATE, $event);

                $events     = $event->getEvents();
                $eventTypes = $event->getEventTypes();

                $timeline = $this->renderView(
                    'MauticLeadBundle:Lead:history.html.php',
                    [
                        'events'       => $events,
                        'eventTypes'   => $eventTypes,
                        'eventFilters' => $filter,
                        'lead'         => $lead,
                    ]
                );

                $dataArray['success']      = 1;
                $dataArray['timeline']     = $timeline;
                $dataArray['historyCount'] = count($events);
            }
        }

        return $this->sendJsonResponse($dataArray);
    }

    /**
     * @param Request $request
     *
     * @return mixed
     */

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @throws \Exception
     */
    protected function getApiPayloadTestAction(Request $request)
    {
        // Get the API payload to test.
        $apiPayload = html_entity_decode(InputHelper::clean($request->request->get('apiPayload')));

        $attributionDefault = html_entity_decode(InputHelper::clean($request->request->get('attributionDefault')));

        $attributionSettings = html_entity_decode(InputHelper::clean($request->request->get('attributionSettings')));

        // default to empty
        $dataArray = [
            'html'    => '',
            'valid'   => false,
            'success' => 0,
        ];

        if (!empty($apiPayload)) {
            /** @var Translator $translator */
            $translator = $this->get('translator');

            /** @var ClientIntegration $clientIntegration */
            $clientIntegration = $this->get('mautic.contactclient.integration');

            $valid                = $clientIntegration->sendTestApi(
                $apiPayload,
                $attributionDefault,
                $attributionSettings
            );
            $dataArray['valid']   = $valid;
            $dataArray['payload'] = $apiPayload;
            $dataArray['message'] = $valid ? $translator->trans(
                'mautic.contactclient.form.test_results.passed'
            ) : $translator->trans('mautic.contactclient.form.test_results.failed');
            $dataArray['error']   = $clientIntegration->getLogs('error');
            $dataArray['html']    = $clientIntegration->getLogsJSON();
            $dataArray['success'] = true;
        }

        return $this->sendJsonResponse($dataArray);
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @throws \Exception
     */
    protected function getFilePayloadTestAction(Request $request)
    {
        // Get the File payload to test.
        $filePayload = html_entity_decode(InputHelper::clean($request->request->get('filePayload')));

        $attributionDefault = html_entity_decode(InputHelper::clean($request->request->get('attributionDefault')));

        $attributionSettings = html_entity_decode(InputHelper::clean($request->request->get('attributionSettings')));

        // default to empty
        $dataArray = [
            'html'    => '',
            'valid'   => false,
            'success' => 0,
        ];

        if (!empty($filePayload)) {
            /** @var Translator $translator */
            $translator = $this->get('translator');

            /** @var ClientIntegration $clientIntegration */
            $clientIntegration = $this->get('mautic.contactclient.integration');

            $valid                = $clientIntegration->sendTestFile(
                $filePayload,
                $attributionDefault,
                $attributionSettings
            );
            $dataArray['valid']   = $valid;
            $dataArray['payload'] = $filePayload;
            $dataArray['message'] = $valid ? $translator->trans(
                'mautic.contactclient.form.test_results.passed'
            ) : $translator->trans('mautic.contactclient.form.test_results.failed');
            $dataArray['error']   = $clientIntegration->getLogs('error');
            $dataArray['html']    = $clientIntegration->getLogsJSON();
            $dataArray['success'] = true;
        }

        return $this->sendJsonResponse($dataArray);
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @throws \Exception
     */
    protected function getTokensAction(Request $request)
    {
        $dataArray = [
            'tokens'  => [],
            'types'   => [],
            'formats' => [],
            'success' => 0,
        ];

        // Get an array representation of the current payload (of last save) for context.
        $filePayload = [];
        // Leaving File payload out of the tokens for now, since token use is not cognizant of the type yet.
        // $filePayload = html_entity_decode(InputHelper::clean($request->request->get('filePayload')));
        // $filePayload = json_decode($filePayload, true);
        // $filePayload = is_array($filePayload) ? $filePayload : [];
        $apiPayload = html_entity_decode(InputHelper::clean($request->request->get('apiPayload')));
        $apiPayload = json_decode($apiPayload, true);
        $apiPayload = is_array($apiPayload) ? $apiPayload : [];
        $payload    = array_merge($filePayload, $apiPayload);

        /** @var \Mautic\LeadBundle\Model\FieldModel $fieldModel */
        $fieldModel = $this->get('mautic.lead.model.field');

        // Exclude company fields as they are not currently used by the token helper.
        $fields      = $fieldModel->getEntities(
            [
                'filter'         => [
                    'force' => [
                        [
                            'column' => 'f.isPublished',
                            'expr'   => 'eq',
                            'value'  => true,
                        ],
                        [
                            'column' => 'f.object',
                            'expr'   => 'notLike',
                            'value'  => 'company',
                        ],
                    ],
                ],
                'hydration_mode' => 'HYDRATE_ARRAY',
            ]
        );
        $contact     = new Contact();
        $fieldGroups = [];
        foreach ($fields as $field) {
            $fieldGroups[$field['group']][$field['alias']] = [
                'value' => $field['label'],
                'type'  => $field['type'],
                'label' => $field['label'],
            ];
        }
        $contact->setFields($fieldGroups);

        /** @var TokenHelper $tokenHelper */
        $tokenHelper = $this->get('mautic.contactclient.helper.token');
        $tokenHelper->newSession(null, $contact, $payload);

        $tokens = $tokenHelper->getContextLabeled();
        if ($tokens) {
            $dataArray['tokens']  = $tokens;
            $dataArray['success'] = true;
            $types                = $tokenHelper->getContextTypes();
            if ($types) {
                $dataArray['types'] = $types;
            }
            $dataArray['formats'] = [
                'date'       => $tokenHelper->getDateFormatHelper()->getFormatsDate(),
                'datetime'   => $tokenHelper->getDateFormatHelper()->getFormatsDateTime(),
                'time'       => $tokenHelper->getDateFormatHelper()->getFormatsTime(),
                'number'     => $tokenHelper->getFormatNumber(),
            ];
        }

        return $this->sendJsonResponse($dataArray);
    }
}
