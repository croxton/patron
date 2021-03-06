<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/patron/domains/
 */

namespace flipbox\patron\queries;

use craft\helpers\Db;
use yii\db\Expression;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait TokenAttributesTrait
{
    /**
     * @var bool|null The enabled state
     */
    public $enabled = true;

    /**
     * @var string|string[]|null The access token(s). Prefix IDs with "not " to exclude them.
     */
    public $accessToken;

    /**
     * @var string|string[]|null The refresh token(s). Prefix IDs with "not " to exclude them.
     */
    public $refreshToken;

    /**
     * Adds an additional WHERE condition to the existing one.
     * The new condition and the existing one will be joined using the `AND` operator.
     * @param string|array|Expression $condition the new WHERE condition. Please refer to [[where()]]
     * on how to specify this parameter.
     * @param array $params the parameters (name => value) to be bound to the query.
     * @return $this the query object itself
     * @see where()
     * @see orWhere()
     */
    abstract public function andWhere($condition, $params = []);

    /**
     * Appends a LEFT OUTER JOIN part to the query.
     * @param string|array $table the table to be joined.
     *
     * Use a string to represent the name of the table to be joined.
     * The table name can contain a schema prefix (e.g. 'public.user') and/or table alias (e.g. 'user u').
     * The method will automatically quote the table name unless it contains some parenthesis
     * (which means the table is given as a sub-query or DB expression).
     *
     * Use an array to represent joining with a sub-query. The array must contain only one element.
     * The value must be a [[Query]] object representing the sub-query while the corresponding key
     * represents the alias for the sub-query.
     *
     * @param string|array $on the join condition that should appear in the ON part.
     * Please refer to [[join()]] on how to specify this parameter.
     * @param array $params the parameters (name => value) to be bound to the query
     * @return $this the query object itself
     */
    abstract public function leftJoin($table, $on = '', $params = []);

    /**
     * @param $enabled
     * @return $this
     */
    public function enabled($enabled)
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * @param $accessToken
     * @return $this
     */
    public function accessToken($accessToken)
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    /**
     * @param $refreshToken
     * @return $this
     */
    public function refreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;
        return $this;
    }

    /**
     *
     */
    protected function applyTokenConditions()
    {
        if ($this->enabled !== null) {
            $this->andWhere(Db::parseParam('enabled', $this->enabled));
        }

        $attributes = ['accessToken', 'refreshToken'];

        foreach ($attributes as $attribute) {
            if (($value = $this->{$attribute}) !== null) {
                $this->andWhere(Db::parseParam($attribute, $value));
            }
        }
    }
}
