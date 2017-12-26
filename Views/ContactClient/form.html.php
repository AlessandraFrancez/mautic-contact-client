<?php

/*
 * @copyright   2016 Mautic, Inc. All rights reserved
 * @author      Mautic, Inc
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$view->extend('MauticCoreBundle:Default:content.html.php');
$view['slots']->set('mauticContent', 'contactclient');

$header = ($entity->getId())
    ?
    $view['translator']->trans(
        'mautic.contactclient.edit',
        ['%name%' => $view['translator']->trans($entity->getName())]
    )
    :
    $view['translator']->trans('mautic.contactclient.new');
$view['slots']->set('headerTitle', $header);

echo $view['assets']->includeScript('plugins/MauticContactClientBundle/Assets/js/jquery.timepicker.min.js');
echo $view['assets']->includeScript('plugins/MauticContactClientBundle/Assets/js/jquery.businessHours.min.js');
echo $view['assets']->includeScript('plugins/MauticContactClientBundle/Assets/js/jsoneditor.min.js');
echo $view['assets']->includeScript('plugins/MauticContactClientBundle/Assets/js/query-builder.min.js');
echo $view['assets']->includeScript('https://cdnjs.cloudflare.com/ajax/libs/ace/1.2.9/ace.js');
echo $view['assets']->includeScript('plugins/MauticContactClientBundle/Assets/js/contactclient.js');

echo $view['assets']->includeStylesheet('plugins/MauticContactClientBundle/Assets/css/jquery.businessHours.css');
echo $view['assets']->includeStylesheet('plugins/MauticContactClientBundle/Assets/css/jquery.timepicker.min.css');
echo $view['assets']->includeStylesheet('plugins/MauticContactClientBundle/Assets/css/query-builder.default.min.css');
echo $view['assets']->includeStylesheet('plugins/MauticContactClientBundle/Assets/css/contactclient.css');

echo $view['form']->start($form);
?>
<!-- start: box layout -->
<div class="box-layout">

    <!-- tab container -->
    <div class="col-md-9 bg-white height-auto bdr-l">
        <div class="">
            <ul class="nav nav-tabs pr-md pl-md mt-10">
                <li class="active">
                    <a href="#details" role="tab" data-toggle="tab">
                        <?php echo $view['translator']->trans('mautic.contactclient.form.group.details'); ?>
                    </a>
                </li>
                <li>
                    <a href="#exclusive" role="tab" data-toggle="tab">
                        <?php echo $view['translator']->trans('mautic.contactclient.form.group.exclusive'); ?>
                    </a>
                </li>
                <li>
                    <a href="#filter" role="tab" data-toggle="tab">
                        <?php echo $view['translator']->trans('mautic.contactclient.form.group.filter'); ?>
                    </a>
                </li>
                <li>
                    <a href="#limit" role="tab" data-toggle="tab">
                        <?php echo $view['translator']->trans('mautic.contactclient.form.group.limit'); ?>
                    </a>
                </li>
                <li class="hide payload-tab">
                    <a href="#payload" role="tab" data-toggle="tab">
                        <?php echo $view['translator']->trans('mautic.contactclient.form.group.payload'); ?>
                    </a>
                </li>
                <li>
                    <a href="#schedule" role="tab" data-toggle="tab">
                        <?php echo $view['translator']->trans('mautic.contactclient.form.group.schedule'); ?>
                    </a>
                </li>
            </ul>
        </div>
        <div class="tab-content">
            <!-- pane -->
            <div class="tab-pane fade in active bdr-rds-0 bdr-w-0" id="details">
                <div class="pa-md">
                    <div class="form-group mb-0">
                        <div class="row">
                            <div class="col-md-5">
                                <?php echo $view['form']->row($form['name']); ?>
                            </div>
                            <div class="col-md-2 contactclient-type-group">
                                <?php echo $view['form']->row($form['type']); ?>
                            </div>
                            <div class="col-md-5">
                                <?php echo $view['form']->row($form['website']); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <?php echo $view['form']->row($form['description']); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade bdr-rds-0 bdr-w-0" id="capacity">
                <div class="pa-md">
                    <div class="form-group mb-0">
                        <div class="row">
                            <div class="col-sm-12">
                                fields
                            </div>
                        </div>
                        <hr class="mnr-md mnl-md">
                    </div>
                </div>
            </div>
            <div class="tab-pane fade bdr-rds-0 bdr-w-0" id="exclusive">
                <div class="pa-md">
                    <div class="form-group mb-0">
                        <div class="row">
                            <div class="col-sm-12">
                                fields
                            </div>
                        </div>
                        <hr class="mnr-md mnl-md">
                    </div>
                </div>
            </div>
            <div class="tab-pane fade bdr-rds-0 bdr-w-0" id="filter">
                <div class="pa-md">
                    <div class="form-group mb-0">
                        <div class="row">
                            <div class="col-sm-12">
                                fields
                            </div>
                        </div>
                        <hr class="mnr-md mnl-md">
                    </div>
                </div>
            </div>
            <div class="tab-pane fade bdr-rds-0 bdr-w-0" id="payload">
                <div class="pa-md">
                    <div class="form-group mb-0">
                        <div class="row">
                            <div class="col-md-12">
                                <?php echo $view['form']->row($form['api_payload']); ?>
                                <?php echo $view['form']->row($form['file_payload']); ?>
                            </div>
                        </div>
                        <hr class="mnr-md mnl-md">
                        Advanced widget to go here.
                    </div>
                </div>
            </div>
            <div class="tab-pane fade bdr-rds-0 bdr-w-0" id="schedule">
                <div class="pa-md">
                    <div class="form-group mb-0">
                        <div class="row">
                            <div class="col-sm-12">
                                fields
                            </div>
                        </div>
                        <hr class="mnr-md mnl-md">
                    </div>
                </div>
            </div>
            <!--/ #pane -->
        </div>
    </div>
    <!--/ tab container -->

    <!-- container -->
    <div class="col-md-3 bg-white height-auto">
        <div class="pr-lg pl-lg pt-md pb-md">
            <?php
            echo $view['form']->row($form['category']);
            echo $view['form']->row($form['isPublished']);
            echo $view['form']->row($form['publishUp']);
            echo $view['form']->row($form['publishDown']);
            ?>
            <hr />
        </div>
    </div>
    <!--/ container -->
</div>
<!--/ box layout -->







<?php echo $view['form']->end($form); ?>