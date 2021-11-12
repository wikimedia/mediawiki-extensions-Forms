<?php

namespace MediaWiki\Extension\Forms\Api;


use MediaWiki\Extension\Forms\ITarget;

class FormSubmit extends \ApiBase {

	/**
	 * Name of the form data is coming from
	 *
	 * @var string
	 */
	protected $form = '';

	/**
	 * @var ITarget
	 */
	protected $target;

	/**
	 * Array of data produced by the form
	 *
	 * @var array
	 */
	protected $data = [];

	/**
	 * Summary text for the edit
	 * @var string
	 */
	protected $summary = '';

	/**
	 * @var \Status
	 */
	protected $status;

	public function execute() {
		$this->status = \Status::newGood();

		$this->readInParameters();
		$this->sendToTarget();
		$this->returnResults();
	}

	protected function getAllowedParams() {
		return [
			'form' => [
				\ApiBase::PARAM_TYPE => 'string',
				\ApiBase::PARAM_REQUIRED => true
			],
			'target' => [
				\ApiBase::PARAM_TYPE => 'string',
				\ApiBase::PARAM_REQUIRED => true
			],
			'data' => [
				\ApiBase::PARAM_TYPE => 'string',
				\ApiBase::PARAM_REQUIRED => true
			],
			'summary' => [
				\ApiBase::PARAM_TYPE => 'string',
				\ApiBase::PARAM_REQUIRED => false
			]
		];
	}

	protected function getParameterFromSettings( $paramName, $paramSettings, $parseLimit ) {
		$value = parent::getParameterFromSettings( $paramName, $paramSettings, $parseLimit );
		if ( $paramName === 'target' ) {
			$decodedValue = \FormatJson::decode( $value, true );
			if ( !isset( $decodedValue['type'] ) ) {
				return null;
			}
			$targetType = $decodedValue['type'];
			$targets = \ExtensionRegistry::getInstance()->getAttribute(
				"FormsTargets"
			);

			if ( isset( $targets[$targetType] ) ) {
				$factory = $targets[$targetType];
				if ( is_callable( $factory ) ) {
					unset( $decodedValue['type'] );
					$config = new \HashConfig( array_merge(
						$decodedValue, [
							'form' => $this->form
						]
					) );
					return call_user_func_array( $factory, [ $config ] );
				}
			}
			return null;
		}
		if ( $paramName === 'data' ) {
			$decodedData = \FormatJson::decode( $value, true );
			return $decodedData;
		}
		return $value;
	}

	protected function readInParameters() {
		$this->form = $this->getParameter( 'form' );
		$this->target = $this->getParameter( 'target' );
		$this->data = $this->getParameter( 'data' );
		$this->summary = $this->getParameter( 'summary' );
	}

	protected function sendToTarget() {
		if ( $this->target instanceof ITarget === false ) {
			$this->status = \Status::newFatal( wfMessage( 'forms-api-form-submit-invalid-target' ) );
			return;
		}
		if ( empty( $this->data ) ) {
			$this->status = \Status::newFatal( wfMessage( 'forms-api-form-submit-empty-data' ) );
			return;
		}

		$this->status = $this->target->execute( $this->data, $this->summary );
	}

	protected function returnResults() {
		$result = $this->getResult();

		if ( $this->status->isGood() ) {
			$result->addValue( null, 'success', 1 );
			$result->addValue( null , 'result', $this->status->getValue() );
			$result->addValue( null , 'defaultAfterAction', $this->target->getDefaultAfterAction() );
		} else {
			$result->addValue( null, 'success', 0 );
			$result->addValue( null, 'error', $this->status->getMessage() );
		}

	}
}
