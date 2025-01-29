<?php

namespace MediaWiki\Extension\Forms\ContentHandler;

use MediaWiki\Content\Content;
use MediaWiki\Content\JsonContentHandler;
use MediaWiki\Content\Renderer\ContentParseParams;
use MediaWiki\Extension\Forms\Action\FormDefinitionEditAction;
use MediaWiki\Extension\Forms\Action\FormDefinitionSourceEditAction;
use MediaWiki\Extension\Forms\Content\FormDefinitionContent;
use MediaWiki\Parser\ParserOutput;
use MediaWiki\Title\Title;

class FormDefinitionHandler extends JsonContentHandler {
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
		if ( !$content instanceof FormDefinitionContent ) {
			throw new \InvalidArgumentException( 'FormDefinitionHandler can only handle FormDefinitionContent' );
		}

		$page = $cpoParams->getPage();
		$title = Title::castFromPageReference( $page );
		$definitionForm = $content->getTitleWithoutExtension( $title );
		$data = (array)$content->getData()->getValue();
		$this->addCategoriesFromJSON( $output, $data );

		$formDataHandler = new FormDataHandler();
		$formDataHandler->fillParserOutputForDefinition( $content, $cpoParams, $output, $definitionForm );
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
