<?php

namespace MediaWiki\Extension\Forms\Special;

use SpecialPage;
use Html;
use ExtensionRegistry;

abstract class FormSpecial extends SpecialPage {

	protected function getFormContainer( $form, $data = '', $action = 'view' ) {
		return Html::element( 'div', [
			'class' => 'forms-form-container',
			'data-data' => $data,
			'data-form' => $form,
			'data-action' => $action
		] );
	}

	protected function insertDependencies() {
		$this->insertTypeMap();
		$this->getOutput()->addModules(
			"ext.forms.init"
		);
	}

	private function insertTypeMap() {
		$typeMap = ExtensionRegistry::getInstance()->getAttribute(
			"FormsTypeMap"
		);

		$this->getOutput()->addJsConfigVars( 'FormsTypeMap', $typeMap );
	}

}