<?php

$user = elgg_extract('user', $vars);

$value = elgg_get_plugin_user_setting('subscription', $user->guid, 'auto_unsubscribe', 'unsubscribed');

echo elgg_view_field([
    '#type' => 'select',
    '#label' => elgg_echo("auto_unsubscribe:notification:subscription"),
    '#help' => elgg_view_icon('help') . elgg_echo('auto_unsubscribe:notification:subscription:help'),
    'name' => 'auto_unsubscribe_subscription',
    'value' => $value,
    'options_values' => [
        'subscribed' => elgg_echo('auto_unsubscribe:notification:subscription:subscribed'),
        'unsubscribed' => elgg_echo('auto_unsubscribe:notification:subscription:unsubscribed')
    ]
]);