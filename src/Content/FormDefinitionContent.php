<?php

namespace MediaWiki\Extension\Forms\Content;

use Html;
use MediaWiki\MediaWikiServices;
use ParserOptions;
use ParserOutput;
use Title;

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
	 * @param Title $title
	 * @param int $revId
	 * @param ParserOptions $options
	 * @param string $generateHtml
	 * @param ParserOutput &$output
	 */
	protected function fillParserOutput( Title $title, $revId, ParserOptions $options,
		$generateHtml, ParserOutput &$output ) {
		$this->currentForm = $this->getTitleWithoutExtension( $title );
		$this->addCategoriesFromJSON( $output );
		parent::fillParserOutput( $title, $revId, $options, $generateHtml, $output );
	}

	/**
	 * @param string $action
	 * @param Title|null $for
	 * @return string
	 */
	public function getFormContainer( $action = 'view', $for = null ) {
		$formConfig = [
			'data-action' => 'create',
			'class' => 'forms-form-container'
		];

		$formConfig['data-form'] = $this->currentForm;

		return Html::element( 'div', $formConfig );
	}

	/**
	 * @param Title $title
	 * @return string
	 */
	public function getTitleWithoutExtension( $title ) {
		$prefixedDBKey = $title->getPrefixedDBKey();
		return substr( $prefixedDBKey, 0, -5 );
	}

	/**
	 * @param Title $title
	 * @return string
	 */
	protected function getDisplayTitle( $title ) {
		$displayTitle = substr( $title->getPrefixedText(), 0, -5 );
		$hookContainer = MediaWikiServices::getInstance()->getHookContainer();
		$hookContainer->run( 'FormsGetDisplayTitle', [ $title, &$displayTitle, 'view' ] );
		return $displayTitle;
	}

	/**
	 *
	 * @param ParserOutput $output
	 */
	private function addCategoriesFromJSON( $output ) {
		$formdata = (array)$this->getData()->getValue();
		$categories = isset( $formdata['categories'] ) ? $formdata['categories'] : [];
		foreach ( $categories as $categoryName ) {
			$output->addCategory( $categoryName, $categoryName );
		}
	}

}
