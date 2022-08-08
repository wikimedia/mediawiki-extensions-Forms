<?php

namespace MediaWiki\Extension\Forms\Content;

use Html;
use MediaWiki\MediaWikiServices;
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
}
