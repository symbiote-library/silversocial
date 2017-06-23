<?php

/**
 * @author marcus@symbiote.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class RestrictedMarkdown extends TextParser {
	public function __construct($content = "") {
		parent::__construct($content);
	}

	public function parse(){
		require_once BASE_PATH . '/microblog/thirdparty/phpmarkdown/markdown.php';
		return Markdown(strip_tags(ShortcodeParser::get_active()->parse($this->content)));
	}
}