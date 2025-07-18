<?php

namespace MediaWiki\Extension\Forms;

use BlueSpice\InsertCategory\ICategoryManipulator;
use MediaWiki\CommentStore\CommentStoreComment;
use MediaWiki\Extension\Forms\Content\FormDefinitionContent;
use MediaWiki\Message\Message;
use MediaWiki\Page\PageIdentity;
use MediaWiki\Page\WikiPageFactory;
use MediaWiki\Permissions\Authority;
use MediaWiki\Revision\RevisionLookup;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\SlotRecord;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class FormDefinitionCategoryManipulator implements ICategoryManipulator, LoggerAwareInterface {

	/** @var LoggerInterface */
	private LoggerInterface $logger;

	/**
	 * @param RevisionLookup $revisionLookup
	 * @param WikiPageFactory $wikiPageFactory
	 */
	public function __construct(
		private readonly RevisionLookup $revisionLookup,
		private readonly WikiPageFactory $wikiPageFactory
	) {
		$this->logger = new NullLogger();
	}

	public function getCategories( PageIdentity $pageIdentity, ?RevisionRecord $revisionRecord = null ): array {
		$content = $this->getContent( $pageIdentity, $revisionRecord );
		if ( !$content ) {
			return [];
		}
		$data = $content->getData();
		if ( !$data->isOK() ) {
			return [];
		}
		$value = $data->getValue();
		return $value->categories ?? [];
	}

	public function setCategories( PageIdentity $pageIdentity, array $categoryTitles, Authority $actor ): bool {
		$categories = array_map( static function ( $cat ) {
			return $cat->getText();
		}, $categoryTitles );
		$categories = array_unique( $categories );

		$content = $this->getContent( $pageIdentity );
		if ( !$content ) {
			throw new \InvalidArgumentException(
				'Form definition content not found for page: ' . $pageIdentity->getFullText()
			);
		}
		$content = $content->setCategories( $categories );
		$wikiPage = $this->wikiPageFactory->newFromTitle( $pageIdentity );
		$updater = $wikiPage->newPageUpdater( $actor );
		$updater->setContent( SlotRecord::MAIN, $content );
		$revision = $updater->saveRevision(
			CommentStoreComment::newUnsavedComment( '' )
		);
		if ( !$revision ) {
			$this->logger->error( 'Failed to save form definition categories', [
				'page' => $pageIdentity->getFullText(),
				'messages' => array_map( static function ( $specifier ) {
					return Message::newFromSpecifier( $specifier )->text();
				}, $updater->getStatus()->getMessages() ),
			] );
			throw new \Exception( 'Failed to save form definition categories' );
		}
		return true;
	}

	/**
	 * @param PageIdentity $pageIdentity
	 * @param RevisionRecord|null $revisionRecord
	 * @return FormDefinitionContent|null
	 */
	private function getContent(
		PageIdentity $pageIdentity, ?RevisionRecord $revisionRecord = null
	): ?FormDefinitionContent {
		$revisionRecord = $revisionRecord ?? $this->revisionLookup->getRevisionByTitle( $pageIdentity );
		if ( !$revisionRecord ) {
			return null;
		}
		$content = $revisionRecord->getContent( SlotRecord::MAIN );
		if ( !( $content instanceof FormDefinitionContent ) ) {
			return null;
		}
		return $content;
	}

	/**
	 * @param LoggerInterface $logger
	 * @return void
	 */
	public function setLogger( LoggerInterface $logger ): void {
		$this->logger = $logger;
	}
}
