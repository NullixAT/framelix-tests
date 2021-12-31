<?php

use Framelix\Framelix\Config;
use Framelix\Framelix\Lang;
use Framelix\FramelixTests\TestCase;

final class LangTest extends TestCase
{
    public function tests(): void
    {
        Lang::$lang = "en";
        $this->assertSame(null, Lang::getLanguageByBrowserSettings());
        Config::set('languageFallback', 'de');
        Config::set('languagesSupported', ['en', 'de']);
        $this->assertSame("Yes", Lang::get('__framelix_yes__'));
        $this->assertSame("blub", Lang::get('blub'));
        $this->assertSame("__blub__", Lang::get('__blub__'));
        $this->assertSame("", Lang::get(''));
        Lang::$values['de']['__blub__'] = 'german';
        Lang::$values['en']['__blub__'] = '';
        $this->assertSame("german", Lang::get('__blub__'));
        $this->assertSame("1 minute", Lang::get('__framelix_time_minutes__', [1]));
        $this->assertSame("2 minutes", Lang::get('__framelix_time_minutes__', [2]));
        $this->assertSame("0 minutes", Lang::get('__framelix_time_minutes__', [0]));
        Lang::$values['en']['__test__'] = '{{0<2:lt|0<=2:lte|0>3:gt|0>=3:gte}}';
        $this->assertSame("1 lt", Lang::get('__test__', [1]));
        $this->assertSame("2 lte", Lang::get('__test__', [2]));
        $this->assertSame("4 gt", Lang::get('__test__', [4]));
        $this->assertSame("3 gte", Lang::get('__test__', [3]));
        $this->assertSame(null, Lang::getLanguageByBrowserSettings());
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = "en-US,de-DE";
        $this->assertSame('en', Lang::getLanguageByBrowserSettings());
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = "ru-RU";
        $this->assertSame(null, Lang::getLanguageByBrowserSettings());
        $this->assertSame(['en'], Lang::getSupportedLanguages());
        Config::set('languageMultiple', true);
        $this->assertSame(['en', 'de'], Lang::getSupportedLanguages());
        $this->assertSame(['de', 'en'], Lang::getAllModuleLanguages());
        $this->assertIsArray(Lang::getValuesForSupportedLanguages());
    }
}
