<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\records;

use Craft;
use craft\helpers\DateTimeHelper;
use craft\validators\DateTimeValidator;
use DateTime;
use flipbox\craft\ember\helpers\ModelHelper;
use flipbox\craft\ember\helpers\QueryHelper;
use flipbox\craft\ember\records\ActiveRecordWithId;
use flipbox\craft\ember\records\StateAttributeTrait;
use flipbox\patron\queries\TokenActiveQuery;
use yii\db\ActiveQueryInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @property string $accessToken
 * @property string $refreshToken
 * @property DateTime|null $dateExpires
 * @property array $values
 * @property ProviderInstance[] $instances
 * @property TokenEnvironment[] $environments
 */
class Token extends ActiveRecordWithId
{
    use StateAttributeTrait,
        ProviderAttributeTrait,
        RelatedEnvironmentsAttributeTrait;

    /**
     * The table alias
     */
    const TABLE_ALIAS = 'patron_tokens';

    /**
     * @inheritdoc
     */
    protected $getterPriorityAttributes = [
        'providerId'
    ];

    /*******************************************
     * QUERY
     *******************************************/

    /**
     * @inheritdoc
     * @return TokenActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public static function find()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Craft::createObject(TokenActiveQuery::class, [get_called_class()]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            $this->stateRules(),
            $this->providerRules(),
            [
                [
                    [
                        'accessToken',
                        'refreshToken'
                    ],
                    'unique'
                ],
                [
                    [
                        'dateExpires'
                    ],
                    DateTimeValidator::class
                ],
                [
                    [
                        'providerId',
                        'accessToken'
                    ],
                    'required'
                ],
                [
                    [
                        'accessToken',
                        'values',
                        'dateExpires',
                        'environments'
                    ],
                    'safe',
                    'on' => [
                        ModelHelper::SCENARIO_DEFAULT
                    ]
                ]
            ]
        );
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isEnabled() && !$this->hasExpired();
    }

    /**
     * @return bool
     */
    public function hasExpired(): bool
    {
        $dateExpires = $this->dateExpires ?: new DateTime('now');
        return DateTimeHelper::isInThePast($dateExpires);
    }


    /*******************************************
     * EVENTS
     *******************************************/

    /**
     * @inheritdoc
     */
    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        return $this->beforeSaveEnvironments($insert);
    }


    /*******************************************
     * UPDATE / INSERT
     *******************************************/

    /**
     * @inheritdoc
     * @throws \Throwable
     */
    protected function insertInternal($attributes = null)
    {
        if (!parent::insertInternal($attributes)) {
            return false;
        }

        return $this->insertInternalEnvironments($attributes);
    }

    /**
     * @inheritdoc
     * @throws \Throwable
     */
    protected function updateInternal($attributes = null)
    {
        if (false === ($response = parent::updateInternal($attributes))) {
            return false;
        }

        return $this->upsertEnvironmentsInternal($attributes) ? $response : false;
    }

    /*******************************************
     * ENVIRONMENTS
     *******************************************/

    /**
     * @inheritdoc
     */
    protected static function environmentRecordClass(): string
    {
        return TokenEnvironment::class;
    }

    /**
     * @inheritdoc
     */
    protected function prepareEnvironmentRecordConfig(array $config = []): array
    {
        $config['token'] = $this;
        return $config;
    }

    /**
     * @inheritdoc
     */
    protected function environmentRelationshipQuery(): ActiveQueryInterface
    {
        return $this->hasMany(
            static::environmentRecordClass(),
            ['tokenId' => 'id']
        );
    }

    /**
     * Get all of the associated provider instances.
     *
     * @param array $config
     * @return \yii\db\ActiveQueryInterface
     */
    public function getInstances(array $config = [])
    {
        $query = $this->hasMany(
            ProviderInstance::class,
            ['providerId' => 'providerId']
        );

        if (!empty($config)) {
            QueryHelper::configure(
                $query,
                $config
            );
        }

        return $query;
    }
}
