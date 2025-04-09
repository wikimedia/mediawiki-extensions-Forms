<?php

namespace MediaWiki\Extension\Forms\HookHandler;

use MediaWiki\Hook\SkinTemplateNavigation__UniversalHook;
use MediaWiki\Message\Message;
use MediaWiki\Page\WikiPageFactory;
use MediaWiki\SpecialPage\SpecialPageFactory;
use MediaWiki\Title\Title;

class AddContentActions implements SkinTemplateNavigation__UniversalHook {

	/**
	 * @param SpecialPageFactory $spf
	 * @param WikiPageFactory $wikiPageFactory
	 */
	public function __construct(
		private readonly SpecialPageFactory $spf,
		private readonly WikiPageFactory $wikiPageFactory
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function onSkinTemplateNavigation__Universal( $sktemplate, &$links ): void {
		$title = $sktemplate->getTitle();
		if ( !$title ) {
			return;
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
		if ( $this->hasFormDataSlot( $title ) ) {
			$sp = $this->spf->getPage( 'EditWithForm' );
			if ( $sp ) {
				$links['views']['edit-with-form'] = [
					'text' => Message::newFromKey( 'forms-action-edit-with-form' )->plain(),
					'title' => Message::newFromKey( 'forms-action-edit-with-form' )->plain(),
					'href' => $sp->getPageTitle( $title->getPrefixedDBkey() )->getLocalURL(),
					'position' => 2
				];
			}
		}
	}

	/**
	 * @param Title $title
	 * @return bool
	 */
	private function hasFormDataSlot( Title $title ) {
		if ( $title->isSpecialPage() || !$title->exists() || !$title->canExist() ) {
			return false;
		}
		$wp = $this->wikiPageFactory->newFromTitle( $title );
		return (bool)$wp->getRevisionRecord()?->hasSlot( FORM_DATA_REVISION_SLOT );
	}

}
