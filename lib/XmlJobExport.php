<?php
/**
 * CATS
 * XML Job Export Library
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 *
 *
 * The contents of this file are subject to the CATS Public License
 * Version 1.1a (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.catsone.com/.
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 *
 * The Original Code is "CATS Standard Edition".
 *
 * The Initial Developer of the Original Code is Cognizo Technologies, Inc.
 * Portions created by the Initial Developer are Copyright (C) 2005 - 2007
 * (or from the year in which this file was created to the year 2007) by
 * Cognizo Technologies, Inc. All Rights Reserved.
 *
 *
 * @package    CATS
 * @subpackage Library
 * @copyright Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 * @version    $Id: XmlJobExport.php 3705 2007-11-26 23:34:51Z will $
 */

/**
 *	XML Job Export Library
 *	@package    CATS
 *	@subpackage Library
 */
class XmlTemplate
{
    /* Prevent this class from being instantiated. */
    private function __construct() {}
    private function __clone() {}


    /**
     * Returns a list of websites and their parameters that receive XML
     * feeds for job submissions.
     *
     * @return array
     */
    public static function getTemplates()
    {
        $templates = array();
        $db = DatabaseConnection::getInstance();

        $sql = sprintf(
            "SELECT
                xml_feed_id,
                name,
                description,
                website,
                post_url,
                success_string,
                xml_template_name
             FROM
                xml_feeds"
        );

        return $db->getAllAssoc($sql);
    }

    /**
     * Returns specific information about a template, which is a website
     * that receives XML feeds for job submissions.
     *
     * @param string Name of the template which corresponds to xml_template_name
     *               in the xml_feeds table.
     * @return unknown
     */
    public static function getTemplate($templateName)
    {
        $db = DatabaseConnection::getInstance();

        $sql = sprintf(
            "SELECT
                xml_feed_id,
                name,
                description,
                website,
                post_url,
                success_string,
                xml_template_name
             FROM xml_feeds
             WHERE xml_template_name = %s",
            $db->makeQueryString($templateName)
        );

        return $db->getAssoc($sql);
    }

    /**
     * Submits all applicable job feeds in all available formats to the
     * asynchronous queue processor which begins submitting them to the
     * appropriate websites.
     *
     * @param int ID of the site to submit
     */
    public static function submitXMLFeeds($siteID)
    {
        if (!eval(Hooks::get('XML_SUBMIT_FEEDS_TO_QUEUE'))) return;
    }

    /**
     * Loads the XML formatted template file from the XML_EXPORT_TEMPLATES_DIR
     * directory, which should have a file extension "xtpl". This file is to
     * be written in XML to whatever format the receiving website requires.
     *
     * Tags are inserted in defined sections. See the example.xtpl file in the
     * XML_EXPORT_TEMPLATES_DIR path for an example and a list of the tags
     * and sections available.
     *
     * @param string file name (not including .xtpl extension or path) of the template
     * @return array of the sections and their content as XML
     */
    public static function loadTemplate($templateName)
    {
        $templateSections = array();

        // Read the template file into a string
        $rawTemplate = file_get_contents(
            sprintf('%s/%s.xtpl', XML_EXPORT_TEMPLATES_DIR, $templateName)
        );

        // Break the template into lines
        $tplLines = split("\n", $rawTemplate);

        // Browse the lines looking for section headers like ">>SECTION_HEADER_NAME"
        for ( $i=0; $i<count($tplLines); $i++ )
        {
            // Strip whitespace/line returns off the line
            $tplLine = trim($tplLines[$i]);

            // Ignore comments
            if (!strcmp(substr($tplLine, 0, 1), "#"))
            {
                continue;
            }

            // New format sections begin like ">>FORMAT_SECTION_NAME"
            if (preg_match("/\>\>([a-zA-Z0-9_-]+)/i", $tplLine, $matches) )
            {
                // $matches[1] is the tag's name
                $templateTag = trim($matches[1]);
                if (strlen($templateTag) > 0)
                {
                    // Start a new, blank section for this tag
                    $templateSections[$templateTag] = '';

                    // Add the following lines to the tag's body until a closing
                    //   "<<SECTION_HEADER_NAME" is found.
                    for ( $i2=$i+1; $i2<count($tplLines); $i2++ )
                    {
                        $tplBodyLine = trim($tplLines[$i2]);

                        if( !strcasecmp($tplBodyLine, sprintf('<<%s', $templateTag)) )
                        {
                            break;
                        }
                        $templateSections[$templateTag] .= $tplBodyLine . "\n";
                    }
                }
            }
        }
        return $templateSections;
    }

    /**
     * Loads all applicable tags for replacement from the file contents of
     * a template (xtpl) file.
     *
     * @param string contents of the file to scan
     * @return array of the tags found
     */
    public static function loadTemplateTags($template)
    {
        $tags = array();
        for ( $i=0; $i<strlen($template)-4; $i++ )
        {
            if (!strcmp(substr($template, $i, 2), '$['))
            {
                $x = strpos( $template, ']', $i+2 );
                if ($x !== false)
                {
                    $tag = substr( $template, $i+2, $x-$i-2 );
                    if (!in_array($tag, $tags))
                    {
                        $tags[] = $tag;
                    }
                }
            }
        }
        return $tags;
    }

    /**
     * Extension function to str_replace that replaces all xtpl file tags
     * from raw string data.
     *
     * @param string Name of the tag without formatting
     * @param string Value that should replace the tag
     * @param string String contents of the template (xtpl) file
     * @return string The new string with the replacements made.
     */
    public static function replaceTemplateTags($tag, $replace, $template)
    {
        return str_replace(
            sprintf('$[%s]', $tag),
            htmlspecialchars($replace),
            $template
        );
    }
}

?>
