<?php
/**
 * Tool Class
 *
 * @author Alfi Rizka
 * @copyright Atomic55
 */



class Atomic55_Tools
{
    //--------------------------------------
    /**
     * Fix magic quote
     * 
     * @see http://php.net/manual/en/security.magicquotes.php
     * @author Alfi Rizka
     * @param ARRAY $var           
     */
    public static function fix_magic_quote(&$var) {
        if(empty($var) === FALSE && is_array($var) === TRUE) {
            if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
                // magic quote = active
                $var = stripslashes_deep($var);
            } else {
                // php v.5.4 magic quote removed (php will always add slashes on GPC data)
                $var = stripslashes_deep($var);
            }
        }
        
        return $var;
    } 
    
    //--------------------------------------
    /**
     * Get contextual help
     * help file located inside <PLUGIN_DIR>/tpl/help/<$id>.html
     *
     * @param STRING $id            
     * @author Alfi Rizka
     */
    public static function help ($id)
    {
        $filename = Atomic55_Plugin::$path . 'help/' . $id . '.html';
        
        if (file_exists($filename) == FALSE) {
            return '<h5>Help File Not Found</h5><p>Create a html than save to <code>' . $filename . '</code></p>';
        }
        
        ob_start();
        include ($filename);
        
        return ob_get_clean();
    }
}