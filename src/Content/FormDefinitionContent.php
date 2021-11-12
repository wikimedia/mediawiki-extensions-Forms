<?php

namespace MediaWiki\Extension\Forms\Content;

use Html;
use Title;
use ParserOutput;
use ParserOptions;
use Hooks;

class FormDefinitionContent extends FormDataContent {
	protected $currentForm = '';

	public function __construct( $text, $modelId = 'FormDefinition' ) {
		parent::__construct( $text, $modelId );
	}

	protected function fillParserOutput( Title $title, $revId, ParserOptions $options, $generateHtml, ParserOutput &$output ) {
		$this->currentForm = $this->getTitleWithoutExtension( $title );
		$this->addCategoriesFromJSON( $output );
		parent::fillParserOutput( $title, $revId, $options, $generateHtml, $output );
	}

	public function getFormContainer( $action = 'view', $for = null ) {
		$formConfig = [
			'data-action' => 'create',
			'class' => 'forms-form-container'
		];

		$formConfig['data-form'] = $this->currentForm;

		return Html::element( 'div', $formConfig );
	}

	public function getTitleWithoutExtension( $title ) {
		$prefixedDBKey = $title->getPrefixedDBKey();
		return substr( $prefixedDBKey, 0, -5);
	}

	protected function getDisplayTitle( $title ) {
		$displayTitle = substr( $title->getPrefixedText(), 0, -5  );
		Hooks::run( 'FormsGetDisplayTitle', [ $title, &$displayTitle, 'view' ] );
		return $displayTitle;
	}

	/**
	 *
	 * @param ParserOutput $output
	 */
	private function addCategoriesFromJSON( $output ) {
		$formdata = (array) $this->getData()->getValue();
		$categories = isset( $formdata['categories'] ) ? $formdata['categories'] : [];
		foreach( $categories as $categoryName ) {
			$output->addCategory( $categoryName, $categoryName );
		}
	}

}
