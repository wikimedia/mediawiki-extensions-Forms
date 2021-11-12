<?php

namespace MediaWiki\Extension\Forms\Target;

use MediaWiki\Extension\Forms\ITarget;
use HashConfig;
use MediaWiki\MediaWikiServices;
use Wikimedia\Rdbms\IDatabase;
use FormatJson;
use User;
use RequestContext;
use Status;

class Database implements ITarget {
	const TABLE = 'form_data';
	const FIELD_ID = 'fd_id';
	const FIELD_TITLE = 'fd_title';
	const FIELD_FORM = 'fd_form';
	const FIELD_DATA = 'fd_data';
	const FIELD_USER = 'fd_user';
	const FIELD_TIMESTAMP = 'fd_timestamp';


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

	public static function factory( HashConfig $config ) {
		if ( !$config->has( 'form' ) ) {
			return null;
		}
		$id = 0;
		if ( $config->has( '_id' ) ) {
			$config->get( '_id' );
		}
		$title = '';
		if ( $config->has( 'title' ) ) {
			$config->get( 'title' );
		}

		$db = MediaWikiServices::getInstance()->getDBLoadBalancer()->getConnection(
			DB_MASTER
		);
		$user = RequestContext::getMain()->getUser();
		return new static( $db, $user, $config->get( 'form' ), $title, $id );
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
			$success = $this->update( $formsubmittedData );
		} else {
			$success = $this->insert( $formsubmittedData );
			$this->id = $this->db->insertId();
		}
		if ( $success ) {
			return Status::newGood( [
				'id' => $this->id
			] );
		}
		return Status::newFatal( "forms-error-target-db-save-failed" );
	}

	private function checkId() {
		$row = $this->db->selectRow(
			static::TABLE,
			[ static::FIELD_FORM ],
			[ static::FIELD_ID => $this->id ]
		);

		// Form cannot change between saves, make sure form is still the same
		if ( $row && $row->{static::FIELD_FORM} === $this->form ) {
			return true;
		}

		return false;
	}

	private function update( array $formsubmittedData ) {
		return $this->db->update(
			static::TABLE,
			$this->getDataForDB( $formsubmittedData ),
			[ static::FIELD_ID => $this->id ]
		);
	}

	private function insert( array $formsubmittedData ) {
		return $this->db->insert(
			static::TABLE,
			$this->getDataForDB( $formsubmittedData )
		);
	}

	private function getDataForDB( $formsubmittedData) {
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
	 *]
	 *
	 * @return array|false
	 */
	public function getDefaultAfterAction() {
		return false;
	}
}
