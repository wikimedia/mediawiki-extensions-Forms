<?php

namespace MediaWiki\Extension\Forms;

use MediaWiki\Extension\Forms\Tag\CreateForm;
use MediaWiki\Extension\Forms\Tag\FormList;
use MediaWiki\Extension\Forms\Tag\FormMeta;
use MediaWiki\Message\Message;
use MediaWiki\Title\Title;
use Parser;
use SkinTemplate;

// phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName
class Setup {

	/**
	 *
	 * @param Title $title
	 * @param string &$model
	 * @return bool
	 */
	public static function onContentHandlerDefaultModelFor( Title $title, &$model ) {
		if ( preg_match( '/\.form$/', $title->getText() ) && !$title->isTalkPage() ) {
			$model = 'FormDefinition';
			return false;
		}

		if ( preg_match( '/\.formdata$/', $title->getText() ) && !$title->isTalkPage() ) {
			$model = 'FormData';
			return false;
		}
		return true;
	}

	/**
	 * @param Parser $parser
	 */
	public static function onParserFirstCallInit( Parser $parser ) {
		$parser->setHook( 'createForm', [ CreateForm::class, 'callback' ] );
		$parser->setHook( 'formMeta', [ FormMeta::class, 'callback' ] );
		$parser->setHook( 'formList', [ FormList::class, 'callback' ] );
	}

	/**
	 * @param SkinTemplate $sktemplate
	 * @param array &$links
	 * @return bool
	 */
	public static function onSkinTemplateNavigation_Universal( SkinTemplate $sktemplate, array &$links ) {
		$title = $sktemplate->getTitle();
		if ( !$title ) {
			return true;
		}
		$currentContentModel = $title->getContentModel();
		if ( in_array( $currentContentModel, [ 'FormDefinition', 'FormData' ] ) ) {
			// In case VisualEditor overrides with "Edit source"
			$links['views']['edit'] = [
				'text' => Message::newFromKey( 'edit' )->plain(),
				'title' => Message::newFromKey( 'edit' )->plain(),
				'href' => $title->getLocalURL( [
					'action' => 'edit',
				] )
			];

			// Add real "Edit source"
			$links['views']['editdefinitionsource'] = $links['views']['edit'];
			$links['views']['editdefinitionsource']['text']
				= Message::newFromKey( 'forms-action-editsource' )->plain();
			$links['views']['editdefinitionsource']['href'] = $title->getLinkURL( [
				'action' => 'editdefinitionsource'
			] );
		}
		return true;
	}

	/**
	 * @param Title $title
	 * @param string &$languageCode
	 * @return bool
	 */
	public static function onCodeEditorGetPageLanguage( Title $title, &$languageCode ) {
		$currentContentModel = $title->getContentModel();
		if ( in_array( $currentContentModel, [ 'FormDefinition', 'FormData' ] ) ) {
			$languageCode = 'json';
			return false;
		}

		return true;
	}
}
