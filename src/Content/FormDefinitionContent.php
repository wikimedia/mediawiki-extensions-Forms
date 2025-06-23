<?php

namespace MediaWiki\Extension\Forms\Content;

use MediaWiki\Extension\Forms\DefinitionManager;

class FormDefinitionContent extends FormDataContent {
	/**
	 * @var string
	 */
	protected $currentForm = '';

	/**
	 * @param string $text
	 * @param string $modelId
	 */
	public function __construct( $text, $modelId = 'FormDefinition' ) {
		parent::__construct( $text, $modelId );
	}

	/**
	 * @return string
	 */
	protected function getPageFormat() {
		return DefinitionManager::DEFINITION_PAGE_SUFFIX;
	}
}
