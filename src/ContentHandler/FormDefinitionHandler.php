<?php

namespace MediaWiki\Extension\Forms\ContentHandler;

use JavaScriptContentHandler;
use MediaWiki\Extension\Forms\Content\FormDefinitionContent;
use MediaWiki\Extension\Forms\Action\FormDefinitionEditAction;

class FormDefinitionHandler extends JavaScriptContentHandler {
	public function __construct( $modelId = 'FormDefinition' ) {
		parent::__construct( $modelId );
	}
	protected function getContentClass() {
		return FormDefinitionContent::class;
	}
	public function supportsSections() {
		return false;
	}
	public function supportsCategories() {
		return true;
	}
	public function supportsRedirects() {
		return false;
	}

	public function getActionOverrides() {
		return [
			'edit' => FormDefinitionEditAction::class
		];
	}
}
