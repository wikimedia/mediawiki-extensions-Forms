<?php

namespace MediaWiki\Extension\Forms\ContentHandler;

use Article;
use Content;
use MediaWiki\Content\Renderer\ContentParseParams;
use MediaWiki\Extension\Forms\Action\FormDataEditAction;
use MediaWiki\Extension\Forms\Content\FormDataContent;
use MediaWiki\MediaWikiServices;
use ParserOutput;
use Title;

class FormDataHandler extends \JsonContentHandler {
	/**
	 * @param string $modelId
	 */
	public function __construct( $modelId = 'FormData' ) {
		parent::__construct( $modelId );
	}

	/**
	 * @return string
	 */
	protected function getContentClass() {
		return FormDataContent::class;
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
			'edit' => FormDataEditAction::class
		];
	}

	/**
	 *
	 * @var string
	 */
	protected $formName;

	/**
	 * @param \Title $destination
	 * @param string $text
	 * @return \MediaWiki\Extension\Forms\ContentHandler\class
	 */
	public function makeRedirectContent( \Title $destination, $text = '' ) {
		$class = $this->getContentClass();
		$json = [
			"_redirect" => $destination->getPrefixedDBkey()
		];
		return new $class( \FormatJson::encode( $json ) );
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
		$data = $content->getData()->getValue() ?? new \stdClass;

		if ( $content->isRedirect() ) {
			$destTitle = $this->getRedirectTarget( $data );
			if ( $destTitle instanceof Title ) {
				$output->addLink( $destTitle );
				if ( $cpoParams->generateHtml ) {
					$output->setText(
						Article::getRedirectHeaderHtml( $title->getPageLanguage(), $destTitle, false )
					);
					$output->addModuleStyles( [ 'mediawiki.action.view.redirectPage' ] );
				}
			}
			return;
		}
		$output->setDisplayTitle( $this->getDisplayTitle( $title ) );
		$output->setText( $this->getFormContainer( $data, 'view', $title ) );
		$output->addModules( [ 'ext.forms.init' ] );
	}

	/**
	 * @param Title $title
	 * @return string
	 */
	private function getDisplayTitle( $title ) {
		$displayTitle = substr( $title->getPrefixedText(), 0, -9 );
		\Hooks::run( 'FormsGetDisplayTitle', [ $title, &$displayTitle, 'view' ] );
		return $displayTitle;
	}

	/**
	 * @param mixed $data
	 * @param string $action
	 * @param Title|null $title
	 * @return string
	 */
	private function getFormContainer( $data, $action = 'view', $title = null ) {
		$formConfig = [
			'data-action' => $action,
			'class' => 'forms-form-container'
		];

		if ( $action !== 'create' ) {
			if ( !$this->getFormProps( $data ) ) {
				return '';
			}
			unset( $data->_form );
			$data = \FormatJson::encode( $data );
			$formConfig['data-data'] = $data;
			$formConfig['data-form'] = $this->formName;
			if ( $title instanceof Title && $title->exists() ) {
				$firstRev = MediaWikiServices::getInstance()->getRevisionLookup()
					->getFirstRevision( $title->toPageIdentity() );
				$formConfig['data-form-created'] = $firstRev->getTimestamp();
			}
		}
		return \Html::element( 'div', $formConfig );
	}

	/**
	 * @param mixed $data
	 * @return void
	 */
	private function getRedirectTarget( $data ) {
		if ( !is_object( $data ) ) {
			return null;
		}
		if ( property_exists( $data, '_redirect' ) ) {
			return Title::newFromText( $data->_redirect );
		}
		return null;
	}

	/**
	 * @param mixed $data
	 * @return bool
	 */
	private function getFormProps( $data ) {
		if ( !$this->formName ) {
			if ( property_exists( $data, '_form' ) ) {
				$this->formName = $data->_form;
				return true;
			}
		}
		return false;
	}
}
