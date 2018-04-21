<?php

$user = elgg_extract('user', $vars);

$value = elgg_get_plugin_user_setting('subscription', $user->guid, 'auto_unsubscribe', 'specific');

echo elgg_view_field([
    '#type' => 'select',
    '#label' => elgg_echo("auto_unsubscribe:notification:subscription"),
    'name' => 'auto_unsubscribe_subscription',
    'value' => $value,
    'options_values' => [
        'all' => elgg_echo('auto_unsubscribe:notification:subscription:all'),
        'specific' => elgg_echo('auto_unsubscribe:notification:subscription:specific')
    ]
]);