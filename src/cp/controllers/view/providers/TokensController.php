<?php

namespace flipbox\patron\cp\controllers\view\providers;

use Craft;
use craft\helpers\UrlHelper;
use flipbox\patron\web\assets\circleIcon\CircleIcon;
use flipbox\patron\records\Provider;
use flipbox\patron\records\Token;

class TokensController extends AbstractViewController
{
    /**
     * The index view template path
     */
    const TEMPLATE_INDEX = AbstractViewController::TEMPLATE_BASE . '/tokens';

    /**
     * The upsert view template path
     */
    const TEMPLATE_UPSERT = self::TEMPLATE_INDEX . '/upsert';

    /**
     * @param $provider
     * @return \yii\web\Response
     * @throws \ReflectionException
     * @throws \flipbox\craft\ember\exceptions\RecordNotFoundException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionIndex($provider)
    {
        Craft::$app->getView()->registerAssetBundle(CircleIcon::class);

        // Empty variables for template
        $variables = [];

        $provider = Provider::getOne([
            'id' => $provider,
            'enabled' => null
        ]);

        // Template variables
        $this->tokenVariables($variables, $provider);

        $variables['provider'] = $provider;

        // Tabs
        $variables['tabs'] = $this->getTabs($provider);
        $variables['selectedTab'] = 'tokens';

        return $this->renderTemplate(static::TEMPLATE_INDEX, $variables);
    }

    /**
     * @param $provider
     * @param $identifier
     * @param Token|null $token
     * @return \yii\web\Response
     * @throws \ReflectionException
     * @throws \flipbox\craft\ember\exceptions\RecordNotFoundException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpsert($provider, $identifier, Token $token = null)
    {
        Craft::$app->getView()->registerAssetBundle(CircleIcon::class);

        // Empty variables for template
        $variables = [];

        $provider = Provider::getOne([
            'id' => $provider,
            'enabled' => null
        ]);

        if (null === $token) {
            $token = Token::getOne([
                is_numeric($identifier) ? 'id' : 'accessToken' => $identifier,
                'enabled' => null,
            ]);
        }

        // Template variables
        $this->tokenUpdateVariables($variables, $provider, $token);

        $variables['provider'] = $provider;
        $variables['token'] = $token;

        // Full page form in the CP
        $variables['fullPageForm'] = true;

        // Tabs
        $variables['tabs'] = $this->getTabs($provider);
        $variables['selectedTab'] = 'tokens';

        return $this->renderTemplate(static::TEMPLATE_UPSERT, $variables);
    }

    /*******************************************
     * PATHS
     *******************************************/

    /**
     * @inheritdoc
     */
    protected function getBaseCpPath(): string
    {
        return parent::getBaseCpPath() . '/' . Craft::$app->getRequest()->getSegment(3) .
            '/' . Craft::$app->getRequest()->getSegment(4);
    }

    /**
     * @inheritdoc
     */
    protected function getBaseActionPath(): string
    {
        return parent::getBaseActionPath() . '/tokens';
    }


    /*******************************************
     * VARIABLES
     *******************************************/

    /**
     * @param array $variables
     * @param Provider $provider
     * @throws \ReflectionException
     */
    protected function tokenVariables(array &$variables, Provider $provider)
    {
        $this->updateVariables($variables, $provider);

        // Set the "Continue Editing" URL
        $variables['continueEditingUrl'] = $this->getBaseContinueEditingUrl();

        // Title
        $variables['title'] .= ' ' . Craft::t('patron', "Tokens");

        // Breadcrumbs
        $variables['crumbs'][] = [
            'label' => Craft::t('patron', "Tokens"),
            'url' => UrlHelper::url(
                $variables['baseCpPath']
            )
        ];
    }

    /**
     * @param array $variables
     * @param Provider $provider
     * @param Token $token
     * @throws \ReflectionException
     */
    protected function tokenUpdateVariables(array &$variables, Provider $provider, Token $token)
    {
        $this->tokenVariables($variables, $provider);

        // Set the "Continue Editing" URL
        $variables['continueEditingUrl'] = $this->getBaseContinueEditingUrl('/' . $token->getId());

        $variables['title'] .= ' ' . Craft::t('patron', "Edit");

        // Breadcrumbs
        $variables['crumbs'][] = [
            'label' => Craft::t('patron', "Edit"),
            'url' => UrlHelper::url(
                $variables['baseCpPath'] . '/' . $token->getId()
            )
        ];
    }
}
