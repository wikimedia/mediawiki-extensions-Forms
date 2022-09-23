<?php

namespace MediaWiki\Extension\Forms\Content;

use MediaWiki\MediaWikiServices;
use Title;

class FormDataContent extends \JsonContent {

	/**
	 * @var string
	 */
	protected $formName;

	/**
	 * @param string $text
	 * @param string $modelId
	 */
	public function __construct( $text, $modelId = "FormData" ) {
		parent::__construct( $text, $modelId );
	}

	/**
	 * @param string $action
	 * @param Title|null $form
	 * @return string
	 */
	public function getFormContainer( $action = 'view', $form = null ) {
		$formConfig = [
			'data-action' => $action,
			'class' => 'forms-form-container'
		];

		$data = $this->getData()->getValue();
		if ( $action !== 'create' ) {
			if ( !$this->getFormProps() ) {
				return '';
			}
			unset( $data->_form );
			$data = \FormatJson::encode( $data );
			$formConfig['data-data'] = $data;
			$formConfig['data-form'] = $this->formName;
			if ( $form instanceof Title && $form->exists() ) {
				$firstRev = MediaWikiServices::getInstance()->getRevisionLookup()
					->getFirstRevision( $form->toPageIdentity() );
				$formConfig['data-form-created'] = $firstRev->getTimestamp();
			}

		}

		return \Html::element( 'div', $formConfig );
	}

	/**
	 * @param Title $title
	 * @return string
	 */
	public function getTitleWithoutExtension( $title ) {
		$prefixedText = $title->getPrefixedText();
		return substr( $prefixedText, 0, -9 );
	}

	/**
	 * @param Title $title
	 * @return string
	 */
	protected function getDisplayTitle( $title ) {
		$displayTitle = substr( $title->getPrefixedText(), 0, -9 );
		$hookContainer = MediaWikiServices::getInstance()->getHookContainer();
		$hookContainer->run( 'FormsGetDisplayTitle', [ $title, &$displayTitle, 'view' ] );
		return $displayTitle;
	}

	/**
	 * @return bool
	 */
	public function isValid() {
		return $this->getText() === '' || parent::isValid();
	}

	/**
	 * @return bool
	 */
	private function getFormProps() {
		if ( !$this->formName ) {
			$data = $this->getData()->getValue();
			if ( property_exists( $data, '_form' ) ) {
				$this->formName = $data->_form;
				return true;
			}
		}
		return false;
	}
}
