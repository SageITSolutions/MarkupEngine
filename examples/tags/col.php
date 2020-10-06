<?php
	namespace MarkupEngine;
	
	class Col extends CustomMarkup{

		public function render(){
			$col = $this->size? "col-".$this->size : "col";
			return <<< HTML
			<div class="{$col}">
				{$this->content}
			</div>
HTML;
		}
	}
?>