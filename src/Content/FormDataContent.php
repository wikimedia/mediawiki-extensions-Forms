<?php

namespace MediaWiki\Extension\Forms\Content;

class FormDataContent extends \JsonContent {
	protected $formName;

	public function __construct( $text, $modelId = "FormData" ) {
		parent::__construct( $text, $modelId );
	}

	protected function fillParserOutput( \Title $title, $revId, \ParserOptions $options, $generateHtml, \ParserOutput &$output ) {
		if ( $this->isRedirect() ) {
			$destTitle = $this->getRedirectTarget();
			if ( $destTitle instanceof \Title ) {
				$output->addLink( $destTitle );
				if ( $generateHtml ) {
					$chain = $this->getRedirectChain();
					if ( $chain ) {
						$output->setText(
							\Article::getRedirectHeaderHtml( $title->getPageLanguage(), $chain, false )
						);
						$output->addModuleStyles( 'mediawiki.action.view.redirectPage' );
					}
				}
			}
			return;
		}
		$output->setDisplayTitle( $this->getDisplayTitle( $title ) );
		$output->setText( $this->getFormContainer() );
		$output->addJsConfigVars( 'FormsTypeMap', $this->getTypeMap() );
		$output->addModules( 'ext.forms.init' );
	}

	public function getRedirectTarget() {
		$data = $this->getData()->getValue();
		if ( property_exists( $data, '_redirect' ) ) {
			return \Title::newFromText( $data->_redirect );
		}
		return null;
	}

	public function getFormContainer( $action = 'view' ) {
		$formConfig = [
			'data-action' => $action,
			'class' => 'forms-form-container'
		];

		$data = $this->getData()->getValue();
		if ( $action !== 'create' ) {
			if( !$this->getFormName() ) {
				return '';
			}
			unset( $data->_form );
			$data = \FormatJson::encode( $data );
			$formConfig['data-data'] = $data;
			$formConfig['data-form'] = $this->formName;
		}

		return \Html::element( 'div', $formConfig );
	}

	public function getFormName() {
		if ( !$this->formName ) {
			$data = $this->getData()->getValue();
			if ( property_exists( $data, '_form' ) ) {
				$this->formName = $data->_form;
			}
		}
		return $this->formName;
	}

	public function getTypeMap() {
		$typeMap = \ExtensionRegistry::getInstance()->getAttribute(
			"FormsTypeMap"
		);

		return $typeMap;
	}

	public function getTitleWithoutExtension( $title ) {
		$prefixedText = $title->getPrefixedText();
		return substr( $prefixedText, 0, -9);
	}

	/**
	 * @param \Title $title
	 * @return string
	 */
	private function getDisplayTitle( $title ) {
		$displayTitle = substr( $title->getPrefixedText(), 0, -9  );
		\Hooks::run( 'FormsGetDisplayTitle', [ $title, &$displayTitle, 'view' ] );
		return $displayTitle;
	}
}
