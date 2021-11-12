<?php

namespace MediaWiki\Extension\Forms\Special;

use SpecialPage;
use Html;
use ExtensionRegistry;

/**
 * Base class for all special pages that are supposed to show a form
 */
abstract class FormSpecial extends SpecialPage {

	protected function getFormContainer( $form, $data = '', $action = 'view', $created = '' ) {
		$data = [
			'class' => 'forms-form-container',
			'data-data' => $data,
			'data-form' => $form,
			'data-action' => $action
		];
		if ( $created !== '' ) {
			$data['data-form-created'] = $created;
		}
		return Html::element( 'div', $data );
	}

	protected function insertDependencies() {
		$this->getOutput()->addModules(
			"ext.forms.init"
		);
	}

}
