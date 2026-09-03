<?php
/*
 * OpenCATS
 *
 * Portions Copyright (C) 2005-2007 Cognizo Technologies, Inc.
 * Originally released as part of CATS Standard Edition under the
 * CATS Public License 1.1a.
 *
 * See LICENSE.md.
 */

/**
 *	Template Library
 *	@package    CATS
 *	@subpackage Library
 */
#[AllowDynamicProperties]
class Template
{
    private $_templateFile;
    private $_filters = array();

    /**
     * Normalizes values before escaping for template output.
     *
     * @param mixed $value
     * @return string
     */
    private static function normalizeEscapedValue($value)
    {
        if (!is_scalar($value) && !(is_object($value) && method_exists($value, '__toString')))
        {
            return '';
        }

        return (string) $value;
    }

    /**
     * Escapes value for HTML text output.
     *
     * @param mixed $value
     * @return string
     */
    public static function escapeHtml($value)
    {
        return htmlspecialchars(
            self::normalizeEscapedValue($value),
            ENT_QUOTES | ENT_SUBSTITUTE,
            HTML_ENCODING
        );
    }

    /**
     * Escapes value for HTML attribute output.
     *
     * @param mixed $value
     * @return string
     */
    public static function escapeAttr($value)
    {
        return self::escapeHtml($value);
    }

    /**
     * Escapes value for URL attribute output and blocks dangerous schemes.
     *
     * @param mixed $value
     * @return string
     */
    public static function escapeUrl($value)
    {
        $url = self::normalizeEscapedValue($value);
        if ($url === '')
        {
            return '';
        }

        $normalizedUrl = html_entity_decode($url, ENT_QUOTES, HTML_ENCODING);
        $normalizedUrl = strtolower($normalizedUrl);
        $normalizedUrl = preg_replace('/[\x00-\x20\x7f]+/', '', $normalizedUrl);

        if (preg_match('/^([a-z][a-z0-9+\-.]*):/', $normalizedUrl, $matches))
        {
            if (in_array($matches[1], array('javascript', 'vbscript', 'data'), true))
            {
                return '';
            }
        }

        return self::escapeAttr($url);
    }

    /**
     * Escapes value for JavaScript literal output.
     *
     * @param mixed $value
     * @return string
     */
    public static function escapeJs($value)
    {
        $encoded = json_encode(
            self::normalizeEscapedValue($value),
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
        );
        if ($encoded === false)
        {
            return '""';
        }

        return $encoded;
    }

    /**
     * Escapes value for JavaScript string literal output inside HTML attributes.
     *
     * @param mixed $value
     * @return string
     */
    public static function escapeJsAttr($value)
    {
        return self::escapeAttr(self::escapeJs($value));
    }

    /**
     * Prints $string with all html special characters converted to &codes;.
     *
     * Ex: 'If x < 2 & x > 0, x = 1.' -> 'If x &lt; 2 &amp; x &gt; 0, x = 1.'.
     *
     * @param mixed $string
     * @return void
     */
    public function _($string)
    {
        echo(self::escapeHtml($string));
    }

    /**
     * Assigns the specified property value to the specified property name
     * for access within the template.
     *
     * @param string property name
     * @param mixed property value
     * @return void
     */
    public function assign($propertyName, $propertyValue)
    {
        $this->$propertyName = $propertyValue;
    }

    /**
     * Assigns the specified property value to the specified property name,
     * by reference, for access within the template.
     *
     * @param string property name
     * @param mixed property value
     * @return void
     */
    public function assignByReference($propertyName, &$propertyValue)
    {
        $this->$propertyName =& $propertyValue;
    }

    /**
     *  TODO: Document me.
     */
    public function addFilter($code)
    {
        $this->_filters[] = $code;
    }

    /**
     * Evaluates a template file. All assignments (see the Template::assign()
     * and Template::assignByReference() methods) must be made before calling
     * this method. The template filename is relative to index.php.
     *
     * @param string template filename
     * @return void
     */
    public function display($template)
    {
        /* File existence checking. */
        $file = realpath('./' . $template);
        if (!$file)
        {
            echo 'Template error: File \'', $template, '\' not found.', "\n\n";
            return;
        }

        $this->_templateFile = $file;

        /* We don't want any variable name conflicts here. */
        unset($file, $template);

        /* Include the template, with output buffering on, and echo it. */
        ob_start();
        include($this->_templateFile);
        $html = ob_get_clean();

        if (strpos($html, '<!-- NOSPACEFILTER -->') === false && strpos($html, 'textarea') === false)
        {
            $html = preg_replace('/^\s+/m', '', $html);
        }

        foreach ($this->_filters as $filter)
        {
            eval($filter);
        }

        echo($html);
    }

    /**
     * Returns access level of logged in user for securedObject
     * Intended to be used in tpl classes to check if user has acces to particular part of page and if shall be generated or not
     */
    protected function getUserAccessLevel($securedObjectName)
    {
        return $_SESSION['CATS']->getAccessLevel($securedObjectName);
    }
}

?>
