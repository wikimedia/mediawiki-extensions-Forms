<?php

namespace MediaWiki\Extension\Forms\Content;

use ParserOptions;
use ParserOutput;
use JavaScriptContent;
use Title;
use Html;

class FormDefinitionContent extends JavaScriptContent {
	public function __construct( $text ) {
		parent::__construct( $text, 'FormDefinition' );
	}

	public function getTitleWithoutExtension( $title ) {
		$prefixedDBKey = $title->getPrefixedDBKey();
		return substr( $prefixedDBKey, 0, -5);
	}
}
