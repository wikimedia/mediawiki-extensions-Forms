<?php

namespace MediaWiki\Extension\Forms\Special;

use MediaWiki\Extension\Forms\DefinitionManager;
use MediaWiki\MediaWikiServices;

class CreateForm extends \SpecialPage {
	/**
	 * @var DefinitionManager
	 */
	protected $definitionManager;

	protected $formDefinition = '';

	public function __construct() {
		parent::__construct( 'CreateFormInstance', "forms-create-form" );

		$this->definitionManager = MediaWikiServices::getInstance()->getService(
			'FormsDefinitionManager'
		);
	}

	public function execute( $subPage ) {
		parent::execute( $subPage );

		if ( !empty( $subPage ) && $this->definitionManager->definitionExists( $subPage ) ) {
			$this->setFormDefinition( $subPage );
		}

		$this->insertTypeMap();
		$this->insertFormContainer();

		$this->getOutput()->addModules(
			"ext.forms.init"
		);
	}

	protected function insertFormContainer() {
		$this->getOutput()->addHTML(
			\Html::element( 'div', [
				'class' => 'forms-form-container',
				'data-form' => $this->formDefinition,
				'data-action' => 'create'
			] )
		);
	}

	protected function insertTypeMap() {
		$typeMap = \ExtensionRegistry::getInstance()->getAttribute(
			"FormsTypeMap"
		);

		$this->getOutput()->addJsConfigVars( 'FormsTypeMap', $typeMap );
	}

	protected function setFormDefinition( $form ) {
		$this->formDefinition = $form;
	}
}