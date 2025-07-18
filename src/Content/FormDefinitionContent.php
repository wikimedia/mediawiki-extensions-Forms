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

	/**
	 * @param array $categories
	 * @return FormDefinitionContent|null
	 */
	public function setCategories( array $categories ): ?static {
		if ( !$this->isValid() ) {
			return null;
		}
		$values = json_decode( $this->mText, true );
		$values['categories'] = $categories;
		$this->mText = json_encode( $values, JSON_PRETTY_PRINT );
		$this->jsonParse = null;
		return $this;
	}
}
