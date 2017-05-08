<?php

evIncBe('sys/php-gettext/gettext.inc');

$supported_locales = array('en_US','pl_PL');

function lngReset($locale){
	$encoding = ATS_HTML_ENCODING;
	// gettext setup
	T_setlocale(LC_MESSAGES, $locale);
	// Set the text domain as 'messages'
	$domain = 'messages';
	T_bindtextdomain($domain, ATS_LOCALE_DIR);
	T_bind_textdomain_codeset($domain, $encoding);
	T_textdomain($domain);	
}

$locale = ATS_DEFAULT_LOCALE;
lngReset($locale);

function lngTranslateStr($str){
	return __($str);
}

function lngTranslateAsoc1($array,$key){
	foreach($array as $k=>$v){
		$array[$k][$key]=lngTranslateStr($array[$k][$key]);
	}
	return $array;
}
?>