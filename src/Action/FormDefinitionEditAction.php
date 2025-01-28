<?php

namespace MediaWiki\Extension\Forms\Action;

use MediaWiki\SpecialPage\SpecialPage;

class FormDefinitionEditAction extends FormDataEditAction {

	/**
	 * @var string
	 */
	protected $contentModel = 'FormDefinition';

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
		// TODO: "forms-edit-form-definition";
		return "edit";
	}

	public function show() {
		parent::show();
		$formName = $this->getCurrentContent()->getTitleWithoutExtension( $this->getTitle() );
		$this->getOutput()->redirect(
			SpecialPage::getTitleFor( 'FormEditor' )->getLocalURL() . "/$formName"
		);
	}

	/**
	 * @return string
	 * @throws \MWException
	 */
	protected function getCreateRedirect() {
		return SpecialPage::getTitleFor( 'FormEditor' )->getLocalURL();
	}

	/**
	 * @return null
	 */
	public function onView() {
		return null;
	}
}
