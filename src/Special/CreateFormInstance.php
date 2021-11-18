<?php

namespace MediaWiki\Extension\Forms\Special;

use MediaWiki\Extension\Forms\DefinitionManager;
use MediaWiki\MediaWikiServices;

class CreateFormInstance extends FormSpecial {
	/**
	 * @var DefinitionManager
	 */
	protected $definitionManager;

	/**
	 * @var string
	 */
	protected $formDefinition = '';

	public function __construct() {
		parent::__construct( 'CreateFormInstance', "forms-create-form" );

		$this->definitionManager = MediaWikiServices::getInstance()->getService(
			'FormsDefinitionManager'
		);
	}

	/**
	 * @param string|null $formName
	 */
	public function execute( $formName ) {
		parent::execute( $formName );

		if ( !empty( $formName ) && $this->definitionManager->definitionExists( $formName ) ) {
			$this->setFormDefinition( $formName );
		}

		$this->insertDependencies();
		$this->getOutput()->addHtml(
			$this->getFormContainer( $this->formDefinition, '', 'create' )
		);
	}

	/**
	 * @param string $form
	 */
	protected function setFormDefinition( $form ) {
		$this->formDefinition = $form;
		$this->getOutput()->setPageTitle(
			wfMessage(
				"forms-createforminstance-form-name",
				str_replace( '_', ' ', $form )
			)
		);
	}
}
