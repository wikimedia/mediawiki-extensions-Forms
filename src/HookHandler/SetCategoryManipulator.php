<?php

namespace MediaWiki\Extension\Forms\HookHandler;

use BlueSpice\InsertCategory\Hook\BlueSpiceInsertCategoryGetCategoryManipulatorHook;
use BlueSpice\InsertCategory\ICategoryManipulator;
use MediaWiki\Content\Content;
use MediaWiki\Extension\Forms\Content\FormDefinitionContent;
use MediaWiki\Extension\Forms\FormDefinitionCategoryManipulator;
use MediaWiki\Page\WikiPageFactory;
use MediaWiki\Revision\RevisionLookup;
use MediaWiki\Title\Title;

class SetCategoryManipulator implements BlueSpiceInsertCategoryGetCategoryManipulatorHook {

	/**
	 * @param RevisionLookup $revisionLookup
	 * @param WikiPageFactory $wikiPageFactory
	 */
	public function __construct(
		private readonly RevisionLookup $revisionLookup,
		private readonly WikiPageFactory $wikiPageFactory
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function onBlueSpiceInsertCategoryGetCategoryManipulator(
		Content $content, Title $page, ICategoryManipulator &$manipulator
	): void {
		if ( !( $content instanceof FormDefinitionContent ) ) {
			return;
		}
		$manipulator = new FormDefinitionCategoryManipulator( $this->revisionLookup, $this->wikiPageFactory );
	}
}
