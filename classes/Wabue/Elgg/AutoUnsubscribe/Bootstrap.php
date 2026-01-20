<?php

namespace Wabue\Elgg\AutoUnsubscribe;

use Elgg\DefaultPluginBootstrap;
use Elgg\Event;
use ElggAnnotation;
use ElggComment;
use ElggEntity;

class Bootstrap extends DefaultPluginBootstrap
{
    private function registerViews(): void
    {
        // Add subscription setting
        elgg_extend_view('forms/settings/notifications', 'notifications/subscriptions/personal_subscriptions');
    }

    public function saveSubscriptionSetting(): void
    {
        /** @var \ElggUser $user */
        $user = get_entity(get_input('guid'));
        $user->setPluginSetting('auto_unsubscribe','subscription', get_input('auto_unsubscribe_subscription'));
    }

    public function subscribeToComments(Event $event): void
    {
        $object = $event->getEntityParam();
        if ($object instanceof ElggComment) {
            $object->addSubscription(elgg_get_logged_in_user_guid());
        }
    }

    public function subscribeToLikes(Event $event): void
    {
        $object = $event->getEntityParam();
        if ($object instanceof ElggAnnotation && $object->name == 'likes') {
            $object->getEntity()->removeSubscription(elgg_get_logged_in_user_guid());
        }
    }

    public function removeSubscriptionAfterCreate(Event $event): void
    {
        $object = $event->getParam('event')->getObject();
        if ($object instanceof ElggEntity && $object->getType() == 'object' && $object->getSubType() == 'discussion') {
            foreach ($event->getParam('subscriptions') as $guid => $methods) {
                if ($guid != $object->getOwnerGUID()) {
                    if (elgg_get_plugin_user_setting('subscription', $guid, 'auto_unsubscribe') != 'subscribed') {
                        $object->muteNotifications($guid);
                    }
                }
            }
        }
    }

    private function registerHandlers(): void
    {
        // Store plugin user setting
        elgg_register_event_handler('action:validate', 'notifications/settings', [$this, 'saveSubscriptionSetting']);

        // Subscribe for comments and likes
        elgg_register_event_handler('create', 'object', [$this, 'subscribeToComments'], 400);
        elgg_register_event_handler('create', 'annotation', [$this, 'subscribeToLikes'], 400);

        // Remove subscriptions for discussions after the first notifications
        elgg_register_event_handler('send:after', 'notifications', [$this, 'removeSubscriptionAfterCreate']);
    }

    public function init(): void
    {
        $this->registerViews();
        $this->registerHandlers();
    }

}
