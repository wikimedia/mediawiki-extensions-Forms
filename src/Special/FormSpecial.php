<?php

namespace MediaWiki\Extension\Forms\Special;

use MediaWiki\Html\Html;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\Title\Title;

/**
 * Base class for all special pages that are supposed to show a form
 */
abstract class FormSpecial extends SpecialPage {

	/**
	 * @param string $form
	 * @param Title|null $targetPage
	 * @param string $data
	 * @param string $action
	 * @param string $created
	 * @return string
	 */
	protected function getFormContainer( $form, ?Title $targetPage, $data = '', $action = 'view', $created = '', ) {
		$data = [
			'class' => 'forms-form-container',
			'data-data' => $data,
			'data-form' => $form,
			'data-action' => $action,
			'data-target-title' => $targetPage?->getPrefixedDBkey(),
		];
		if ( $created !== '' ) {
			$data['data-form-created'] = $created;
		}
		return Html::element( 'div', $data );
	}

	/**
	 * @return void
	 */
	protected function insertDependencies() {
		$this->getOutput()->addModules(
			"ext.forms.init"
		);
	}

}
