<?php
	namespace MarkupEngine;
	
	class Row extends CustomMarkup{

		public function render(){
			return <<< HTML
			<div class="row">
				{$this->content}
			</div>
HTML;
		}
	}
?>