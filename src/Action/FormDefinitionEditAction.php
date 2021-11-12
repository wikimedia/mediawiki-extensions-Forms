<?php

namespace MediaWiki\Extension\Forms\Action;

use SpecialPage;

class FormDefinitionEditAction extends FormDataEditAction {
	protected $contentModel = 'FormDefinition';

	protected $action = 'edit';

	public function getName() {
		return 'edit';
	}

	public function getRestriction() {
		return "edit"; // TODO: "forms-edit-form-definition";
	}

	public function show() {

		parent::show();
		$formName = $this->getCurrentContent()->getTitleWithoutExtension( $this->getTitle() );
		$this->getOutput()->redirect(
			SpecialPage::getTitleFor( 'FormEditor' )->getLocalURL() . "/$formName"
		);
	}


	protected function getCreateRedirect() {
		return SpecialPage::getTitleFor( 'FormEditor' )->getLocalURL();
	}

	public function onView() {
		return null;
	}
}
