<?php
	namespace MarkupEngine;
	
	class Header extends CustomMarkup{

		public function render(){
			$Year = date('Y');
			$title = $this->title ?? "Not provided";
			$tag = "&lt;".$this->name." /&gt;";
			$HTML = <<< HTML
					<a class="backtotop" href="./index.php">&larr; Back to index</a>
					<h2>{$title}</h2>
					<strong>Markup Engine &copy; Sage IT Soltuions {$Year} (&copy; Oliver Lillie 2013)</strong>
					This text is an example CustomTag {$tag}
					This examples body: {$this->content}
HTML;
			return nl2br($HTML);
		}
	}
?>