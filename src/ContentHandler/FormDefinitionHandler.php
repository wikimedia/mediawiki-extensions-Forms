<?php

namespace MediaWiki\Extension\Forms\ContentHandler;

use Content;
use JavaScriptContentHandler;
use MediaWiki\Content\Renderer\ContentParseParams;
use MediaWiki\Extension\Forms\Action\FormDefinitionEditAction;
use MediaWiki\Extension\Forms\Action\FormDefinitionSourceEditAction;
use MediaWiki\Extension\Forms\Content\FormDefinitionContent;
use ParserOutput;
use Title;

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

	/**
	 * @param Content $content
	 * @param ContentParseParams $cpoParams
	 * @param ParserOutput &$output The output object to fill (reference).
	 */
	protected function fillParserOutput(
		Content $content,
		ContentParseParams $cpoParams,
		ParserOutput &$output
	) {
		$dbKey = $cpoParams->getPage()->getDBkey();
		$title = Title::newFromDBkey( $dbKey );
		$this->currentForm = $this->getTitleWithoutExtension( $title );
		$data = (array)$content->getData()->getValue();
		$this->addCategoriesFromJSON( $output, $data );

		$formDataHandler = new FormDataHandler();
		$formDataHandler->fillParserOutput( $content, $cpoParams, $output );
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
	 * @param ParserOutput $output
	 * @param array $data
	 * @return void
	 */
	private function addCategoriesFromJSON( $output, $data ) {
		$categories = isset( $data['categories'] ) ? $data['categories'] : [];
		foreach ( $categories as $categoryName ) {
			$output->addCategory( $categoryName, $categoryName );
		}
	}
}
