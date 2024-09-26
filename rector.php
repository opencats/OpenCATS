<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    // scan paths
    $rectorConfig->paths([
        '/var/www/html/OpenCATS',
    ]);

    // skip rules
    $rectorConfig->skip([
        // paths
        '/var/www/html/OpenCATS/vendor',
        //        '/var/www/html/OpenCATS/ci',
        //        '/var/www/html/OpenCATS/db',
        //        'var/www/html/OpenCATS/docker',
        //        'var/www/html/OpenCATS/test',
        //        'var/www/html/OpenCATS/src',
        //        '/var/www/html/OpenCATS/lib/artichow',
        //        '/var/www/html/OpenCATS/lib/fpdf',
        //        '/var/www/html/OpenCATS/lib/simpletest',
        //        '/var/www/html/OpenCATS/lib/sphinx',

    ]);

    // rule sets
    $rectorConfig->sets([
        //        SetList::EARLY_RETURN,
        //        SetList::DEAD_CODE,
        //        SetList::CODE_QUALITY,
        //        SetList::CODING_STYLE,
        LevelSetList::UP_TO_PHP_82,
    ]);
};
