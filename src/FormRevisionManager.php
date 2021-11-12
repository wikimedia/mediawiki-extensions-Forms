<?php

namespace MediaWiki\Extension\Forms;

use MediaWiki\MediaWikiServices;
use MediaWiki\Storage\RevisionStore;
use Database;
use Title;
use Status;

class FormRevisionManager {
	const TABLE = 'form_revision';

	const FIELD_REV_ID = 'fr_rev_id';
	const FIELD_PAGE_ID = 'fr_page_id';
	const FIELD_APPLIES_FROM = 'fr_applies_from';

	/**
	 * @var RevisionStore
	 */
	protected $revisionStore;

	/**
	 * @var Database
	 */
	protected $db;

	/**
	 *
	 * @param RevisionStore $revisionStore
	 * @param Database $db
	 * @return FormRevisionManager
	 */
	public static function factory( $revisionStore, $db ) {
		return new static( $revisionStore, $db );
	}

	public function __construct( $revisionStore, $db ) {
		$this->revisionStore = $revisionStore;
		$this->db = $db;
	}

	/**
	 * Inserts revision tracking
	 *
	 * @param Title $title
	 * @param int $revId
	 * @param string $ts
	 * @return bool
	 */
	public function insert( $title, $revId = 0, $ts = '' ) {
		if ( $revId === 0 ) {
			$revId = $title->getLatestRevID();
		}
		if ( $ts === '' ) {
			$ts = wfTimestamp( TS_MW );
		}

		if ( $this->exists( $revId ) ) {
			return true;
		}
		$this->doInsert( $title->getArticleID(), $revId, $ts );
	}

	/**
	 * Delete revision history for given form
	 *
	 * @param $formId
	 * @return bool
	 */
	public function deleteForForm( $formId ) {
		if ( !$formId ) {
			return true;
		}

		return (bool)$this->db->delete(
			static::TABLE,
			[ static::FIELD_PAGE_ID => $formId ],
			__METHOD__
		);
	}

	public function syncRevs( $form, $maxRev ) {
		$defManager = MediaWikiServices::getInstance()->getService(
			'FormsDefinitionManager'
		);

		$conds = [];
		if ( $form !== '*' ) {
			if ( $defManager->definitionExists( $form ) ) {
				$formTitle = Title::newFromText( "$form.form" ); // TODO: Hardcoded
				if ( !$formTitle instanceof Title || !$formTitle->exists() ) {
					return Status::newFatal( "Form $form is invalid or is not declared as a wikipage" );
				} else {
					$conds[static::FIELD_PAGE_ID] = $formTitle->getArticleID();
				}
			} else {
				return Status::newFatal( "Form $form does not exist" );
			}
		}
		if ( $maxRev > 0 ) {
			$conds[] = static::FIELD_REV_ID . "<= $maxRev";
		}

		if( empty( $conds ) ) {
			$conds = [
				static::FIELD_REV_ID . "> 0 "
			];
		}

		$res = $this->db->delete(
			static::TABLE,
			$conds
		);
		if ( $res ) {
			return Status::newGood();
		}
		return Status::newFatal( 'Database action failed' );
	}

	/**
	 * @param Title $title
	 * @param string $ts
	 * @return int|null if no revision can be found
	 */
	public function getRevisionForTime( $title, $ts ) {
		$row = $this->db->selectRow(
			static::TABLE,
			[ static::FIELD_REV_ID ],
			[
				static::FIELD_PAGE_ID => $title->getArticleID(),
				static::FIELD_APPLIES_FROM . "<= $ts"
			],
			__METHOD__,
			[ 'ORDER BY' => static::FIELD_APPLIES_FROM . " DESC" ]
		);


		if ( $row ) {
			return (int) $row->{static::FIELD_REV_ID};
		}

		return null;
	}

	private function exists( $revId ) {
		$row = $this->db->selectRow(
			static::TABLE,
			'*',
			[ static::FIELD_REV_ID => $revId ]
		);
		return !!$row;
	}

	private function doUpdate( $revId, $ts ) {
		$res = $this->db->update(
			static::TABLE,
			[ static::FIELD_APPLIES_FROM => $ts ],
			[ static::FIELD_REV_ID => $revId ]
		);

		return $res;
	}

	private function doInsert( $pageId, $revId, $ts ) {
		$res = $this->db->insert(
			static::TABLE,
			[
				static::FIELD_REV_ID => $revId,
				static::FIELD_PAGE_ID => $pageId,
				static::FIELD_APPLIES_FROM => $ts
			]
		);

		return $res;
	}

}
