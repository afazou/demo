<?php
/**
 *      [Gome Wap!] (C)2013-2023 Gome Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: template.php 2012-03-30 16:39:05Z lilixing $
 */
define('PHP_CLOSE_TAG', '?'.'>');
define('TEMPLATEID', '1');

function parse_template($tplfile,$objfile) 
{
    defined('TEMPLATE_COMPILE_PATH') or define('TEMPLATE_COMPILE_PATH', 'template_compile_path');

    $nest = 5;
    if(!$fp = @fopen($tplfile, 'r'))
    {
        dexit("Current template file '$tplfile' not found or have no access!");
    }

    $template = fread($fp, filesize($tplfile));
    fclose($fp);

    $var_regexp = "((\\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)(\[[a-zA-Z0-9_\-\.\"\'\[\]\$\x7f-\xff]+\])*)";
    $const_regexp = "([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)";
    
    $template = preg_replace("/([\n\r]+)\t+/s", "\\1", $template);
    $template = preg_replace("/\<\!\-\-\{(.+?)\}\-\-\>/s", "{\\1}", $template);
    $template = str_replace("{LF}", "<?=\"\\n\"?>", $template);
    $template = preg_replace("/\{(\\\$[a-zA-Z0-9_\[\]\'\"\$\.\x7f-\xff]+)\}/s", "<?=\\1;?>", $template);
    $template = preg_replace("/$var_regexp/es", "addquote('<?=\\1?>')", $template);
    $template = preg_replace("/\<\?\=\<\?\=$var_regexp\?\>;\?\>/es", "addquote('<?=\\1?>')", $template);
    $template = preg_replace("/\{\<\?\=\\\$([a-zA-Z0-9_\[\]\'\"\$\x7f-\xff]+)\?\>->([\(\):a-zA-Z0-9_\-\>\[\]\'\"\$\x7f-\xff]+)\}/s", "<?=\\\$\\1->\\2;?>", $template);
    $template = preg_replace("/[\n\r\t]*\{template\s+([a-z0-9_]+)\}[\n\r\t]*/is", "\n<? include template('\\1'); ?>\n", $template);
    $template = preg_replace("/[\n\r\t]*\{template\s+(.+?)\}[\n\r\t]*/is", "\n<? include template('\\1'); ?>\n", $template);
    $template = preg_replace("/[\n\r\t]*\{eval\s+(.+?)\}[\n\r\t]*/ies", "stripvtags('<? \\1; ?>','')", $template);
    $template = preg_replace("/[\n\r\t]*\{echo\s+(.+?)\}[\n\r\t]*/ies", "stripvtags('<? echo \\1;?>','')", $template);
    $template = preg_replace("/([\n\r\t]*)\{elseif\s+(.+?)\}([\n\r\t]*)/ies", "stripvtags('\\1<? } elseif(\\2) { ?>\\3','')", $template);
    $template = preg_replace("/([\n\r\t]*)\{else\}([\n\r\t]*)/is", "\\1<? } else { ?>\\2", $template);

    for($i = 0; $i < $nest; $i++) 
    {
        $template = preg_replace("/[\n\r\t]*\{loop\s+(\S+)\s+(\S+)\}[\n\r]*(.+?)[\n\r]*\{\/loop\}[\n\r\t]*/ies", "stripvtags('<? if(is_object(\\1) || is_array(\\1)) { foreach(\\1 as \\2) { ?>','\\3<? } } ?>')", $template);
        $template = preg_replace("/[\n\r\t]*\{loop\s+(\S+)\s+(\S+)\s+(\S+)\}[\n\r\t]*(.+?)[\n\r\t]*\{\/loop\}[\n\r\t]*/ies", "stripvtags('<? if(is_object(\\1) || is_array(\\1)) { foreach(\\1 as \\2 => \\3) { ?>','\\4<? } } ?>')", $template);
        $template = preg_replace("/([\n\r\t]*)\{if\s+(.+?)\}([\n\r]*)(.+?)([\n\r]*)\{\/if\}([\n\r\t]*)/ies", "stripvtags('\\1<? if(\\2) { ?>\\3','\\4\\5<? } ?>\\6')", $template);
    }

    $template = preg_replace("/\{$const_regexp\}/s", "<?=\\1?>", $template);
    $template = preg_replace("/ \?\>[\n\r]*\<\? /s", " ", $template);
    $template = str_replace("\$%", "\$", $template);
    $template = str_replace("<? exit; ?>", "", $template);

    if(!$fp = fopen($objfile, 'w')) 
    {
        dexit("Directory ".TEMPLATE_COMPILE_PATH." not found or have no access!");
    }

    flock($fp, 2);
    fwrite($fp, $template);
    fclose($fp);
}

function dexit($str)
{
    die($str);
}

function transamp($str)
{
    $str = str_replace('&', '&amp;', $str);
    $str = str_replace('&amp;amp;', '&amp;', $str);
    $str = str_replace('\"', '"', $str);
    return $str;
}

function addquote($var) 
{
    return str_replace("\\\"", "\"", preg_replace("/\[([a-zA-Z0-9_\-\.\x7f-\xff]+)\]/s", "['\\1']", $var));
}

function languagevar($var) 
{
    if(isset($GLOBALS['language'][$var])) 
    {
        return $GLOBALS['language'][$var];
    }
    else
    {
        return "!$var!";
    }
}

function stripvtags($expr, $statement)
{
    $expr = str_replace("\\\"", "\"", preg_replace("/\<\?\=(\\\$.+?)\?\>/s", "\\1", $expr));
    $statement = str_replace("\\\"", "\"", $statement);
    return $expr.$statement;
}

function stripscriptamp($s) 
{
    $s = str_replace('&amp;', '&', $s);
    return "<script src=\"$s\" type=\"text/javascript\"></script>";
}

function stripblock($var, $s) 
{
    $s = str_replace('\\"', '"', $s);
    $s = preg_replace("/<\?=\\\$(.+?)\?>/", "{\$\\1}", $s);
    preg_match_all("/<\?=(.+?)\?>/e", $s, $constary);
    $constadd = '';
    $constary[1] = array_unique($constary[1]);
    foreach($constary[1] as $const) 
    {
        $constadd .= '$__'.$const.' = '.$const.';';
    }
    $s = preg_replace("/<\?=(.+?)\?>/", "{\$__\\1}", $s);
    $s = str_replace('?>', "\n\$$var .= <<<EOF\n", $s);
    $s = str_replace('<?', "\nEOF;\n", $s);
    return "<?\n$constadd\$$var = <<<EOF\n".$s."\nEOF;\n?>";
}

function template($file, $templateid = 0) 
{
    /*默认的模板路径和编译路径*/
    $tp = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR ;
    $default_template_path = $tp . 'view' . DIRECTORY_SEPARATOR. 'default'.DIRECTORY_SEPARATOR;
    $default_template_compile_path = $tp . 'data' .DIRECTORY_SEPARATOR."compile".DIRECTORY_SEPARATOR;

    $template_path =  defined('TEMPLATE_PATH') ? TEMPLATE_PATH : $default_template_path;
    $template_compile_path  = defined('TEMPLATE_COMPILE_PATH') ? TEMPLATE_COMPILE_PATH : $default_template_compile_path;
    $templateid = $templateid ? $templateid : TEMPLATEID;

    $file_info = pathinfo($file);
    $path = '';
    if ($file_info['dirname'] != '.')
    {
        $path = $file_info['dirname'];
        $file   = $file_info['basename'];
    }
    $tpldir = $template_path . $path;
    $objdir = $template_compile_path .$path;
    if (!is_dir($objdir))
    {
        $dirarr = explode(DIRECTORY_SEPARATOR,$path);
        $dirInfo = $template_compile_path;
        foreach($dirarr as $k => $dir)
        {
            $dirInfo .=$dir  . DIRECTORY_SEPARATOR ;
            @mkdir($dirInfo);
        }
    }
    $tplfile = $tpldir . DIRECTORY_SEPARATOR.$file.'.html';
    $objfile = $objdir . DIRECTORY_SEPARATOR .$file.'.php';
    file_exists($tplfile) || dexit("");
    if(@filemtime($tplfile) > @filemtime($objfile))
    {
        require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'template.php';
        parse_template($tplfile,$objfile);
    }
    return $objfile;
}
?>
