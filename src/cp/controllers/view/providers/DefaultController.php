<?php

namespace flipbox\patron\cp\controllers\view\providers;

use Craft;
use flipbox\patron\web\assets\card\Card;
use flipbox\patron\web\assets\circleIcon\CircleIcon;
use flipbox\patron\helpers\ProviderHelper;
use flipbox\patron\records\Provider;
use flipbox\patron\web\assets\providers\ProvidersAsset;

class DefaultController extends AbstractViewController
{
    /**
     * The index view template path
     */
    const TEMPLATE_INDEX = AbstractViewController::TEMPLATE_BASE;

    /**
     * The upsert view template path
     */
    const TEMPLATE_UPSERT = self::TEMPLATE_INDEX . '/upsert';

    /**
     * @return \yii\web\Response
     * @throws \yii\base\InvalidConfigException
     */
    public function actionIndex()
    {
        Craft::$app->getView()->registerAssetBundle(Card::class);
        Craft::$app->getView()->registerAssetBundle(CircleIcon::class);

        // Empty variables for template
        $variables = [];

        // apply base view variables
        $this->baseVariables($variables);

        // Full page form in the CP
        $variables['fullPageForm'] = Craft::$app->getConfig()->getGeneral()->allowAdminChanges;

        // Configured providers
        $variables['providers'] = Provider::findAll([
            'enabled' => null
        ]);

        return $this->renderTemplate(static::TEMPLATE_INDEX, $variables);
    }

    /**
     * @param null $identifier
     * @param Provider|null $provider
     * @return \yii\web\Response
     * @throws \ReflectionException
     * @throws \craft\errors\InvalidPluginException
     * @throws \flipbox\craft\ember\exceptions\RecordNotFoundException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpsert($identifier = null, Provider $provider = null)
    {
        $this->getView()->registerAssetBundle(ProvidersAsset::class);

        // Empty variables for template
        $variables = [];

        if (null === $provider) {
            if (null === $identifier) {
                $provider = new Provider();
            } else {
                $provider = Provider::getOne([
                    'enabled' => null,
                    is_numeric($identifier) ? 'id' : 'handle' => $identifier
                ]);
            }
        }

        // Template variables
        if (!$provider->getId()) {
            $this->insertVariables($variables);
        } else {
            $this->updateVariables($variables, $provider);
        }

        $providerInfo = $this->module->getProviderInfo();

        // Available providers options
        $providerOptions = [];
        $providers = $this->module->getProviders();
        foreach ($providers as $availableProvider) {
            $info = $providerInfo[$availableProvider] ?? [];
            $providerOptions[] = [
                'label' => $info['name'] ?? ProviderHelper::displayName($availableProvider),
                'value' => $availableProvider
            ];
        }

        $variables['providers'] = $providers;
        $variables['providerOptions'] = $providerOptions;
        $variables['provider'] = $provider;

        $pluginLocks = [];
        $pluginHandles = $provider->getLocks()
            ->alias('locks')
            ->leftJoin('{{%plugins}} plugins', '[[plugins.id]]=[[locks.pluginId]]')
            ->select(['handle'])->column();

        foreach ($pluginHandles as $pluginHandle) {
            $pluginLocks[] = array_merge(
                Craft::$app->getPlugins()->getPluginInfo($pluginHandle),
                [
                    'icon' => Craft::$app->getPlugins()->getPluginIconSvg($pluginHandle)
                ]
            );
        }

        // Plugins that have locked this provider
        $variables['pluginLocks'] = $pluginLocks;

        // Full page form in the CP
        $variables['fullPageForm'] = Craft::$app->getConfig()->getGeneral()->allowAdminChanges;

        // Tabs
        $variables['tabs'] = $this->getTabs($provider);
        $variables['selectedTab'] = 'general';

        return $this->renderTemplate(static::TEMPLATE_UPSERT, $variables);
    }
}
