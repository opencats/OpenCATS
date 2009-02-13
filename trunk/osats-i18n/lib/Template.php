<?php
/**
 * OSATS
 */

/**
 *	Template Library
 *	@package    CATS
 *	@subpackage Library
 */
class Template
{
    private $_templateFile;
    private $_filters = array();

    /**
     * Prints $string with all html special characters converted to &codes;.
     *
     * Ex: 'If x < 2 & x > 0, x = 1.' -> 'If x &lt; 2 &amp; x &gt; 0, x = 1.'.
     *
     * @param string input
     * @return void
     */
    public function _($string)
    {
        echo(htmlspecialchars($string));
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
}
