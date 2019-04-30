<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\migrations;

use craft\db\Migration;
use craft\helpers\Json;
use flipbox\craft\ember\helpers\ObjectHelper;
use flipbox\patron\records\Provider;
use yii\db\Query;

class m190426_101341_project_config extends Migration
{
    /**
     * @return bool
     * @throws \yii\base\NotSupportedException
     */
    public function safeUp()
    {
        $this->addColumn(
            Provider::tableName(),
            'clientId',
            $this->char(Provider::CLIENT_ID_LENGTH)->notNull()
        );

        $this->addColumn(
            Provider::tableName(),
            'clientSecret',
            $this->char(Provider::CLIENT_SECRET_LENGTH)
        );

        if (!$this->providerInstances()) {
            return false;
        }

        return $this->deleteLegacyTables();
    }

    /**
     * @throws \yii\base\NotSupportedException
     */
    protected function providerInstances(): bool
    {
        $tableName = '{{%patron_provider_instances}}';

        $schema = $this->getDb()->getSchema();
        if ($schema->getTableSchema($schema->getRawTableName($tableName)) === null) {
            return true;
        }

        $providers = Provider::findAll([
            'enabled' => null
        ]);

        foreach ($providers as $provider) {
            if (!$query = (new Query())
                ->select([
                    'clientId',
                    'clientSecret',
                    'settings'
                ])
                ->from([$tableName])
                ->where([
                    'providerId' => $provider->id
                ])->one()
            ) {
                continue;
            }

            $provider->clientId = $query['clientId'];
            $provider->clientSecret = $query['clientSecret'];

            if ($settings = $query['settings']) {
                ObjectHelper::populate(
                    $provider->getSettings(),
                    Json::decodeIfJson($settings)
                );
            }

            $provider->save();
        }

        return true;
    }

    /**
     * @return bool
     */
    protected function deleteLegacyTables(): bool
    {
        $this->dropTableIfExists('{{%patron_provider_environments}}');
        $this->dropTableIfExists('{{%patron_token_environments}}');
        $this->dropTableIfExists('{{%patron_provider_instances}}');

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        return true;
    }
}
