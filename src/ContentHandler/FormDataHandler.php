<?php

namespace MediaWiki\Extension\Forms\ContentHandler;

use Article;
use MediaWiki\Content\Content;
use MediaWiki\Content\JsonContentHandler;
use MediaWiki\Content\Renderer\ContentParseParams;
use MediaWiki\Extension\Forms\Action\FormDataEditAction;
use MediaWiki\Extension\Forms\Content\FormDataContent;
use MediaWiki\Html\Html;
use MediaWiki\Json\FormatJson;
use MediaWiki\MediaWikiServices;
use MediaWiki\Parser\ParserOutput;
use MediaWiki\Title\Title;

class FormDataHandler extends JsonContentHandler {
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
	 * @var string
	 */
	protected $formName;

	/**
	 * @var string|null
	 */
	private $forcedFormName = null;

	/**
	 * @param Title $destination
	 * @param string $text
	 * @return \MediaWiki\Extension\Forms\Content\FormDataContent
	 */
	public function makeRedirectContent( Title $destination, $text = '' ) {
		$class = $this->getContentClass();
		$json = [
			"_redirect" => $destination->getPrefixedDBkey()
		];
		return new $class( FormatJson::encode( $json ) );
	}

	/**
	 * @param Content $content
	 * @param ContentParseParams $cpoParams
	 * @param ParserOutput $output
	 * @param string $definitionForm
	 *
	 * @return void
	 */
	public function fillParserOutputForDefinition(
		Content $content, ContentParseParams $cpoParams, ParserOutput $output, string $definitionForm
	) {
		$this->forcedFormName = $definitionForm;
		$this->fillParserOutput( $content, $cpoParams, $output, 'create' );
	}

	/**
	 * @param Content $content
	 * @param ContentParseParams $cpoParams
	 * @param ParserOutput &$output The output object to fill (reference).
	 * @param string|null $defaultAction
	 */
	protected function fillParserOutput(
		Content $content,
		ContentParseParams $cpoParams,
		ParserOutput &$output,
		?string $defaultAction = 'view'
	) {
		if ( !$content instanceof FormDataContent ) {
			throw new \InvalidArgumentException( 'FormDataHandler can only handle FormDataContent' );
		}

		$title = Title::castFromPageReference( $cpoParams->getPage() );
		$data = $content->getData()->getValue() ?? new \stdClass;

		if ( $content->isRedirect() ) {
			$destTitle = $this->getRedirectTarget( $data );
			if ( $destTitle instanceof Title ) {
				$output->addLink( $destTitle );
				if ( $cpoParams->getGenerateHtml() ) {
					$output->setRawText(
						Article::getRedirectHeaderHtml( $title->getPageLanguage(), $destTitle )
					);
					$output->addModuleStyles( [ 'mediawiki.action.view.redirectPage' ] );
				}
			}
			return;
		}
		$output->setDisplayTitle( $content->getDisplayTitle( $title ) );
		$output->setRawText( $this->getFormContainer( $data, $defaultAction, $title ) );
		$output->addModules( [ 'ext.forms.init' ] );
	}

	/**
	 * @param mixed $data
	 * @param string $action
	 * @param Title $title
	 * @return string
	 */
	private function getFormContainer( $data, $action, Title $title ) {
		$formConfig = [
			'data-action' => $action,
			'class' => 'forms-form-container',
			'data-target-title' => $title->getPrefixedDBkey(),
		];
		if ( $this->forcedFormName ) {
			$formConfig['data-form'] = $this->forcedFormName;
		}

		if ( $action !== 'create' ) {
			if ( !$this->getFormProps( $data ) ) {
				return '';
			}
			unset( $data->_form );
			$data = FormatJson::encode( $data );
			$formConfig['data-data'] = $data;
			$formConfig['data-form'] = $this->forcedFormName ?? $this->formName;
			if ( $title->exists() ) {
				$firstRev = MediaWikiServices::getInstance()->getRevisionLookup()
					->getFirstRevision( $title->toPageIdentity() );
				$formConfig['data-form-created'] = $firstRev->getTimestamp();
			}
		}
		return Html::element( 'div', $formConfig );
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
