<?php

use PHPUnit\Framework\TestCase;

if (! defined('LEGACY_ROOT')) {
    define('LEGACY_ROOT', '.');
}

include_once(LEGACY_ROOT . '/lib/BrowserDetection.php');

class BrowserDetectionTest extends TestCase
{
    /* See http://www.useragentstring.com/ for updating. */
    public function testDetect()
    {
        // FIXME: Add more browsers!
        $intendedMatches = [
            [
                '',
                [
                    'name' => 'Masked',
                    'version' => '',
                ],
                'Detected masked user agent properly.',
            ],
            [
                ' ',
                [
                    'name' => 'Masked',
                    'version' => '',
                ],
                'Detected masked user agent properly.',
            ],
            [
                'I don\'t exist!',
                [
                    'name' => 'Unknown',
                    'version' => '',
                ],
                'Detected an unknown user agent properly.',
            ],
            [
                'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1',
                [
                    'name' => 'Firefox',
                    'version' => '2.0.0.1',
                ],
                'Detected Firefox 2.0.0.1.',
            ],
            [
                'Mozilla/5.0 (Windows; U; Windows NT 6.0; fi) AppleWebKit/522.12.1 (KHTML, like Gecko) Version/3.0.1 Safari/522.12.2',
                [
                    'name' => 'Safari',
                    'version' => '3.0.1',
                ],
                'Detected Safari 3.0.1.',
            ],
            [
                'Mozilla/5.0 (Macintosh; U; PPC Mac OS X; tr-tr) AppleWebKit/418 (KHTML, like Gecko) Safari/417.9.3',
                [
                    'name' => 'Safari',
                    'version' => '2.0.3',
                ],
                'Detected Safari 2.0.3.',
            ],
            [
                'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.1.4322; .NET CLR 2.0.50727)',
                [
                    'name' => 'Internet Explorer',
                    'version' => '7.0',
                ],
                'Detected Internet Explorer 7.0.5730.11.',
            ],
            [
                'Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.8b2) Gecko/20050702)',
                [
                    'name' => 'Mozilla',
                    'version' => '1.8b',
                ],
                'Detected Mozilla rv:1.8b.',
            ],
            [
                'Mozilla/5.0 (compatible; Konqueror/3.5; Linux) KHTML/3.5.3 (like Gecko) Kubuntu 6.06 Dapper',
                [
                    'name' => 'Konqueror',
                    'version' => '3.5',
                ],
                'Detected Konqueror 3.5.',
            ],
            [
                'Opera/9.02 (Windows NT 5.1; U; en)',
                [
                    'name' => 'Opera',
                    'version' => '9.02',
                ],
                'Detected Opera 9.02.',
            ],
            [
                'Mozilla/5.0 (compatible; iCab 3.0.2; Macintosh; U; PPC Mac OS)',
                [
                    'name' => 'iCab',
                    'version' => '3.0.2',
                ],
                'Detected iCab 3.0.2.',
            ],
            [
                'iCab/2.9.1 (Macintosh; U; PPC)',
                [
                    'name' => 'iCab',
                    'version' => '2.9.1',
                ],
                'Detected iCab 2.9.1.',
            ],
            [
                'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.0.9) Gecko/20061211 SeaMonkey/1.0.7',
                [
                    'name' => 'SeaMonkey',
                    'version' => '1.0.7',
                ],
                'Detected SeaMonkey 1.0.7.',
            ],
            [
                'Mozilla/4.0 (compatible; MSIE 7.0; America Online Browser 1.1; rev1.5; Windows NT 5.1; .NET CLR 1.1.4322; .NET CLR 2.0.50727)',
                [
                    'name' => 'America Online Browser',
                    'version' => '1.1',
                ],
                'Detected America Online Browser 1.1.',
            ],
            [
                'Mozilla/4.0 (compatible; MSIE 6.0; AOL 9.0; Windows NT 5.1; SV1; FreeprodTB; FunWebProducts; .NET CLR 1.1.4322; .NET CLR 2.0.50727)',
                [
                    'name' => 'AOL',
                    'version' => '9.0',
                ],
                'Detected AOL 9.0.',
            ],
            [
                'Mozilla/5.0 (Macintosh; U; Intel Mac OS X; en-US; rv:1.8.0.7) Gecko/20060911 Camino/1.0.3',
                [
                    'name' => 'Camino',
                    'version' => '1.0.3',
                ],
                'Detected Camino 1.0.3.',
            ],
            [
                'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
                [
                    'name' => 'Googlebot',
                    'version' => '2.1',
                ],
                'Detected Googlebot 2.1.',
            ],
            [
                'Mozilla/5.0 (compatible; Yahoo! Slurp; http://help.yahoo.com/help/us/ysearch/slurp)',
                [
                    'name' => 'Yahoo Crawler',
                    'version' => '',
                ],
                'Detected Yahoo Crawler.',
            ],
            [
                'Lynx/2.8.5rel.5 libwww-FM/2.14 SSL-MM/1.4.1 OpenSSL/0.9.8d',
                [
                    'name' => 'Lynx',
                    'version' => '2.8.5',
                ],
                'Detected Lynx 2.8.5.',
            ],
            [
                'Links (0.99pre14; CYGWIN_NT-5.1 1.5.22(0.156/4/2) i686; 80x25)',
                [
                    'name' => 'Links',
                    'version' => '0.99pre14',
                ],
                'Detected Links 0.99pre14.',
            ],
            [
                'curl/7.15.4 (i686-pc-cygwin) libcurl/7.15.4 OpenSSL/0.9.8d zlib/1.2.3',
                [
                    'name' => 'cURL',
                    'version' => '7.15.4',
                ],
                'Detected cURL 7.15.4.',
            ],
            [
                'Wget/1.10.2',
                [
                    'name' => 'Wget',
                    'version' => '1.10.2',
                ],
                'Detected Wget 1.10.2.',
            ],
            [
                'W3C_Validator/1.432.2.5',
                [
                    'name' => 'W3C Validator',
                    'version' => '1.432.2.5',
                ],
                'Detected W3C Validator 1.432.2.5.',
            ],
            [
                'W3C-checklink/4.2.1 [4.21] libwww-perl/5.803',
                [
                    'name' => 'W3C Link Checker',
                    'version' => '4.2.1',
                ],
                'Detected W3C Link Checker 4.2.1.',
            ],
            [
                'Jigsaw/2.2.5 W3C_CSS_Validator_JFouffa/2.0',
                [
                    'name' => 'W3C CSS Validator',
                    'version' => '2.0',
                ],
                'Detected W3C CSS Validator 2.0.',
            ],
        ];

        foreach ($intendedMatches as $intendedMatch) {
            $this->assertSame(
                BrowserDetection::detect($intendedMatch[0]),
                $intendedMatch[1],
                ltrim($intendedMatch[2] . ' %s')
            );
        }
    }
}
