<?php

namespace MediaWiki\Extension\Forms\Target;

use MediaWiki\Config\HashConfig;
use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\Forms\ITarget;
use MediaWiki\Json\FormatJson;
use MediaWiki\MediaWikiServices;
use MediaWiki\Status\Status;
use MediaWiki\User\User;
use Wikimedia\Rdbms\IDatabase;

class Database implements ITarget {
	public const TABLE = 'form_data';
	public const FIELD_ID = 'fd_id';
	public const FIELD_TITLE = 'fd_title';
	public const FIELD_FORM = 'fd_form';
	public const FIELD_DATA = 'fd_data';
	public const FIELD_USER = 'fd_user';
	public const FIELD_TIMESTAMP = 'fd_timestamp';

	/**
	 * @var IDatabase
	 */
	protected $db;

	/**
	 * @var User
	 */
	protected $user;

	/**
	 * @var string
	 */
	protected $form = '';

	/**
	 * @var string
	 */
	protected $title = '';

	/**
	 * @var int
	 */
	protected $id = 0;

	/**
	 * Database constructor.
	 * @param IDatabase $db
	 * @param User $user
	 * @param string $form
	 * @param string $title
	 * @param int $id
	 */
	protected function __construct( $db, $user, $form, $title = '', $id = 0 ) {
		$this->db = $db;
		$this->form = $form;
		$this->title = $title;
		$this->id = $id;
		$this->user = $user;
	}

	/**
	 * @param HashConfig $config
	 * @return ITarget
	 */
	public static function factory( HashConfig $config ) {
		if ( !$config->has( 'form' ) || !$config->get( 'form' ) ) {
			return null;
		}
		$id = 0;
		if ( $config->has( '_id' ) ) {
			$id = $config->get( '_id' );
		}
		$title = '';
		if ( $config->has( 'title' ) ) {
			$title = $config->get( 'title' );
		}

		$db = MediaWikiServices::getInstance()->getDBLoadBalancer()->getConnection(
			DB_PRIMARY
		);
		$user = RequestContext::getMain()->getUser();
		return new static( $db, $user, $config->get( 'form' ), $title, $id );
	}

	/**
	 * TODO: This probably does not work, it should not always set the latest rev
	 * @param array &$formsubmittedData
	 * @param bool|false $toLatest
	 */
	public function trySetFormRev( &$formsubmittedData, $toLatest = false ) {
		$definitionManager = MediaWikiServices::getInstance()->getService(
			"FormsDefinitionManager"
		);
		if ( $definitionManager->definitionIsWikipage( $this->form ) ) {
			$formsubmittedData['_form_rev'] = $definitionManager->getLatestDefinitionRev( $this->form );
		}
	}

	/**
	 *
	 * @param array $formsubmittedData
	 * @param string|null $summary
	 * @return Status
	 */
	public function execute( $formsubmittedData, $summary ) {
		if ( $this->id > 0 ) {
			if ( !$this->checkId() ) {
				return Status::newFatal( "forms-error-form-mismatch" );
			}
			$this->addIDToData( $formsubmittedData );
			$this->trySetFormRev( $formsubmittedData );
			$success = $this->update( $formsubmittedData );
		} else {
			$this->trySetFormRev( $formsubmittedData, true );
			$success = $this->insert( $formsubmittedData );
			if ( $success ) {
				$this->id = $this->db->insertId();
				$success = $this->insertIDtoData( $formsubmittedData );
			}
		}
		if ( $success ) {
			return Status::newGood( [
				'id' => $this->id
			] );
		}
		return Status::newFatal( "forms-error-target-db-save-failed" );
	}

	/**
	 * @return bool
	 */
	private function checkId() {
		$row = $this->db->selectRow(
			static::TABLE,
			[ static::FIELD_FORM ],
			[ static::FIELD_ID => $this->id ],
			__METHOD__
		);

		// Form cannot change between saves, make sure form is still the same
		if ( $row && $row->{static::FIELD_FORM} === $this->form ) {
			return true;
		}

		return false;
	}

	/**
	 * @param array $formsubmittedData
	 * @return bool
	 */
	private function update( array $formsubmittedData ) {
		return $this->db->update(
			static::TABLE,
			$this->getDataForDB( $formsubmittedData ),
			[ static::FIELD_ID => $this->id ]
		);
	}

	/**
	 * @param array $formsubmittedData
	 * @return bool
	 */
	private function insert( array $formsubmittedData ) {
		return $this->db->insert(
			static::TABLE,
			$this->getDataForDB( $formsubmittedData ),
			__METHOD__
		);
	}

	/**
	 * Helper function that updates the DB data with
	 * the row id of the newly inserted data
	 *
	 * @param array $formsubmittedData
	 * @return bool
	 */
	private function insertIDToData( $formsubmittedData ) {
		$this->addIdToData( $formsubmittedData );
		return $this->update( $formsubmittedData );
	}

	/**
	 * Add id property to the formdata
	 * @param array &$formsubmittedData
	 */
	private function addIDToData( &$formsubmittedData ) {
		$formsubmittedData['_id'] = $this->id;
	}

	/**
	 * @param array $formsubmittedData
	 * @return array
	 */
	private function getDataForDB( $formsubmittedData ) {
		return [
			static::FIELD_FORM => $this->form,
			static::FIELD_TITLE => $this->title,
			static::FIELD_USER => $this->user->isAnon() === false ? $this->user->getID() : 0,
			static::FIELD_TIMESTAMP => wfTimestamp( TS_MW ),
			static::FIELD_DATA => FormatJson::encode( $formsubmittedData )
		];
	}

	/**
	 * Action that occurs after form instance is saved
	 *
	 * Can be overridden by specifying target.afterAction in form definition
	 *
	 * Example - redirecting to a page:
	 * return [
	 *    "type" => "redirect",
	 *    "url" => Title::newMainPage()->getLocalUrl()
	 * ]
	 *
	 * @return array|false
	 */
	public function getDefaultAfterAction() {
		return false;
	}
}
