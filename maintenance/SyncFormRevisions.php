<?php

$IP = dirname( dirname( dirname( __DIR__ ) ) );

require_once "$IP/maintenance/Maintenance.php";

class SyncFormRevisions extends Maintenance {

	public function __construct() {
		parent::__construct();
		$this->requireExtension( "Forms" );

		$this->addOption( 'quick', 'Skip count down' );
		$this->addOption( 'form', 'Form for which to sync revisions, "*" for all', true, true );
		$this->addOption( 'untilRev', 'Sync revisions until this form definition revision', false, true );
	}

	public function execute() {
		if ( !$this->hasOption( 'quick' ) ) {
			$this->countDown( 5 );
		}

		$this->output( 'Starting to sync revisions...' . PHP_EOL );
		$form = $this->getOption( 'form' );
		$maxRev = $this->getOption( 'untilRev', 0 );

		$revManager = \MediaWiki\MediaWikiServices::getInstance()->getService(
			'FormsRevisionManager'
		);

		$status = $revManager->syncRevs( $form, $maxRev );
		if ( $status->isOK() === false ) {
			return $this->output( "Error:" . $status->getMessage() . PHP_EOL );
		}
		$this->output( '...done' . PHP_EOL );
	}
}

$maintClass = 'SyncFormRevisions';
require_once RUN_MAINTENANCE_IF_MAIN;
