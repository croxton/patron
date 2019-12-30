<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\events;

use Craft;
use flipbox\patron\records\Provider;
use yii\base\Event;
use yii\db\AfterSaveEvent;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.1.0
 */
class ManageProviderProjectConfig
{
    /**
     * @param AfterSaveEvent $event
     * @throws \yii\base\ErrorException
     * @throws \yii\base\Exception
     * @throws \yii\base\NotSupportedException
     * @throws \yii\web\ServerErrorHttpException
     */
    public static function save(AfterSaveEvent $event)
    {
        /** @var Provider $record */
        $record = $event->sender;

        Craft::$app->getProjectConfig()->set(
            'plugins.patron.providers.' . $record->uid,
            $record->toProjectConfig()
        );
    }

    /**
     * @param Event $event
     */
    public static function delete(Event $event)
    {
        /** @var Provider $record */
        $record = $event->sender;

        Craft::$app->getProjectConfig()->remove(
            'plugins.patron.providers.' . $record->uid
        );
    }
}
