<?php

namespace MediaWiki\Extension\Forms\Action;

use FormlessAction;
use MediaWiki\Extension\Forms\Content\FormDataContent;
use MediaWiki\Storage\RevisionRecord;
use SpecialPage;
use Content;
use Hooks;
use ContentHandler;

class FormDataEditAction extends FormlessAction {
	protected $contentModel = 'FormData';
	protected $contentFormat = '';

	protected $action = 'edit';

	public function getName() {
		return 'edit';
	}

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
			return $out->addWikiMsg( 'ext-forms-edit-error-form-sealed' );
		}
		if ( $this->action === 'create' ) {
			// All creations in this content model should be done over the SP
			return $out->redirect( $this->getCreateRedirect() );
		}

		$out->setPageTitle( $this->getDisplayTitle() );

		$out->addHTML( $content->getFormContainer( $this->action ) );
		$out->addJsConfigVars( 'FormsTypeMap', $content->getTypeMap() );
		$out->addModules( 'ext.forms.init' );
	}

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

	protected function getCreateRedirect() {
		return SpecialPage::getTitleFor( 'CreateFormInstance' )->getLocalURL();
	}

	public function onView() {
		return null;
	}

	private function getDisplayTitle() {
		$displayTitle = wfMessage(
			"forms-form-edit-title",
			$this->getCurrentContent()->getTitleWithoutExtension( $this->getTitle() )
		)->plain();
		Hooks::run( 'FormsGetDisplayTitle', [ $this->getTitle(), &$displayTitle, 'edit' ] );
		return $displayTitle;
	}

	private function checkSealed( FormDataContent $content ) {
		$data = $content->getData()->getValue();
		if( !property_exists( $data, '_sealed' ) ) {
			return false;
		}
		if ( $data->_sealed === true ) {
			return true;
		}
		return false;
	}
}
