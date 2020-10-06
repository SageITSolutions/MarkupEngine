<?php
	namespace MarkupEngine;
	
	class Alert extends CustomMarkup{

		public function render(){
			$style = $this->type ?? "primary";
			return <<< HTML
			<div class="alert alert-{$style}" role="alert">
				{$this->content}
			</div>
HTML;
		}
	}
?>