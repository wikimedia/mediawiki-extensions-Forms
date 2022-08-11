<?php

namespace MediaWiki\Extension\Forms\Special;

use Config;
use MediaWiki\Extension\Forms\DefinitionManager;
use MediaWiki\MediaWikiServices;
use TextContent;
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
	 * @var Config
	 */
	protected $config;

	/**
	 * @var string
	 */
	protected $action = 'create';

	/**
	 *
	 * @param Config $config
	 */
	public function __construct( Config $config ) {
		parent::__construct( 'FormEditor', 'forms-edit-form-definition' );
		$this->config = $config;
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

		$this->getOutput()->addJsConfigVars(
			'formsEmailTargets',
			array_keys( $this->config->get( 'FormsTargetEMailRecipients' )
		) );
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
		if ( method_exists( MediaWikiServices::class, 'getWikiPageFactory' ) ) {
			// MW 1.36+
			$wikipage = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( $title );
		} else {
			$wikipage = WikiPage::factory( $title );
		}
		$content = $wikipage->getContent();
		$data = ( $content instanceof TextContent ) ? $content->getText() : '';
		return $data;
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
