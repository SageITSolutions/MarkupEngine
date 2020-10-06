<?php

    /**
     * @author Daniel S. Davis (daniel.davis@sageitsolutions.net)
     * Special Thanks to Oliver Lillie (aka buggedcom) <publicmail@buggedcom.co.uk> for his contribution to CustomTag 1.0.0
     * Release found not working as released under GitHUB with PHP 7.  
     *
     * @license BSD
     * @copyright Copyright (c) 2008 Oliver Lillie <http://www.buggedcom.co.uk>
     * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation
     * files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy,
     * modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software
     * is furnished to do so, subject to the following conditions:  The above copyright notice and this permission notice shall be
     * included in all copies or substantial portions of the Software.
     *
     * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
     * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
     * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE,
     * ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
     *
     * @package MarkupEngine
     * @version 1.0.0
     */
    
    namespace MarkupEngine;

    require_once 'CustomMarkup.php';

    class MarkupEngine
    {
        
        public $version                 = '2.0.0';
        private static $_instance       = 0;
        
        /**
         * Holds the options array
         * @access private
         * @var array
         */
        private $_options               = [
            'parse_on_shutdown'         => false, // Creates a register_shutdown_function to process buffered output (requires use_buffer)
            'use_buffer'                => false, // Use Buffer instead of HTML
            'echo_output'               => false, // Echo compacted (processed) HTML
            'tag_callback_prefix'       => 'ct_',  //
            'tag_global_callback'       => false, // All Callbacks go through provided callback [$functionname,$tagData,$buffer]
            'tag_directory'             => false, // Location for tag extensions
            'template_directory'        => false, // Location for tag extensions
            'missing_tags_error_mode'   => 'THROW_EXCEPTION',   // The error mode for outputting errors
            'sniff_for_buried_tags'     => false, // recursive search for tags
            'cache_tags'                => false, // cache for improved performance (requires cache_directory)
            'cache_directory'           => false, // Location for cached tags
            'custom_cache_tag_class'    => false, // override to manipulate tag cache (include methods getCache and cache)
            'hash_tags'                 => false, // defines if inner content #{varname} requires matching values and added as array
        ];
        
        const ERROR_EXCEPTION               = 'THROW_EXCEPTION';    // throws and exception for catching.
        const ERROR_SILENT                  = 'ERROR_SILENT';       // returns empty data if a tag or error occurs
        const ERROR_ECHO                    = 'ERROR_ECHO';         // returns an error string
        
        public static $name                 = 'MarkupEngine';
        public static $nocache_tags         = [];
        private static $_required           = [];
        private static $_tags_to_collect    = [];
        private $_collections               = [];
        private $_registered                = [];
        private $_buffer_in_use             = false;
        public static $tag_collections      = [];
        private static $_tag_order          = [];
        protected $_searchtags              = [];
        protected $_searchReg               = "";
        
        public static $tag_directory_base   = false; 
        public static $template_directory_base = false;
        public static $error_mode           = false;
        
        /**
         * Initialize MarkupEngine
         *
         * @param array $options to override existing settings
         */
        function __construct($options=[]) {
            $this->_options['tag_directory']        = dirname(__FILE__).DIRECTORY_SEPARATOR.'tags'.DIRECTORY_SEPARATOR;
            $this->_options['template_directory']   = dirname(__FILE__).DIRECTORY_SEPARATOR.'tags'.DIRECTORY_SEPARATOR;
            if($options && is_array($options)){
                $this->_options = array_merge($this->_options, $options);
            }
            $this->setdefaults();
            $this->autoloadClasses();        
        }

        /**
         * Internal Autoloader for Tag Classes
         *
         * @return void
         */
        protected function autoloadClasses () {
            spl_autoload_register(function ($class){
                $namespace = __NAMESPACE__;
                $class = str_replace($namespace.'\\', '', $class);
                include $this->_options['tag_directory'].strtolower($class).".php";
            });
        }
        
        /**
         * Process Default Options and Parse to Static Variables
         *
         * @return void
         */
        public function setdefaults(){
            if($this->_options['parse_on_shutdown']){
                $this->_options['use_buffer'] = true;
                $this->_options['echo_output'] = true;
            }
            self::$template_directory_base = $this->_options['template_directory'];
            self::$tag_directory_base = $this->_options['tag_directory'];
            self::$error_mode = $this->_options['missing_tags_error_mode'];

            if($this->_options['custom_cache_tag_class'] && !class_exists($this->_options['custom_cache_tag_class'])) {
                $this->_options['cache_tags'] = false;
            }

            if($this->_options['tag_directory']){
                $searchRegex = "";
                foreach(glob($this->_options['tag_directory'].'*.php') as $file) {
                    if(is_file($file)){
                        $filename = str_replace($this->_options['tag_directory'],"",$file);
                        $filename = strtolower(str_replace(".php","",$filename));
                        $this->_searchtags[] = $filename;
                        if($searchRegex != "") $searchRegex .= "|";
                        $searchRegex .= '<'.$filename;
                    }
                }
                $this->_searchReg = $searchRegex;
            }

            if($this->_options['cache_tags'])
            {
                if(!$this->_options['custom_cache_tag_class'])
                {
                    if(!$this->_options['cache_directory'])
                    {
                        $this->_options['cache_directory'] = dirname(__FILE__).DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR;
                    }
                    else
                    {
                        if(!is_dir($this->_options['cache_directory']) || !is_writable($this->_options['cache_directory']))
                        {
                            $this->_options['cache_tags'] = false;
                        }
                    }
                }
                else
                {
                    if(!class_exists($this->_options['custom_cache_tag_class']))
                    {
                        $this->_options['cache_tags'] = false;
                    }
                }
            }

            if($this->_options['use_buffer'] === true)
            {
                ob_start();
            }
            if($this->_options['parse_on_shutdown'] === true)
            {
                register_shutdown_function(array(&$this, 'parse'));
            }
        }
        
        /**
         * Parses the source for any custom tags.
         * @access public
         * @param mixed|boolean|string $source If false then it will capture the output buffer, otherwise if a string
         *  it will use this value to search for custom tags.
         * @param boolean $parse_collections If true then any collected tags will be parsed after the tags are parsed.
         * @return string The parsed $source value.
         */
        public function parse($source=false, $parse_collections=false, $_internal_loop=false) {

            self::$_instance += 1;              // increment the parse count so it has unique identifiers
            if($source === false) {             // capture the source from the buffer
                $source = ob_get_clean();
                $this->_buffer_in_use = true;
                $parse_collections = true;
            }
            $tags = $this->processTags($source);    // Scrub for all tags
            if(count($tags) > 0) {
                $output = $this->renderTags($tags); // display output for all tags
                if($this->_options['echo_output'] === true) {
                    echo $output;
                }
                return $output;
            }
            return $source;
        }
        
        /**
         * Processes a tag by loading
         * @access private
         * @param array $tag The tag to parse.
         * @return string The content of the tag.
         */
        private function renderTag($tag) {   
            if($tag->disabled ?? false) return ''; // return empty for disabled tag
            
            $tag_data = false;
            $caching_tag = $tag->cache ?? true;
            if($this->_options['cache_tags'] && $caching_tag) { // Cache
                if($this->_options['custom_cache_tag_class']) {
                    $tag_data = call_user_func_array(array($this->_options['custom_cache_tag_class'], 'getCache'), array($tag));
                }
                else {
                    $cache_file = $this->_options['cache_directory'].md5(serialize($tag));
                    if(is_file($cache_file) === true) {
                        $tag_data = file_get_contents($cache_file);
                    }
                }
                if($tag_data) {
                    $tag['cached'] = true;
                    return $tag_data;
                }
            }    
            $tag_data = $tag->render();

            if(empty($tag_data) === false) {    // Find all buried tags
                if($this->_options['sniff_for_buried_tags'] && strpos($tag_data, '<'.'ct:') !== false) {
                    // we have the possibility of buried tags so lets parse
                    // but first make sure the output isn't echoed out
                    $old_echo_value = $this->_options['echo_output'];
                    $this->_options['echo_output'] = false; 
                    // parse the tag_data 
                    $tag_data = $this->parse($tag_data, false, true);
                    // restore the echo_output value back to what it was originally
                    $this->_options['echo_output'] = $old_echo_value;
                }
                
                if($this->_options['cache_tags'] === true && $caching_tag === true) {
                    if($this->_options['custom_cache_tag_class'] !== false) {
                        call_user_func_array(array($this->_options['custom_cache_tag_class'], 'cache'), array($tag, $tag_data));
                    }
                    else {
                        file_put_contents($this->_options['cache_directory'].md5(serialize($tag)), $tag_data, LOCK_EX);
                    }
                }
            }
            return $tag_data;
        }
        
        /**
         * Produces an error.
         * @access public
         * @param string $tag The name of the tag producing an error.
         * @param string $message The message of the error.
         * @return mixed|error|string Either a string or thrown error is returned dependent 
         *  on the 'missing_tags_error_mode' option.
         */
        public static function throwError($tag, $message) {
            if(self::$error_mode === self::ERROR_EXCEPTION && !self::buffer_in_use) {
                throw new MarkupEngineException('<strong>'.$tag.'</strong> '.$message.'.');
            }
            else if(self::$error_mode !== self::ERROR_SILENT) {
                return '<strong>['.self::$name.' Error]</strong>: '.ucfirst($tag).' Tag - '.$message.'<br />';
            }
            return '';
        }
        
        /**
         * Loops and parses the found custom tags.
         * @access private
         * @param array $tags An array of found custom tag data.
         * @return mixed|string|boolean Returns false if there are no tags, string otherwise.
         */
        private function renderTags($tags){
            if(count($tags) > 0){   
                foreach($tags as $key=>$tag) { 
                    // Loop through Tags
                    if($tag->attributes->delayed ?? false) continue;
                    if(($has_buried = preg_match_all('!------@@%([0-9\-]+)%@@------!', $tag->content, $info)) > 0) {   
                        //Tag has Embedded Tag
                        $containers = $info[0];
                        $indexs = $info[1];
                        $replacements = [];
                        foreach ($indexs as $key2=>$index){
                            $index_parts = explode('-', $index);
                            $tag_index = array_pop($index_parts);
                            if($tags[$tag_index]->parsed){
                                $replacements[$key2] = $tags[$tag_index]->parsed;
                            }
                            else {   
                                if($tags[$tag_index]->block) {
                                    $block = preg_replace('/ delayed="true"/', '', $tags[$tag_index]->block, 1);
                                    if($tag->block) {
                                        $tag->block = str_replace($containers[$key2], $block, $tag->block); 
                                    }
                                    $tag->content = str_replace($containers[$key2], $block, $tag->content); 
                                }
                            }
                        }
                        $tags[$key]->innermarkers = $tag->innermarkers = $containers;
                    }
                    if($tag->name === '___text') {  // Plain Text (no rendering)
                        $tags[$key]->parsed = $tag->content;
                    }
                    else {                          // Tag for processing  
                        $body               = $this->renderTag($tag);
                        $tags[$key]->parsed = $body;
                    }
                    // update any buried tags within the parsed content
                    $tags[$key]->parsed = $has_buried > 0 ? str_replace($containers, $replacements, $tags[$key]->parsed) : $tags[$key]->parsed;
                }
                
                return $tags[$key]->parsed;
            }
            return false;
        }

        /**
         * Utility Method to search for last allowable Tag not already processed
         *
         * @param string $subject
         * @return array of matched items
         */
        private function getLastTag($subject) {
            $PregMatch = '/'.$this->_searchReg.'/';    
            if (!preg_match_all($PregMatch, $subject, $matches,PREG_OFFSET_CAPTURE)) {
                return false;
            }
            return $matches[0][count($matches[0])-1];
        }
        
        /**
         * Searches and parses a source for custom tags.
         * @access public
         * @param string $source The source to search for custom tags in.
         * @return array An array of found tags.
         */
        public function processTags($source) { 
            $tags = [];

            // Sets Open Pos to end of HTML ($source)
            $eot = strlen($source);

            while ($eot !== false) {
                $currentSource   = substr($source, 0, $eot);    // Remaining HTML (moving Up)
                $eot = $this->getLastTag($currentSource);       // Postion of "Opener"

                if(!$eot) { // No More Tags found
                    $Class =  __NAMESPACE__.'\\CustomMarkupConcrete';
                    $tag   = new $Class($source, -1, -1);
                    array_push($tags, $tag);
                    break;
                }
                else{ // Tag found (start from last find)
                    $tagName           = str_replace('<','',$eot[0]);
                    $eot               = $eot[1];
                    $closer            = "</$tagName>";
                    $currentSource     = substr($source, $eot); // HTML from Last occurence till end or Last processed Tag
                    $NextDOM           = strpos($currentSource, '<', 1); //Start of Next DOM Tag
                    $NextClostTag      = strpos($currentSource, '/'.'>'); //Close Bracket Loc
                    
                    if($NextClostTag !== false && $NextClostTag < $NextDOM){
                        // Closing DOM is before the next DOM element (indicates <tag /> format)
                        $TagClose = $NextClostTag + 2; // Update TagClose to include />
                    }
                    else{
                        // Traditional <tag></tag> format
                        $TagClose_begin  = strpos($currentSource, $closer);
                        $TagClose        = strpos($currentSource, '>', $TagClose_begin)+1;
                    }

                    $tag_source     = substr($currentSource, 0, $TagClose);                                     //  Actual Tag Body
                    $Class          = __NAMESPACE__.'\\'.ucwords(str_replace(array('_', '-'), ' ', $tagName));  //  Tag Class
                    if(!class_exists($Class)) $Class =  __NAMESPACE__.'\\CustomMarkupConcrete';
                    $tag            = new $Class($tag_source, self::$_instance, count($tags));                  //  Tag
                    //$tag            = $this->_buildTag($tag_source, $tagName);
                    $index          = count($tags);
                    array_push($tags, $tag); // Append Tag (stdClass)

                    $source = substr($source, 0, $eot).$tag->placeholder.substr($source, $eot+$TagClose); // Update Source for next request
                }
            }
            return $tags;
        }    
    }
    
    /**
     * The Custom Tags exception.
     */
    class MarkupEngineException extends \Exception { }
    
    /**
     * The Custom Tags Helper class, It can help in making small custom tags,
     * however it is best to use your own template system or way of doing things.
     */
    class MarkupEngineHelper
    {
        /**
         * Returns the content of a template.
         * @access public
         * @param array $tag The tag array.
         * @param boolean $produce_error If there is an error with the template and this is set to true then an error is produced.
         */
        public static function getTemplate($tag, $produce_error=true)
        {
//          get the template
            $template_name = isset($tag['attributes']['template']) === true ? $tag['attributes']['template'] : 'default';
            if(is_array(MarkupEngine::$tag_directory_base) === false)
            {
                MarkupEngine::$tag_directory_base = array(MarkupEngine::$tag_directory_base);
            }
            foreach (MarkupEngine::$tag_directory_base as $directory)
            {
                $template = rtrim($directory, DIRECTORY_SEPARATOR).$tag['name'].DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.$template_name.'.html';
                if(is_file($template) === true)
                {
                    return file_get_contents($template);
                }
            }
//          template doesn't exist so produce error if required
            if($produce_error === true)
            {
                MarkupEngine::throwError($tag['name'], 'tag template resource not found.');
            }
            return false;
        }
        
        /**
         * A simple templater, replaces %VARNAME% with the value.
         * @access public
         * @param array $tag The tag array.
         * @param array $replacements The array of search and replace values.
         */
        public static function parseTemplate($tag, $replacements)
        {
//          get template
            $template = self::getTemplate($tag);
            $search = $replace = [];
//          compile search and replace values for replacement
            foreach ($replacements as $varname => $varvalue)
            {
                array_push($search, '%'.strtolower($varname).'%');
                array_push($replace, $varvalue);
            }
            return str_replace($search, $replace, $template);
        }
        
    }
    
