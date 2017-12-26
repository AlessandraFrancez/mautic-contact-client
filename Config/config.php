<?php

/*
 * @copyright   2016 Mautic, Inc. All rights reserved
 * @author      Mautic, Inc
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

return [
    'name'        => 'Mautic Contact Client',
    'description' => 'Send contacts to third party APIs or enhance your contacts without code.',
    'version'     => '1.0',
    'author'      => 'Mautic',

    'routes' => [
        'main' => [
            'mautic_contactclient_index' => [
                'path'       => '/contactclient/{page}',
                'controller' => 'MauticContactClientBundle:ContactClient:index',
            ],
            'mautic_contactclient_action' => [
                'path'       => '/contactclient/{objectAction}/{objectId}',
                'controller' => 'MauticContactClientBundle:ContactClient:execute',
            ],
        ],
    ],

    'services' => [
        'events' => [
            'mautic.contactclient.subscriber.stat' => [
                'class'     => 'MauticPlugin\MauticContactClientBundle\EventListener\StatSubscriber',
                'arguments' => [
                    'mautic.contactclient.model.contactclient',
                ],
            ],
            'mautic.contactclient.subscriber.contactclient' => [
                'class'     => 'MauticPlugin\MauticContactClientBundle\EventListener\ContactClientSubscriber',
                'arguments' => [
                    'router',
                    'mautic.helper.ip_lookup',
                    'mautic.core.model.auditlog',
                    'mautic.page.model.trackable',
                    'mautic.page.helper.token',
                    'mautic.asset.helper.token',
                    'mautic.form.helper.token',
                    'mautic.contactclient.model.contactclient',
                ],
            ],
            'mautic.contactclient.stats.subscriber' => [
                'class'     => \MauticPlugin\MauticContactClientBundle\EventListener\StatsSubscriber::class,
                'arguments' => [
                    'doctrine.orm.entity_manager',
                ],
            ],
            'mautic.contactclient.campaignbundle.subscriber' => [
                'class'     => 'MauticPlugin\MauticContactClientBundle\EventListener\CampaignSubscriber',
                'arguments' => [
                    'mautic.campaign.model.event',
                    'mautic.contactclient.model.contactclient',
                    'mautic.page.helper.tracking',
                    'router',
                ],
            ],
        ],
        'forms' => [
            'mautic.contactclient.form.type.contactclientshow_list' => [
                'class'     => 'MauticPlugin\MauticContactClientBundle\Form\Type\ContactClientShowType',
                'arguments' => 'router',
                'alias'     => 'contactclientshow_list',
            ],
            'mautic.contactclient.form.type.contactclient_list' => [
                'class'     => 'MauticPlugin\MauticContactClientBundle\Form\Type\ContactClientListType',
                'arguments' => 'mautic.contactclient.model.contactclient',
                'alias'     => 'contactclient_list',
            ],
        ],
        'models' => [
            'mautic.contactclient.model.contactclient' => [
                'class'     => 'MauticPlugin\MauticContactClientBundle\Model\ContactClientModel',
                'arguments' => [
                    'mautic.form.model.form',
                    'mautic.page.model.trackable',
                    'mautic.helper.templating',
                    'event_dispatcher',
                    'mautic.lead.model.lead',
                ],
            ],
        ],
        'other' => [
            'mautic.contactclient.helper.token' => [
                'class'     => 'MauticPlugin\MauticContactClientBundle\Helper\TokenHelper',
                'arguments' => [
                    'mautic.contactclient.model.contactclient',
                    'router',
                ],
            ],
        ],
    ],

    'menu' => [
        'main' => [
            'mautic.contactclient' => [
                'route'    => 'mautic_contactclient_index',
                'access'   => 'plugin:contactclient:items:view',
                'parent'   => 'mautic.core.channels',
                'priority' => 10,
            ],
        ],
    ],

    'categories' => [
        'plugin:contactclient' => 'mautic.contactclient',
    ],
];
