<?php

/*
 * @copyright   2016 Mautic, Inc. All rights reserved
 * @author      Mautic, Inc
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticContactClientBundle\Form\Type;

use Mautic\CoreBundle\Form\EventListener\CleanFormSubscriber;
use Mautic\CoreBundle\Form\EventListener\FormExitSubscriber;
use Mautic\CoreBundle\Security\Permissions\CorePermissions;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ContactClientType extends AbstractType
{
    /**
     * @var CorePermissions
     */
    private $security;

    /**
     * ContactClientType constructor.
     *
     * @param CorePermissions $security
     */
    public function __construct(CorePermissions $security)
    {
        $this->security = $security;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // $builder->addEventSubscriber(new CleanFormSubscriber(['website' => 'url', 'specification' => 'html']));
        // $builder->addEventSubscriber(new FormExitSubscriber('contactclient', $options));

        $builder->add(
            'name',
            'text',
            [
                'label'      => 'mautic.core.name',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
            ]
        );

        $builder->add(
            'description',
            'textarea',
            [
                'label'      => 'mautic.core.description',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control editor'],
                'required'   => false,
            ]
        );

        $builder->add(
            'specification',
            'textarea',
            [
                'label'      => 'mautic.contactclient.form.specification',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'        => 'form-control',
                    'rows'         => 12,
                    'onchange'     => 'Mautic.contactclientUpdatePreview()',
                ],
                'required' => false,
            ]
        );

        $builder->add(
            'website',
            'url',
            [
                'label'      => 'mautic.contactclient.form.website',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'mautic.contactclient.form.website.tooltip',
                ],
                'required' => false,
            ]
        );

        //add category
        $builder->add(
            'category',
            'category',
            [
                'bundle' => 'plugin:contactclient',
            ]
        );

        if (!empty($options['data']) && $options['data']->getId()) {
            $readonly = !$this->security->isGranted('plugin:contactclient:items:publish');
            $data     = $options['data']->isPublished(false);
        } elseif (!$this->security->isGranted('plugin:contactclient:items:publish')) {
            $readonly = true;
            $data     = false;
        } else {
            $readonly = false;
            $data     = false;
        }

        $builder->add(
            'isPublished',
            'yesno_button_group',
            [
                'read_only' => $readonly,
                'data'      => $data,
            ]
        );

        $builder->add(
            'publishUp',
            'datetime',
            [
                'widget'     => 'single_text',
                'label'      => 'mautic.core.form.publishup',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                    'data-toggle' => 'datetime',
                ],
                'format'   => 'yyyy-MM-dd HH:mm',
                'required' => false,
            ]
        );

        $builder->add(
            'publishDown',
            'datetime',
            [
                'widget'     => 'single_text',
                'label'      => 'mautic.core.form.publishdown',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                    'data-toggle' => 'datetime',
                ],
                'format'   => 'yyyy-MM-dd HH:mm',
                'required' => false,
            ]
        );

        $builder->add(
            'type',
            'button_group',
            [
                'label' => 'mautic.contactclient.form.type',
                'label_attr' => ['class' => 'control-label'],
                'choices' => [
                    'mautic.contactclient.form.type.api' => 'api',
                    'mautic.contactclient.form.type.file' => 'file',
                ],
                'choices_as_values' => true,
                'required' => true,
                'attr'       => [
                    'class'       => 'form-control',
                    'tooltip'     => 'mautic.contactclient.form.type.tooltip',
                ],
            ]
        );

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }

        $customButtons = [];

        if (!empty($options['update_select'])) {
            $builder->add(
                'buttons',
                'form_buttons',
                [
                    'apply_text'        => false,
                    'pre_extra_buttons' => $customButtons,
                ]
            );
            $builder->add(
                'updateSelect',
                'hidden',
                [
                    'data'   => $options['update_select'],
                    'mapped' => false,
                ]
            );
        } else {
            $builder->add(
                'buttons',
                'form_buttons',
                [
                    'pre_extra_buttons' => $customButtons,
                ]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'MauticPlugin\MauticContactClientBundle\Entity\ContactClient',
            ]
        );
        $resolver->setDefined(['update_select']);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'contactclient';
    }
}
