<?php

namespace MediaWiki\Extension\Forms\ContentHandler;

use MediaWiki\Content\Content;
use MediaWiki\Content\JsonContentHandler;
use MediaWiki\Content\Renderer\ContentParseParams;
use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\Forms\Action\FormDefinitionEditAction;
use MediaWiki\Extension\Forms\Action\FormDefinitionSourceEditAction;
use MediaWiki\Extension\Forms\Content\FormDefinitionContent;
use MediaWiki\Extension\Forms\Target\TitleTarget;
use MediaWiki\Extension\Forms\Util\PickerMaker;
use MediaWiki\Extension\Forms\Util\TargetFactory;
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

		$targetData = isset( $data['target'] ) ? (array)$data['target'] : [];
		if ( !$targetData ) {
			return;
		}
		// Title is needed to create the target, but since we are not actually executing the target,
		// we can use a dummy title.
		$targetData['title'] = 'Dummy';
		$targetData['form'] = $definitionForm;
		$target = ( new TargetFactory() )->makeTarget( $targetData['type'], $targetData );
		if ( $target instanceof TitleTarget ) {
			$this->outputTitleInputForm( $output, $definitionForm );
		} else {
			$formDataHandler = new FormDataHandler();
			$formDataHandler->fillParserOutputForDefinition( $content, $cpoParams, $output, $definitionForm );
		}
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

	private function outputTitleInputForm( ParserOutput &$output, string $form ) {
		RequestContext::getMain()->getOutput()->enableOOUI();
		$output->setEnableOOUI( true );

		$form = ( new PickerMaker() )->makeTargetTitlePicker( $form );

		$output->setRawText( $form->toString() );
	}

}
