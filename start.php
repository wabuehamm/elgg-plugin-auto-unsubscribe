<?php

function auto_unsubscribe_init()
{
    // Add subscription setting
    elgg_extend_view('notifications/settings/other', 'notifications/subscriptions/personal_subscriptions');
    elgg_register_plugin_hook_handler('action', 'notificationsettings/save', 'auto_unsubscribe_save_subscription_setting');

    // Subscribe for comments and likes
    elgg_register_event_handler('create', 'object', 'auto_unsubscribe_subscription_subscribe_comment', 400);
    elgg_register_event_handler('create', 'annotation', 'auto_unsubscribe_subscription_subscribe_like', 400);

    // Remove subscriptions for discussions after the first notifications
    elgg_register_plugin_hook_handler('send:after', 'notifications', 'auto_unsubscribe_unsubscribe_after_notification');

}

/**
 * Store the subscription setting
 */
function auto_unsubscribe_save_subscription_setting($hook, $type, $return, $params)
{
    elgg_set_plugin_user_setting('subscription', get_input('auto_unsubscribe_subscription'), get_input('guid'), 'auto_unsubscribe');
    return true;
}

/**
 * Subscribe to a discussion on comment
 */
function auto_unsubscribe_subscription_subscribe_comment($event, $type, \ElggObject $object) {
    if (elgg_instanceof($object, 'object', 'discussion_reply')) {
        content_subscriptions_subscribe($object->container_guid, elgg_get_logged_in_user_guid());
    }
}

/**
 * Subscribe to a discussion on like
 */
function auto_unsubscribe_subscription_subscribe_like($event, $type, $annotation) {
    if ($annotation instanceof \ElggAnnotation && $annotation->name == 'likes') {
        content_subscriptions_subscribe($annotation->getEntity()->guid, elgg_get_logged_in_user_guid());
    }
}

/**
 * Remove subscriptions for discussions after the first notifications for all
 * users beside the discussion owner
 */
function auto_unsubscribe_unsubscribe_after_notification($hook, $type, $return, $params)
{

    if (elgg_instanceof($params['event']->getObject(), 'object', 'discussion')) {
        foreach ($params['subscriptions'] as $guid => $methods) {
            if ($guid != $params['event']->getObject()->getOwnerGUID()) {
                if (elgg_get_plugin_user_setting('subscription', $guid, 'auto_unsubscribe') != 'subscribed') {
                    content_subscriptions_unsubscribe(
                        $params['event']->getObject()->getGUID(),
                        $guid
                    );
                }
            }
        }
    }

    return $return;
}

elgg_register_event_handler('init', 'system', 'auto_unsubscribe_init');

