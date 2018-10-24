<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\records\traits;

use flipbox\ember\records\traits\ActiveRecord;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait TokenAttribute
{
    use ActiveRecord, TokenRules, TokenMutator;
}
