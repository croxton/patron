<?php

namespace flipbox\patron\cp\controllers\view\providers;

use Craft;
use craft\helpers\UrlHelper;
use flipbox\craft\assets\circleicon\CircleIcon;
use flipbox\patron\Patron;
use flipbox\patron\records\Provider;
use flipbox\patron\records\ProviderInstance;

class InstancesController extends AbstractViewController
{

    /**
     * The index view template path
     */
    const TEMPLATE_INDEX = AbstractViewController::TEMPLATE_BASE . '/instances';

    /**
     * The upsert view template path
     */
    const TEMPLATE_UPSERT = self::TEMPLATE_INDEX . '/upsert';

    /**
     * @param null $provider
     * @param null $identifier
     * @param ProviderInstance|null $instance
     * @return \yii\web\Response
     * @throws \flipbox\craft\ember\exceptions\RecordNotFoundException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpsert($provider = null, $identifier = null, ProviderInstance $instance = null)
    {
        Craft::$app->getView()->registerAssetBundle(CircleIcon::class);

        $provider = Provider::getOne([
            'id' => $provider,
            'enabled' => null,
            'environment' => null
        ]);

        // Empty variables for template
        $variables = [];

        if (null === $instance) {
            if (null === $identifier) {
                $instance = new ProviderInstance();
            } else {
                $instance = ProviderInstance::getOne(['id' => $identifier]);
            }
        }

        $instance->setProvider($provider);

        // Template variables
        $this->instanceVariables($variables, $provider, $instance);

        $availableEnvironments = array_merge(
            $this->availableEnvironments($provider),
            $instance->getEnvironments()
                ->indexBy(null)
                ->select(['environment'])
                ->column()
        );

        $instanceOptions = [];
        foreach (Patron::getInstance()->getSettings()->getEnvironments() as $env) {
            $instanceOptions[] = [
                'label' => Craft::t('patron', $env),
                'value' => $env,
                'disabled' => !in_array($env, $availableEnvironments, true)
            ];
        }

        $variables['provider'] = $provider;
        $variables['instance'] = $instance;
        $variables['environmentOptions'] = $instanceOptions;

        // Full page form in the CP
        $variables['fullPageForm'] = true;

        // Tabs
        $variables['tabs'] = $this->getTabs($provider);
        $variables['selectedTab'] = 'general';

        return $this->renderTemplate(static::TEMPLATE_UPSERT, $variables);
    }


    /*******************************************
     * BASE VARIABLES
     *******************************************/

    /**
     * @inheritdoc
     */
    protected function getBaseCpPath(): string
    {
        return $this->getBaseCpProviderPath() . '/' . Craft::$app->getRequest()->getSegment(4);
    }

    /**
     * @inheritdoc
     */
    protected function getBaseActionPath(): string
    {
        return parent::getBaseActionPath() . '/instances';
    }


    /*******************************************
     * VARIABLES
     *******************************************/

    /**
     * @param array $variables
     * @param Provider $provider
     */
    protected function instanceVariables(array &$variables, Provider $provider, ProviderInstance $instance)
    {
        $this->updateVariables($variables, $provider);

        // Set the "Continue Editing" URL
        $continueEditingPath = $instance->getId() ?: '{id}';
        $variables['continueEditingUrl'] = $this->getBaseContinueEditingUrl('/' . $continueEditingPath);

        $variables['title'] .= ' ' . Craft::t('patron', "Instance");

        // Breadcrumbs
        $variables['crumbs'][] = [
            'label' => Craft::t('patron', "Instance"),
            'url' => UrlHelper::url(
                $variables['baseCpPath'] . '/instances/' .
                Craft::$app->getRequest()->getSegment(5)
            )
        ];
    }
}
