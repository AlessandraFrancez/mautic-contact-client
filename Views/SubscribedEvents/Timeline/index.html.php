<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic, Inc
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

$logs = (isset($event['extra']['logs'])) ? $event['extra']['logs'] : null;
$integrationEntityId = (isset($event['extra']['integrationEntityId'])) ? $event['extra']['integrationEntityId'] : null;
?>
<?php if ($integrationEntityId): ?>
<dl class="dl-horizontal">
    <dt><?php echo $view['translator']->trans('mautic.contactclient.timeline.integration_entity_id'); ?></dt>
    <dd><?php echo $integrationEntityId; ?></dd>
</dl>
<?php endif; ?>
<?php if ($logs): ?>
<div class="small">
    <hr />
    <strong><?php echo $view['translator']->trans('mautic.contactclient.timeline.logs.heading') ?></strong>
    <br />
    <small>
        <pre>
            <?php echo $logs; ?>
        </pre>
    </small>
</div>
<?php endif; ?>
