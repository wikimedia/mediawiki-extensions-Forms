<?php

namespace MediaWiki\Extension\Forms\ContentHandler;

use JavaScriptContentHandler;
use MediaWiki\Extension\Forms\Action\FormDefinitionEditAction;
use MediaWiki\Extension\Forms\Action\FormDefinitionSourceEditAction;
use MediaWiki\Extension\Forms\Content\FormDefinitionContent;

class FormDefinitionHandler extends JavaScriptContentHandler {
	/**
	 * @param string $modelId
	 */
	public function __construct( $modelId = 'FormDefinition' ) {
		parent::__construct( $modelId );
	}

	/**
	 * @return bool
	 */
	protected function getContentClass() {
		return FormDefinitionContent::class;
	}

	/**
	 * @return bool
	 */
	public function supportsSections() {
		return false;
	}

	/**
	 * @return bool
	 */
	public function supportsCategories() {
		return true;
	}

	/**
	 * @return bool
	 */
	public function supportsRedirects() {
		return false;
	}

	/**
	 * @return array
	 */
	public function getActionOverrides() {
		return [
			'edit' => FormDefinitionEditAction::class,
			'editdefinitionsource' => FormDefinitionSourceEditAction::class
		];
	}
}
