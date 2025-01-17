<?php

namespace MediaWiki\Extension\Forms\Content;

use Html;
use MediaWiki\Title\Title;

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
	 *
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
	 * @return string
	 */
	protected function getPageFormat() {
		return ".form";
	}
}
