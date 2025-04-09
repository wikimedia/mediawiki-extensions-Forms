<?php

namespace MediaWiki\Extension\Forms\HookHandler;

use MediaWiki\Hook\MediaWikiServicesHook;
use MediaWiki\Revision\Hook\ContentHandlerDefaultModelForHook;
use MediaWiki\Revision\SlotRoleRegistry;
use MediaWiki\Title\Title;

class SetupContentModels implements
	MediaWikiServicesHook,
	ContentHandlerDefaultModelForHook
{

	/**
	 * @inheritDoc
	 */
	public function onMediaWikiServices( $services ) {
		$services->addServiceManipulator(
			'SlotRoleRegistry',
			static function ( SlotRoleRegistry $registry ) {
				if ( $registry->isDefinedRole( FORM_DATA_REVISION_SLOT ) ) {
					return;
				}
				$registry->defineRoleWithModel(
					FORM_DATA_REVISION_SLOT,
					FORM_DATA_CONTENT_MODEL,
					[
						'display' => 'none'
					]
				);
			}

		);
	}

	/**
	 * Not using interface, to avoid multiple handlers in case CodeEditor is not enabled
	 *
	 * @param Title $title
	 * @param string|null &$lang
	 * @param string $model
	 * @param string $format
	 * @return bool
	 */
	public function onCodeEditorGetPageLanguage( Title $title, ?string &$lang, string $model, string $format ) {
		$currentContentModel = $title->getContentModel();
		if ( in_array( $currentContentModel, [ FORM_DATA_CONTENT_MODEL, FORM_DEFINITION_CONTENT_MODEL ] ) ) {
			$lang = 'json';
			return false;
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function onContentHandlerDefaultModelFor( $title, &$model ) {
		if ( preg_match( '/\.form$/', $title->getText() ) && !$title->isTalkPage() ) {
			$model = FORM_DEFINITION_CONTENT_MODEL;
			return false;
		}

		if ( preg_match( '/\.formdata$/', $title->getText() ) && !$title->isTalkPage() ) {
			$model = FORM_DATA_CONTENT_MODEL;
			return false;
		}
		return true;
	}
}
