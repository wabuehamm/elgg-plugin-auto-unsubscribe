# Auto unsubscribe plugin

This plugin enhances the [content subscriptions](https://github.com/ColdTrick/content_subscriptions) plugin by unsubscribing all users from a discussion topic after the first notification took place. That way, all members of a group get a notification about a new topic, but afterwards only get notifications about replies, when they liked, commented or explicitely subscribed to the discussion topic.

# Requirements

* Elgg 2.3
* Content subscriptions 5.1.1

# Installation

Download a release and unzip the file into the mods directory of Elgg.

# Development

## Release

Run the following command to release a new version to GitHub

    GITHUB_TOKEN=<my token> grunt release:<new release number>
