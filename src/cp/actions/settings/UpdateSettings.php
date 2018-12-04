<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\cp\actions\settings;

use Craft;
use craft\helpers\ArrayHelper;
use flipbox\craft\ember\actions\models\CreateModel;
use flipbox\craft\ember\exceptions\ModelNotFoundException;
use flipbox\patron\models\Settings;
use flipbox\patron\Patron;
use yii\base\Model;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method array parentNormalizeSiteConfig($config = [])
 */
class UpdateSettings extends CreateModel
{
    public $validBodyParams = [
        'callbackUrlPath',
        'encryptStorageData',
        'autoPopulateTokenEnvironments',
        'applyProviderEnvironmentsToToken'
    ];

    /**
     * @inheritdoc
     */
    public $statusCodeSuccess = 200;

    /**
     * @inheritdoc
     * @param Settings $model
     * @return Settings
     */
    protected function populate(Model $model): Model
    {
        $model->setEnvironments(
            $this->environmentValuesFromBody()
        );

        return parent::populate($model);
    }

    /**
     * Normalize settings from body
     *
     * @return array
     */
    protected function environmentValuesFromBody(): array
    {
        $environmentArray = [];
        if ($rawEnvironments = Craft::$app->getRequest()->getBodyParam('environments', [])) {
            foreach (ArrayHelper::toArray($rawEnvironments) as $rawEnvironment) {
                $environmentArray = array_merge(
                    $environmentArray,
                    $this->normalizeEnvironmentValue($rawEnvironment)
                );
            }
        }
        return array_values($environmentArray);
    }

    /**
     * @param string|array $value
     * @return array
     */
    protected function normalizeEnvironmentValue($value = []): array
    {
        if (is_array($value)) {
            $value = ArrayHelper::getValue($value, 'value');
        }

        return [$value => $value];
    }

    /**
     * @param Model $model
     * @return bool
     * @throws \Throwable
     */
    protected function performAction(Model $model): bool
    {
        if (!$model instanceof Settings) {
            throw new ModelNotFoundException(sprintf(
                "Settings must be an instance of '%s', '%s' given.",
                Settings::class,
                get_class($model)
            ));
        }

        return Patron::getInstance()->getCp()->getSettings()->save($model);
    }

    /**
     * @inheritdoc
     * @return Settings
     */
    protected function newModel(array $config = []): Model
    {
        return clone Patron::getInstance()->getSettings();
    }
}
