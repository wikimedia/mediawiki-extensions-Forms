<?php

namespace MediaWiki\Extension\Forms\Api;

use MediaWiki\Api\ApiBase;
use MediaWiki\Extension\Forms\ITarget;
use MediaWiki\Json\FormatJson;
use MediaWiki\Status\Status;
use Wikimedia\ParamValidator\ParamValidator;

class FormSubmit extends ApiBase {

	/**
	 * @var string|null
	 */
	protected $form = null;

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
	 * @var Status
	 */
	protected $status;

	public function execute() {
		$this->status = Status::newGood();

		$this->readInParameters();
		$this->sendToTarget();
		$this->returnResults();
	}

	/**
	 * @return array
	 */
	protected function getAllowedParams() {
		return [
			'form' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true
			],
			'target' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true
			],
			'data' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true
			],
			'summary' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => false
			]
		];
	}

	/**
	 * Using the settings determine the value for the given parameter
	 *
	 * @param string $paramName Parameter name
	 * @param array|mixed $paramSettings Default value or an array of settings
	 *  using PARAM_* constants.
	 * @param bool $parseLimit Whether to parse and validate 'limit' parameters
	 * @return mixed Parameter value
	 */
	protected function getParameterFromSettings( $paramName, $paramSettings, $parseLimit ) {
		$value = parent::getParameterFromSettings( $paramName, $paramSettings, $parseLimit );
		if ( $paramName === 'target' ) {
			$decodedValue = FormatJson::decode( $value, true );
			if ( !isset( $decodedValue['type'] ) ) {
				return null;
			}

			return $decodedValue;
		}
		if ( $paramName === 'data' ) {
			return FormatJson::decode( $value, true );
		}

		return $value;
	}

	protected function readInParameters() {
		$this->form = $this->getParameter( 'form' );
		$this->data = $this->getParameter( 'data' );
		$this->target = $this->makeTarget( $this->getParameter( 'target' ) );
		$this->summary = $this->getParameter( 'summary' );
	}

	protected function sendToTarget() {
		if ( $this->target instanceof ITarget === false ) {
			$this->status = Status::newFatal(
				$this->msg( 'forms-api-form-submit-invalid-target' )
			);
			return;
		}
		if ( empty( $this->data ) ) {
			$this->status = Status::newFatal(
				$this->msg( 'forms-api-form-submit-empty-data' )
			);
			return;
		}

		$this->status = $this->target->execute( $this->data, $this->summary );
	}

	protected function returnResults() {
		$result = $this->getResult();

		if ( $this->status->isGood() ) {
			$result->addValue( null, 'success', 1 );
			$result->addValue( null, 'result', $this->status->getValue() );
			$result->addValue(
				null, 'defaultAfterAction', $this->target->getDefaultAfterAction()
			);
		} else {
			$result->addValue( null, 'success', 0 );
			$result->addValue( null, 'error', [ 'info' => $this->status->getMessage() ] );
		}
	}

	/**
	 * @return string
	 */
	public function needsToken() {
		return 'csrf';
	}

	/**
	 * @param array $data
	 * @return ITarget|null
	 */
	private function makeTarget( $data ) {
		$targetType = $data['type'];
		$targets = \ExtensionRegistry::getInstance()->getAttribute(
			"FormsTargets"
		);

		if ( isset( $targets[$targetType] ) ) {
			$factory = $targets[$targetType];
			if ( is_callable( $factory ) ) {
				$id = $data['_id'] ?? $this->data['_id'] ?? null;
				if ( $id ) {
					$data['_id'] = $id;
				}
				unset( $data['type'] );
				$config = new \HashConfig( array_merge(
					$data, [
						'form' => $this->form
					]
				) );

				return call_user_func_array( $factory, [ $config ] );
			}
		}
		return null;
	}
}
