<?php
	namespace MarkupEngine;
	
	class Block extends CustomMarkup{

		public function render(){
			$block = $this->type ?? "container";
			return <<< HTML
			<div class="{$block}">
				{$this->content}
			</div>
HTML;
		}
	}
?>