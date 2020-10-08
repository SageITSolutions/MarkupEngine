<?php
    namespace MarkupEngine;

    abstract class CustomMarkup
    {        
        protected $block        = "",       // FULL block (HTML)
                  $content      = "",       // Inner content (HTML)
                  $name         = "",       // Class / Object Name
                  $placeholder  = "",       // Used to represent Item
                  $attributes   = [],       // extensible items
                  $inlineClose  = false,    // Defines if tag is <tag /> format
                  $tagclose     = ">",      // Used in defining tag format
                  $parsed       = false,
                  $parsedcontent= "",
                  $innermarkers = "",       
                  $tagSearch    = "";       // Regex Search Pattern
        
        /**
         * Creates Markup Object
         */
        public function __construct($strBody = "", $instance, $index){
            $this->placeholder  = '------@@%'.$instance.'-'.$index.'%@@------';
            $this->block        = $strBody;
            $this->name         = strtolower(str_replace(__NAMESPACE__."\\","",get_class($this)));
            if(substr($strBody, -2) == '/>'){
                $this->inlineClose = true;
                $this->tagclose = "\/>";
            }
            $this->tagSearch    = "/<($this->name)\s*([^$this->tagclose]*)/";
            $this->build($strBody);
        }

        private function build($strBody){
            $begin_len = strlen('<'.$this->name);
            if(substr($strBody, 0, $begin_len) !== '<'.$this->name) {
                $this->name = '___text';
                $this->content = $this->block;
                return;
            }
            else if(substr($strBody, 1, 1) === '/'){
                $this->name = '---ERROR---';
                return;
            }
            $matches = [];
            if(preg_match_all($this->tagSearch, $this->block, $matches) > 0) {
                $attribute_string = $matches[2][0];
                if(!$this->inlineClose) {
                    $begin_len      = strlen($matches[0][0]);
                    $end_len        = strlen("</".$this->name.">");
                    $this->content  = substr($strBody, $begin_len+1, strlen($matches[0][0])-$begin_len-$end_len);
                }
                
                $attributes = [];
                if(preg_match_all("!([_\-A-Za-z0-9]*)(=\")([^\"]*)(\")!is", $attribute_string, $attributes) > 0)
                {
                    foreach($attributes[0] as $key=>$row)
                    {
                        $this->attributes[$attributes[1][$key]] = $attributes[3][$key];
                    }
                    /** @todo: template engine */
                    /*if(isset($this->attributes['template']) === true)
                    {                                             
                        $template = $this->_options['template_directory'].$tag['name'].DIRECTORY_SEPARATOR.$this->attributes['template'].'.html';
                        if(is_file($template) === false)
                        {                                 
                            $this->attributes['_template'] = $template;
                        }
                        else
                        {
                            $this->attributes['template'] = $template;
                        }
                    }*/
                }
            } 
            $this->attributes = (object) $this->attributes;
        }

        /**
         * Magic Method to return readonly properties
         *
         * @param string $var property name
         * @return variant property
         */
        public function __get($var){
            // Add in_array to restrict access
            if(isset($this->$var)) return $this->$var;
            if(isset($this->attributes->$var)) return $this->attributes->$var;
            return null;
        }

        /**
         * Magic Method to set readonly properties
         *
         * @param string $var name of variable to set
         * @param string $val value to apply
         */
        public function __set($var, $val){
            if(in_array($var,['parsed','parsedcontent','block','content','placeholder','innermarkers'])){
                $this->$var = $val ?? null;
            }
        }

        abstract public function render();
    }

    class CustomMarkupConcrete extends CustomMarkup
    {       
        public function render(){ return $this->content; }
    }


    
