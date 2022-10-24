<?php

namespace MediaWiki\Extension\Forms\Api;

use MediaWiki\Extension\Forms\DefinitionManager;
use MediaWiki\MediaWikiServices;
use Wikimedia\ParamValidator\ParamValidator;

class GetDefinitions extends \ApiBase {
	public const QUERY_TYPE_QUERY_AVAILABLE = 'query-available';
	public const QUERY_TYPE_GET_DEFINITION = 'get-definition';

	/**
	 * @var string
	 */
	protected $type;

	/**
	 * @var string
	 */
	protected $definitionType;

	/**
	 *
	 * @var string
	 */
	protected $validForTime;

	/**
	 * @var \Status
	 */
	protected $status;

	/**
	 * @var DefinitionManager
	 */
	protected $definitionManager;

	public function execute() {
		$this->status = \Status::newGood();
		$this->definitionManager = MediaWikiServices::getInstance()->getService(
			"FormsDefinitionManager"
		);

		$this->readInParameters();
		$this->dispatch();
		$this->returnResults();
	}

	/**
	 * @return array
	 */
	protected function getAllowedParams() {
		return [
			'type' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => false,
				ParamValidator::PARAM_DEFAULT => self::QUERY_TYPE_QUERY_AVAILABLE
			],
			'definitionType' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => false,
				ParamValidator::PARAM_DEFAULT => DefinitionManager::TYPE_CONCRETE
			],
			'name' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => false,
			],
			'validForTime' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => false,
				ParamValidator::PARAM_DEFAULT => ''
			]
		];
	}

	protected function readInParameters() {
		$this->type = $this->getParameter( 'type' );
	}

	protected function dispatch() {
		switch ( $this->type ) {
			case self::QUERY_TYPE_QUERY_AVAILABLE:
				 $this->getAvailableDefinitions();
				break;
			case self::QUERY_TYPE_GET_DEFINITION:
				$this->getDefinitionContent();
				break;
			default:
				$this->status = \Status::newGood();
		}
	}

	protected function returnResults() {
		$result = $this->getResult();

		if ( $this->status->isGood() ) {
			$result->addValue( null, 'success', 1 );
			$result->addValue( null, 'result', $this->status->getValue() );
		} else {
			$result->addValue( null, 'success', 0 );
			$result->addValue( null, 'error', $this->status->getMessage() );
		}
	}

	/**
	 *
	 */
	protected function getAvailableDefinitions() {
		$type = $this->getParameter( 'definitionType' );
		$this->status = \Status::newGood(
			$this->definitionManager->getDefinitionKeys( $type )
		);
	}

	/**
	 *
	 */
	protected function getDefinitionContent() {
		$name = $this->getParameter( 'name' );
		if ( !$name ) {
			$this->status = \Status::newFatal(
				$this->msg( 'forms-api-get-definitions-no-name' )
			);
		}
		if ( !$this->definitionManager->definitionExists( $name ) ) {
			$this->status = \Status::newFatal(
				$this->msg( 'forms-api-get-definitions-not-exist', $name )
			);
		}

		$validForTime = $this->getParameter( 'validForTime' );
		$this->status = \Status::newGood( [
			'definition' => $this->definitionManager->getDefinition( $name, $validForTime ),
			'lang' => $this->definitionManager->getDefinitionLang( $name )
		] );
	}
}
