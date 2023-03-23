<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/ajax',
        __DIR__ . '/attachments',
        __DIR__ . '/careers',
        __DIR__ . '/js',
        __DIR__ . '/lib',
        __DIR__ . '/modules',
        __DIR__ . '/optional-updates',
        __DIR__ . '/rss',
        __DIR__ . '/scripts',
        __DIR__ . '/src',
        __DIR__ . '/test',
        __DIR__ . '/xml',
    ]);

    // register a single rule
    $rectorConfig->rule(InlineConstructorDefaultToPropertyRector::class);

    // define sets of rules
        $rectorConfig->sets([
            LevelSetList::UP_TO_PHP_72
        ]);
};
