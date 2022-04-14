<?php

namespace MediaWiki\Extension\Forms\Action;

use Content;
use ContentHandler;
use FormlessAction;
use Hooks;
use MediaWiki\Extension\Forms\Content\FormDataContent;
use MediaWiki\Revision\RevisionRecord;
use SpecialPage;
use Title;

class FormDataEditAction extends FormlessAction {

	/**
	 * @var string
	 */
	protected $contentModel = 'FormData';

	/**
	 * @var string
	 */
	protected $contentFormat = '';

	/**
	 * @var string
	 */
	protected $action = 'edit';

	/**
	 * @return string
	 */
	public function getName() {
		return 'edit';
	}

	/**
	 * @return string
	 */
	public function getRestriction() {
		return 'edit';
	}

	public function show() {
		$this->useTransactionalTimeLimit();
		$out = $this->getOutput();

		$this->checkCanExecute( $this->getUser() );

		$content = $this->getCurrentContent();
		if ( $content instanceof FormDataContent === false ) {
			return;
		}

		if ( $this->checkSealed( $content ) ) {
			$out->addReturnTo( $this->getTitle() );
			return $out->addWikiMsg( 'forms-edit-error-form-sealed' );
		}
		if ( $this->action === 'create' ) {
			// All creations in this content model should be done over the SP
			return $out->redirect( $this->getCreateRedirect() );
		}

		$out->setPageTitle( $this->getDisplayTitle() );

		$out->addHTML( $content->getFormContainer( $this->action, $this->getTitle() ) );
		$out->addModules( 'ext.forms.init' );
	}

	/**
	 * @return Content|null
	 */
	protected function getCurrentContent() {
		$rev = $this->page->getRevision();
		$content = $rev ? $rev->getContent( RevisionRecord::RAW ) : null;

		if ( $content === false || $content === null ) {
			$handler = ContentHandler::getForModelID( $this->contentModel );
			$this->action = 'create';
			return $handler->makeEmptyContent();
		}
		return $content;
	}

	/**
	 * @return Title
	 */
	protected function getCreateRedirect() {
		return SpecialPage::getTitleFor( 'CreateFormInstance' )->getLocalURL();
	}

	/**
	 * @return null
	 */
	public function onView() {
		return null;
	}

	/**
	 * @return string
	 */
	private function getDisplayTitle() {
		$displayTitle = wfMessage(
			"forms-form-edit-title",
			$this->getCurrentContent()->getTitleWithoutExtension( $this->getTitle() )
		)->plain();
		Hooks::run( 'FormsGetDisplayTitle', [ $this->getTitle(), &$displayTitle, 'edit' ] );
		return $displayTitle;
	}

	/**
	 * @param FormDataContent $content
	 * @return bool
	 */
	private function checkSealed( FormDataContent $content ) {
		$data = $content->getData()->getValue();
		if ( $data === null ) {
			return false;
		}
		if ( !property_exists( $data, '_sealed' ) ) {
			return false;
		}
		if ( $data->_sealed === true ) {
			return true;
		}
		return false;
	}
}
