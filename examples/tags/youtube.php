<?php
	namespace MarkupEngine;
	
	class Youtube extends CustomMarkup{
	
		public function render(){
			return <<< HTML
				<iframe width="560" height="315" 
					src="https://www.youtube.com/embed/{$this->src}" 
					frameborder="0" 
					allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
					allowfullscreen>
				</iframe>

HTML;
		}
	}
?>