<?php

namespace MediaWiki\Extension\Forms\Special;

use MediaWiki\Config\Config;
use MediaWiki\Content\TextContent;
use MediaWiki\Extension\Forms\DefinitionManager;
use MediaWiki\Html\Html;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use OOUI\ActionFieldLayout;
use OOUI\ButtonInputWidget;
use OOUI\FormLayout;
use OOUI\TextInputWidget;

class FormEditor extends FormSpecial {
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

		if ( !$subPage ) {
			if ( $this->getRequest()->wasPosted() ) {
				$subPage = $this->getRequest()->getVal( 'formName' );
				if ( $subPage ) {
					header( 'Location: ' . $this->getPageTitle( $subPage )->getLocalURL() );
					exit;
				}
			}
			$this->outputCreateForm();
			return;
		}

		$cancelReturnTo = $successReturnTo = $this->getPageTitle()->getLocalURL();
		if ( str_ends_with( $subPage, '.form' ) ) {
			$subPage = substr( $subPage, 0, -5 );
		}
		$formTitle = $this->getFormTitle( $subPage );
		if ( $formTitle instanceof Title && $formTitle->exists() ) {
			$successReturnTo = $formTitle->getLocalURL();
		}
		if ( $this->formExists( $subPage ) ) {
			$this->data = $this->getDataForForm( $subPage );
			$this->action = 'edit';
			// If form already exist, in both cancel and save cases, return to it
			$cancelReturnTo = $successReturnTo;
		}

		$this->insertDependencies();
		$this->getOutput()->addHtml(
			Html::element( 'div', [
				'id' => 'form-editor',
				'data-form-data' => $this->data,
				'data-success-redir' => $successReturnTo,
				'data-cancel-redir' => $cancelReturnTo,
				'data-target-page' => $formTitle->getPrefixedDBkey(),
			] )
		);
	}

	private function outputCreateForm() {
		$this->getOutput()->enableOOUI();

		$nameInput = new TextInputWidget( [
			'name' => 'formName',
			'required' => true,
		] );
		$submit = new ButtonInputWidget( [
			'type' => 'submit',
			'label' => $this->msg( 'forms-editor-create-form-label' )->text(),
			'flags' => [ 'primary', 'progressive' ]
		] );
		$layout = new ActionFieldLayout( $nameInput, $submit, [
			'label' => $this->msg( 'forms-form-editor-form-name-label' )->text(),
		] );
		$form = new FormLayout( [
			'method' => 'POST',
			'action' => $this->getPageTitle()->getLocalURL(),
			'items' => [ $layout ]
		] );
		$this->getOutput()->addHtml( $form );
	}

	protected function insertDependencies() {
		$this->getOutput()->addModules( 'ext.forms.form.editor' );
		$this->getOutput()->addJsConfigVars( 'formsAvailableFormsForWidget', $this->getAvailableFormsForWidget() );

		$this->getOutput()->addJsConfigVars(
			'formsEmailTargets',
			array_keys( $this->config->get( 'FormsTargetEMailRecipients' )
			)
		);
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
	 * @param string $name
	 * @return Title|null
	 */
	private function getFormTitle( string $name ): ?Title {
		$title = Title::newFromText( "$name.form" );
		if ( $title instanceof Title ) {
			return $title;
		}
		return null;
	}

	/**
	 * @param string $subPage
	 * @return string
	 */
	private function getDataForForm( $subPage ) {
		$title = $this->getFormTitle( $subPage );
		if ( !$title instanceof Title ) {
			return '';
		}
		$wikipage = MediaWikiServices::getInstance()->getWikiPageFactory()
			->newFromTitle( $title );
		$content = $wikipage->getContent();
		return ( $content instanceof TextContent ) ? $content->getText() : '';
	}

	/**
	 *
	 * @return DefinitionManager
	 */
	private function getDefinitionManager() {
		/** @var DefinitionManager $manager */
		return MediaWikiServices::getInstance()->getService( 'FormsDefinitionManager' );
	}
}
