<?php

namespace MediaWiki\Extension\Forms\Util;

use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MediaWiki\Title\Title;
use OOUI\ActionFieldLayout;
use OOUI\ButtonInputWidget;
use OOUI\DropdownInputWidget;
use OOUI\FormLayout;
use OOUI\HiddenInputWidget;
use OOUI\TextInputWidget;

class PickerMaker {

	/**
	 * @param string $forForm
	 * @return FormLayout
	 */
	public function makeTargetTitlePicker( string $forForm ): FormLayout {
		$nameInput = new TextInputWidget( [
			'name' => 'target',
			'required' => true,
		] );
		$formInput = new HiddenInputWidget( [
			'name' => 'formName',
			'value' => $forForm,
		] );
		$submit = new ButtonInputWidget( [
			'type' => 'submit',
			'label' => Message::newFromKey( 'forms-editor-create-form-instance-label' )->text(),
			'flags' => [ 'primary', 'progressive' ]
		] );
		$layout = new ActionFieldLayout( $nameInput, $submit, [
			'label' => Message::newFromKey( 'forms-target-page-label' )->text(),
		] );
		$editFormInstanceSpecial = MediaWikiServices::getInstance()->getSpecialPageFactory()
			->getPage( 'EditWithForm' );
		return new FormLayout( [
			'method' => 'GET',
			'action' => $editFormInstanceSpecial->getPageTitle()->getLocalURL(),
			'items' => [ $layout, $formInput ]
		] );
	}

	/**
	 * @param array $definitions
	 * @param Title|null $targetTitle
	 * @return FormLayout
	 */
	public function makeFormDefinitionPicker( array $definitions, ?Title $targetTitle ): FormLayout {
		$options = array_map(
			static function ( $definition ) {
				return [
					'label' => $definition,
					'data' => $definition,
				];
			},
			$definitions
		);

		$dropdown = new DropdownInputWidget( [
			'name' => 'formName',
			'options' => $options,
			'required' => true,
		] );
		$submit = new ButtonInputWidget( [
			'type' => 'submit',
			'label' => Message::newFromKey( 'forms-editor-create-form-instance-label' )->text(),
			'flags' => [ 'primary', 'progressive' ]
		] );
		$layout = new ActionFieldLayout( $dropdown, $submit, [
			'label' => Message::newFromKey( 'forms-form-definition-label' )->text(),
		] );
		$editFormInstanceSpecial = MediaWikiServices::getInstance()->getSpecialPageFactory()
			->getPage( 'EditWithForm' );
		return new FormLayout( [
			'method' => 'GET',
			'action' => $editFormInstanceSpecial->getPageTitle(
				$targetTitle ? $targetTitle->getPrefixedDBkey() : false
			)->getLocalURL(),
			'items' => [ $layout ]
		] );
	}
}
