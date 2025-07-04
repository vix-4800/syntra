<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Vix\Syntra\Commands\Rector\CanHelpersRector;
use Vix\Syntra\Commands\Rector\ConvertAccessChainRector;
use Vix\Syntra\Commands\Rector\DeleteAllShortcutRector;
use Vix\Syntra\Commands\Rector\FindAllIdShortcutRector;
use Vix\Syntra\Commands\Rector\FindOneFindAllShortcutRector;
use Vix\Syntra\Commands\Rector\FindOneIdShortcutRector;
use Vix\Syntra\Commands\Rector\UpdateAllShortcutRector;
use Vix\Syntra\Commands\Rector\UserFindOneToIdentityRector;

return RectorConfig::configure()
    ->withRootFiles()
    ->withSkip([
        'vendor'
    ])
    ->withRules([
        // Yii Specific
        CanHelpersRector::class, // Replaces can/!can chains with canAny, canAll, cannotAny, or cannotAll
        FindOneFindAllShortcutRector::class, // Converts Model::find()->where([...])->one() or all() to Model::findOne(...) / findAll(...)
        FindOneIdShortcutRector::class, // Converts Model::findOne(['id' => $id]) to Model::findOne($id)
        FindAllIdShortcutRector::class, // Replaces Model::findAll([\'id\' => $id]) with Model::findAll($id)
        UpdateAllShortcutRector::class, // Replaces chains like find()->where([...])->update([...]) with updateAll([...], [...])
        DeleteAllShortcutRector::class, // Replaces chains like find()->where([...])->delete() with deleteAll([...])
        ConvertAccessChainRector::class, // Replaces identity->hasAccessChain()/hasNoAccessChain() with user->canAny()/cannotAny() for Yii apps
        UserFindOneToIdentityRector::class, // Replaces redundant User::findOne(...) lookups for current user with Yii::$app->user->identity

        // Laravel Specific
    ]);
