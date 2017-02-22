<?php
/**
 * Theme Library
 * @package OpenCATS
 * @subpackage Library
 * @copyright (C) OpenCats
 * @author Bloafer
 *
 * Theme library for enhancing OpenCATS, allowing end users to create themes without touching the core files
 * theme functions are provided as global functions to give a WordPress feel to themes, debugging is based on
 * Drupal style debugging, to apply theme debug just uncomment the THEME_DEBUG in the config.php file
 */

include_once("./config.php");

if(!defined("THEME_DEBUG")){
    define("THEME_DEBUG", false);
}
if(!defined("THEME")){
    define("THEME", "opencats2016");
}
// Include functions file first if it exists
theme("functions", array(), false, array());
// Filters after function file
run_filter("oc_enqueue_styles");
run_filter("oc_enqueue_scripts");

function get_current_theme(){
    return THEME;
}
function get_header($printOutput=true){
    return theme("header", array(), $printOutput, array());
}
function get_footer($printOutput=true){
    return theme("footer", array(), $printOutput, array());
}
function theme($themeFiles, $variables=array(), $printOutput=true, $fallback=array("index")){
    if(!is_array($themeFiles)){
        $themeFiles = array($themeFiles);
    }
    // Always add fallbacks last
    $themeFiles = array_merge($themeFiles, $fallback);

    if(THEME_DEBUG){
        print PHP_EOL . "<!-- THEME DEBUG for '" . get_current_theme() . "' -->" . PHP_EOL;
        if(count($themeFiles)==1){
            print "<!-- CALL: theme('" . implode("', '", $themeFiles) . "') -->" . PHP_EOL;
        }else{
            print "<!-- CALL: theme(array('" . implode("', '", $themeFiles) . "')) -->" . PHP_EOL;
        }
        print "<!-- " . PHP_EOL . "File name suggestions:" . PHP_EOL;
    }
    $using = false;
    foreach($themeFiles as $themeFile){
        $fileCheck = get_template_directory($themeFile . ".php");
        if(!$using && file_exists($fileCheck)){
            $using = $fileCheck;
        }
        if(THEME_DEBUG){
            print " " . ($using==$fileCheck?"*":"x") . " " . $fileCheck . PHP_EOL;
        }
    }
    if(!isset($variables["content"])){
        $variables["content"] = "No content provided";
    }
    if(isset($variables["active"])){
        oc_set_active($variables["active"]);
    }

    extract($variables);
    if(THEME_DEBUG){
        print PHP_EOL . "Available variables:" . PHP_EOL;
        if(!empty(array_keys($variables))){
            print " $" . implode(PHP_EOL . " $", array_keys($variables)) . PHP_EOL;
        }else{
            print " None" . PHP_EOL;
        }
        print "-->" . PHP_EOL;
    }
    $output = '';
    if($using){
        if(THEME_DEBUG){
            if($printOutput){
                print "<!-- Begin output from '" . $using . "' -->" . PHP_EOL;
            }else{
                print "<!-- File included '" . $using . "' -->" . PHP_EOL;
            }
        }
        ob_start();
        include $using;
        $output = ob_get_contents();
        ob_end_clean();
        if($printOutput){
            print $output;
        }
    }else{
        if(THEME_DEBUG){
                print "<!-- No file used -->" . PHP_EOL;
        }
    }
    if(THEME_DEBUG && $using && $printOutput){
        print "<!-- End output from '" . $using . "' -->" . PHP_EOL;
    }
    return $output;
}
function get_template_directory_uri($append=false){
    $fullPath = get_template_directory($append);
    $basePath = realpath("." . DIRECTORY_SEPARATOR);
    return str_replace($basePath, '', $fullPath);
}
function get_template_directory($append=false){
    $ds = DIRECTORY_SEPARATOR;
    $themePath = realpath("." . $ds . "theme" . $ds . get_current_theme());
    if($themePath){
        if($append){
            $themePath .= $ds . $append;
        }
    }else{
        // Theme cannot be found
    }

    return $themePath;
}
function oc_footer(){
    oc_render_scripts(true);
}
function oc_head(){
    oc_render_title();
    oc_render_styles();
    oc_render_scripts();

}
function oc_render_title(){
    print '<title>OpenCATS - ' . oc_get_title() . '</title>';
}
function oc_render_styles(){
    global $oc_style;
    if(is_array($oc_style)){
        foreach($oc_style as $styleDef){
            print '<link href="' . $styleDef["src"] . ($styleDef["ver"]?'?v=' . $styleDef["ver"]:'') . '" media="' . $styleDef["media"] . '" rel="stylesheet" type="text/css" />' . PHP_EOL;
        }
    }
}
function oc_render_scripts($inFooter=false){
    global $oc_script;
    if(is_array($oc_script)){
        foreach($oc_script as $scriptDef){
            if($inFooter==$scriptDef["in_footer"]){
                print '<script type="text/javascript" src="' . $scriptDef["src"] . ($scriptDef["ver"]?'?v=' . $scriptDef["ver"]:'') . '"></script>' . PHP_EOL;
            }
        }
    }
}
function oc_set_title($title){
    global $oc_title;
    $oc_title = $title;
    return $oc_title;
}
function oc_get_title(){
    global $oc_title;
    return $oc_title;
}
function oc_set_active($active){
    global $oc_active;
    $oc_active = $active;
    return $oc_active;
}
function oc_get_active(){
    global $oc_active;
    return $oc_active;
}
function oc_enqueue_style($handle, $src='', $deps=array(), $ver=false, $media='all'){
    global $oc_style;
    if(!isset($oc_style[$handle])){
        $oc_style[$handle] = array(
            'src'   => $src,
            'deps'  => $deps,
            'ver'   => $ver,
            'media' => $media,
        );
    }
    return true;
}
function oc_enqueue_script($handle, $src='', $deps=array(), $ver=false, $in_footer=false){
    global $oc_script;
    if(!isset($oc_script[$handle])){
        $oc_script[$handle] = array(
            'src'       => $src,
            'deps'      => $deps,
            'ver'       => $ver,
            'in_footer' => $in_footer,
        );
    }
    return true;
}
function add_filter($tag, $functionToAdd, $priority = 10, $accepted_args = 1){
    global $oc_filter;
    if(!isset($oc_filter[$tag])){
        $oc_filter[$tag] = array();
    }
    $oc_filter[$tag][$priority][] = $functionToAdd;
    return true;
}
function run_filter($tag){
    global $oc_filter;
    if(isset($oc_filter[$tag])){
        foreach($oc_filter[$tag] as $priority => $arrayOfFunctions){
            foreach($arrayOfFunctions as $function){
                $value = call_user_func_array($function, array());
            }
        }
    }
}
function oc_version(){
    $build    = $_SESSION['CATS']->getCachedBuild();
    if($build > 0){
        $buildString = ' build ' . $build;
    }else{
        $buildString = '';
    }
    return 'CATS Version ' . CATS_VERSION . $buildString;
}
function oc_response_time(){
    $loadTime = $_SESSION['CATS']->getExecutionTime();
    return 'Server Response Time: ' . $loadTime . ' seconds.';
}
function oc_copyright(){
    return COPYRIGHT_HTML;
}