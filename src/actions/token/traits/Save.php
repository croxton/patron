<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\actions\token\traits;

use flipbox\patron\records\Token;
use yii\db\ActiveRecord;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait Save
{
    /**
     * @inheritdoc
     * @param Token $record
     */
    protected function performAction(ActiveRecord $record): bool
    {
        $record->autoSaveEnvironments = true;


        return $record->save();
    }
}
