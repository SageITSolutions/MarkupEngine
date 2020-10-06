<!-- PROJECT LOGO -->
<br />
<p align="center">
  <a href="https://github.com/SageDavis/MarkupEngine">
    <img src="images/logo.png" alt="Logo" width="445" height="120">
  </a>

  <h2 align="center">MarkupEngine</h2>

  <p align="center">
    Allows you to create HTML markup tags that are rendered via class extensions to the included CustomMarkup Class.
Markup intended for simple visual elements reaused throughout code and should not inlcude Database calls (separate of View and Modal)
    <br />
    <a href="https://github.com/SageDavis/MarkupEngine"><strong>Explore the docs »</strong></a>
    <br />
    <br />
    <a href="https://github.com/SageDavis/MarkupEngine">View Demo</a>
    ·
    <a href="https://github.com/SageDavis/MarkupEngine/issues">Report Bug</a>
    ·
    <a href="https://github.com/SageDavis/MarkupEngine/issues">Request Feature</a>
  </p>
</p>



<!-- TABLE OF CONTENTS -->
## Table of Contents

* [About the Project](#about-the-project)
  * [Built With](#built-with)
* [Getting Started](#getting-started)
  * [Installation](#installation)
* [Usage](#usage)
* [Roadmap](#roadmap)
* [Contributing](#contributing)
* [License](#license)
* [Contact](#contact)
* [Acknowledgements](#acknowledgements)



<!-- ABOUT THE PROJECT -->
## About The Project

### Built With

* [vscode](https://code.visualstudio.com/)
* [php 7.3.5](https://www.php.net/releases/7_3_5.php)
* [php Herdoc](https://www.php.net/manual/en/language.types.string.php#language.types.string.syntax.heredoc)



<!-- GETTING STARTED -->
## Getting Started

To get a local copy up and running follow these simple steps.

### Installation

1. Clone the repo
```sh
git clone https://github.com/SageDavis/MarkupEngine.git
```

<!-- USAGE EXAMPLES -->
## Usage

This project consists of an included MarkupEngine class which parses custom HTML tags into valid HTML.

**Components:**
* MarkupEngine.php class (parser)
* CustomMarkup.php class (abstract base class used by parser)
* tags/mytagname.php    (customizable folder containing tag classes which must extend CustomMarkup)

### Example Tag.

```html
<header some="attribute">
    This is an example block of code. <br />
    This body would return as the Tag's "Content" while some would be an accessible attribute in the header class.
</header>`
```

### Integrated Example

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

<!-- ROADMAP -->
## Roadmap

See the [open issues](https://github.com/SageDavis/MarkupEngine/issues) for a list of proposed features (and known issues).



<!-- CONTRIBUTING -->
## Contributing

Contributions are what make the open source community such an amazing place to be learn, inspire, and create. Any contributions you make are **greatly appreciated**.

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request



<!-- LICENSE -->
## License

Distributed under the MIT License. See `LICENSE` for more information.



<!-- CONTACT -->
## Contact

Sage IT Solutions - [Email](mailto:daniel.davis@sageitsolutions.net)

Project Link: [https://github.com/SageDavis/MarkupEngine](https://github.com/SageDavis/MarkupEngine)



<!-- ACKNOWLEDGEMENTS -->
## Acknowledgements

* [buggedcom Original Lib](https://github.com/buggedcom/PHP-Custom-Tags)
