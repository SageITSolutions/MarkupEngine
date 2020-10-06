#MarkupEngine

Allows you to create HTML markup tags that are rendered via class extensions to the included CustomMarkup Class.
Markup intended for simple visual elements reaused throughout code and should not inlcude Database calls (separate of View and Modal)

##Example Tag.

```html
<header some="attribute">
    This is an example block of code. <br />
    This body would return as the Tag's "Content" while some would be an accessible attribute in the header class.
</header>`
```

##Integrated Example

```php
<?php

require_once '../lib/MarkupEngine.php';

$ME = new MarkupEngine([]
    'parse_on_shutdown' 	=> true,
    'tag_directory' 		=> __DIR__.DIRECTORY_SEPARATOR.'tags'.DIRECTORY_SEPARATOR,
    'sniff_for_buried_tags' => true
]);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">

<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Example Page</title>
    <meta name="author" content="Daniel S. Davis">
    <!-- Date: 2020-10-06 -->
</head>
<body> 
    <youtube id="HTr778JctJE" />
</body>
</html>
```

**Inside the related tag file; tags/youtube.php:**

```php
<?php
namespace MarkupEngine;
	
class Header extends CustomMarkup{

    public function render(){
        $objID = $this->id; //alias for $this->attribute->id
        $Year = date('Y');
        $example = "Simple Example";//$tag['attributes']['example_name'];
        return <<< HTML
                <object width="480" height="295" id = "{$objID}">
                        <param name="movie" value="http://www.youtube.com/v/{$objID}=en&fs=1 5"></param>
                        <param name="allowFullScreen" value="true"></param>
                        <param name="allowscriptaccess" value="always"></param>
                        <embed src="http://www.youtube.com/v/{$objID}=en&fs=1 5" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="480" height="295">
                        </embed>
                </object>
HTML;
    }
}

````

**Resulting output:**

```html
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd"> 

<html lang="en"> 
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Example Page</title>
    <meta name="author" content="Daniel S. Davis">
    <!-- Date: 2020-10-06 -->
</head>
<body> 
    <object width="480" height="295" id = "HTr778JctJE">
            <param name="movie" value="http://www.youtube.com/v/HTr778JctJE=en&fs=1 5"></param>
            <param name="allowFullScreen" value="true"></param>
            <param name="allowscriptaccess" value="always"></param>
            <embed src="http://www.youtube.com/v/HTr778JctJE=en&fs=1 5" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="480" height="295">
            </embed>
    </object> 
</body> 
</html>
```
