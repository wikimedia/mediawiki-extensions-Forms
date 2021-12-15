<?php

namespace MediaWiki\Extension\Forms\Special;

use MediaWiki\Extension\Forms\DefinitionManager;
use MediaWiki\MediaWikiServices;
use Title;
use WikiPage;

class FormEditor extends FormSpecial {

	/**
	 * @var string
	 */
	protected $form = 'FormEditor';

	/**
	 * @var string
	 */
	protected $data = '';

	/**
	 * @var string
	 */
	protected $action = 'create';

	public function __construct() {
		parent::__construct( 'FormEditor', 'forms-edit-form-definition' );
	}

	/**
	 * @param string|null $subPage
	 */
	public function execute( $subPage ) {
		parent::execute( $subPage );

		if ( $subPage ) {
			if ( $this->formExists( $subPage ) ) {
				$this->data = $this->getDataForForm( $subPage );
				$this->action = 'edit';
			}
		}

		$this->insertDependencies();
		$this->getOutput()->addHtml(
			$this->getFormContainer( $this->form, $this->data, $this->action )
		);
	}

	protected function insertDependencies() {
		$this->getOutput()->addModules( 'ext.forms.form.editor' );
		$this->getOutput()->addJsConfigVars( 'formsAvailableFormsForWidget', $this->getAvailableFormsForWidget() );
	}

	/**
	 * @return array
	 */
	private function getAvailableFormsForWidget() {
		$defManager = $this->getDefinitionManager();

		$formatted = [];
		foreach ( $defManager->getAllDefinitionKeys() as $group => $names ) {
			$formatted[$group] = [];
			foreach ( $names as $name ) {
				$formatted[$group][] = [
					'data' => $name,
					'label' => $name
				];
			}
		}
		return $formatted;
	}

	/**
	 * @param string $subPage
	 * @return bool
	 */
	private function formExists( $subPage ) {
		$title = Title::newFromText( "$subPage.form" );
		if ( $title instanceof Title && $title->exists() ) {
			return true;
		}
		return false;
	}

	/**
	 * @param string $subPage
	 * @return string
	 */
	private function getDataForForm( $subPage ) {
		$title = Title::newFromText( "$subPage.form" );
		$wikipage = WikiPage::factory( $title );
		$content = $wikipage->getContent();
		return $content->getNativeData();
	}

	/**
	 *
	 * @return DefinitionManager
	 */
	private function getDefinitionManager() {
		/** @var DefinitionManager $manager */
		$manager = MediaWikiServices::getInstance()->getService( 'FormsDefinitionManager' );
		return $manager;
	}

}
