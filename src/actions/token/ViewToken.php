<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\actions\token;

use flipbox\craft\ember\actions\records\ViewRecord;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class ViewToken extends ViewRecord
{
    use LookupTokenTrait;

    /**
     * @inheritdoc
     */
    public function run($token)
    {
        return parent::run($token);
    }
}
