<?php

namespace Wabue\Elgg\AutoUnsubscribe;

use Elgg\DefaultPluginBootstrap;
use ElggAnnotation;
use ElggComment;
use ElggEntity;
use ElggObject;

class Bootstrap extends DefaultPluginBootstrap
{
    private function registerViews()
    {
        // Add subscription setting
        elgg_extend_view('notifications/settings/other', 'notifications/subscriptions/personal_subscriptions');
    }

    public function saveSubscriptionSetting()
    {
        elgg_set_plugin_user_setting('subscription', get_input('auto_unsubscribe_subscription'), get_input('guid'), 'auto_unsubscribe');
        return true;
    }

    public function subscribeToComments($event, $type, ElggObject $object)
    {
        if ($object instanceof ElggComment) {
            /** @noinspection PhpUndefinedFunctionInspection */
            content_subscriptions_subscribe($object->container_guid, elgg_get_logged_in_user_guid());
        }
    }

    public function subscribeToLikes($event, $type, ElggAnnotation $object)
    {
        if ($object instanceof ElggAnnotation && $object->name == 'likes') {
            /** @noinspection PhpUndefinedFunctionInspection */
            content_subscriptions_subscribe($object->getEntity()->guid, elgg_get_logged_in_user_guid());
        }
    }

    public function removeSubscriptionAfterCreate($hook, $type, $return, $params)
    {
        /** @var ElggEntity $object */
        $object = $params['event']->getObject();
        if ($object instanceof ElggEntity && $object->getType() == 'object' && $object->getSubType() == 'discussion') {
            foreach ($params['subscriptions'] as $guid => $methods) {
                if ($guid != $params['event']->getObject()->getOwnerGUID()) {
                    if (elgg_get_plugin_user_setting('subscription', $guid, 'auto_unsubscribe') != 'subscribed') {
                        /** @noinspection PhpUndefinedFunctionInspection */
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

    private function registerHandlers()
    {
        // Store plugin user setting
        elgg_register_plugin_hook_handler('action', 'notificationsettings/save', [$this, 'saveSubscriptionSetting']);

        // Subscribe for comments and likes
        elgg_register_event_handler('create', 'object', [$this, 'subscribeToComments'], 400);
        elgg_register_event_handler('create', 'annotation', [$this, 'subscribeToLikes'], 400);

        // Remove subscriptions for discussions after the first notifications
        elgg_register_plugin_hook_handler('send:after', 'notifications', [$this, 'removeSubscriptionAfterCreate']);
    }

    public function boot()
    {
        if (elgg_is_active_plugin('content_subscriptions')) {
            $this->registerViews();
            $this->registerHandlers();
        }
    }

}
