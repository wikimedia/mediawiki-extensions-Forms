<?php

namespace MediaWiki\Extension\Forms\Content;

use Title;

class FormDataContent extends \JsonContent {
	/**
	 *
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
	 * @param Title $title
	 * @param int $revId
	 * @param \ParserOptions $options
	 * @param string $generateHtml
	 * @param \ParserOutput &$output
	 */
	protected function fillParserOutput( Title $title, $revId, \ParserOptions $options,
		$generateHtml, \ParserOutput &$output ) {
		if ( $this->isRedirect() ) {
			$destTitle = $this->getRedirectTarget();
			if ( $destTitle instanceof Title ) {
				$output->addLink( $destTitle );
				if ( $generateHtml ) {
					$chain = $this->getRedirectChain();
					if ( $chain ) {
						$output->setText(
							\Article::getRedirectHeaderHtml(
								$title->getPageLanguage(), $chain, false
							)
						);
						$output->addModuleStyles( 'mediawiki.action.view.redirectPage' );
					}
				}
			}
			return;
		}
		$output->setDisplayTitle( $this->getDisplayTitle( $title ) );
		$output->setText( $this->getFormContainer( 'view', $title ) );
		$output->addModules( 'ext.forms.init' );
	}

	/**
	 * @return Title|null
	 */
	public function getRedirectTarget() {
		$data = $this->getData()->getValue();
		if ( !is_object( $data ) ) {
			return null;
		}
		if ( property_exists( $data, '_redirect' ) ) {
			return Title::newFromText( $data->_redirect );
		}
		return null;
	}

	/**
	 * @param string $action
	 * @param Title|null $for
	 * @return string
	 */
	public function getFormContainer( $action = 'view', $for = null ) {
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
			if ( $for instanceof Title && $for->exists() ) {
				$formConfig['data-form-created'] = $for->getFirstRevision()->getTimestamp();
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

	/**
	 * @param Title $title
	 * @return string
	 */
	protected function getDisplayTitle( $title ) {
		$displayTitle = substr( $title->getPrefixedText(), 0, -9 );
		\Hooks::run( 'FormsGetDisplayTitle', [ $title, &$displayTitle, 'view' ] );
		return $displayTitle;
	}

	/**
	 * @return bool
	 */
	public function isValid() {
		return $this->getText() === '' || parent::isValid();
	}
}
