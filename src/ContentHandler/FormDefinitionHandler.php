<?php

namespace MediaWiki\Extension\Forms\ContentHandler;

use JavaScriptContentHandler;
use MediaWiki\Extension\Forms\Action\FormDefinitionEditAction;
use MediaWiki\Extension\Forms\Content\FormDefinitionContent;

class FormDefinitionHandler extends JavaScriptContentHandler {
	public function __construct() {
		parent::__construct( 'FormDefinition' );
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

	/*public function getActionOverrides() {
		return [
			'edit' => FormDefinitionEditAction::class
		];
	}*/
}
