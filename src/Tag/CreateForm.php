<?php
namespace MediaWiki\Extension\Forms\Tag;

class CreateForm {
	protected $sizes = [
		'small' => "50%",
		'medium' => "75%",
		'large' => "100%"
	];

	/**
	 *
	 * @var string
	 */
	protected $input = '';

	/**
	 *
	 * @var array
	 */
	protected $args = [];

	/**
	 *
	 * @var \Parser
	 */
	protected $parser = null;

	/**
	 *
	 * @var \PPFrame
	 */
	protected $frame = null;

	/**
	 * @var array
	 */
	protected $data = [];

	/**
	 *
	 * @param string $input
	 * @param array $args
	 * @param \Parser $parser
	 * @param PPFrame $frame
	 * @return mixed
	 */
	public static function callback( $input, array $args, \Parser $parser, \PPFrame $frame ) {
		$taghandler = new static($input, $args, $parser, $frame);

		return $taghandler->handle();
	}

	/**
	 *
	 * @param string $input
	 * @param array $args
	 * @param \Parser $parser
	 * @param \PPFrame $frame
	 */
	public function __construct( $input, array $args, \Parser $parser, \PPFrame $frame ) {
		$this->input = $input;
		$this->args = $args;
		$this->parser = $parser;
		$this->frame = $frame;
	}

	public function handle() {
		$this->parser->getOutput()->updateCacheExpiry(0);
		$out = $this->parser->getOutput();

		$this->parseData();

		$forms = $this->parseForm();
		if ( empty( $forms ) ) {
			return $this->error( 'forms-error-no-form-specified' );
		}

		$typeMap = \ExtensionRegistry::getInstance()->getAttribute(
			"FormsTypeMap"
		);
		$out->addJsConfigVars('FormsTypeMap', $typeMap);
		$out->addModules('ext.forms.init');

		return $this->getFormContainer( $forms );
	}

	protected function getStyle() {
		if ( isset( $this->args['style'] ) ) {
			return $this->args['style'];
		}
		$size = $this->getSize();
		return "width: $size;";
	}

	protected function getSize() {
		$size = $this->sizes['large'];
		if ( isset( $this->args['size'] ) && isset( $this->sizes[$this->args['size']] ) ) {
			$size = $this->sizes[$this->args['size']];
		}
		return $size;
	}

	protected function parseForm() {
		if ( !isset( $this->args['form'] ) || empty( $this->args['form'] ) ) {
			return [];
		}

		$form = explode( '|', $this->args['form'] );
		return $form;
	}

	protected function error( $message ) {
		return \Html::element( 'div', [
			"class" => "ext-forms-tag-error"
		], wfMessage( $message )->plain() );
	}

	protected function getFormContainer( $forms ) {
		$data = [
			'class' => 'forms-form-container',
			'style' => $this->getStyle(),
			'data-data' => \FormatJson::encode( $this->data ),
			'data-action' => 'create'
		];

		if ( count( $forms ) > 1 ) {
			$data['data-form-picker'] = \FormatJson::encode( [
				'forms' => $forms,
				'autoSelectForm' => $this->autoSelectForm()
			] );
		} else {
			$data['data-form'] = $forms[0];
		}
		return \Html::element('div', $data );
	}

	protected function parseData() {
		if ( !isset( $this->args['data'] ) ) {
			return;
		}
		$data = $this->args['data'];
		$dataItems = explode( '|', $data );
		foreach ( $dataItems as $dataItem ) {
			$bits = explode( '=', $dataItem );
			if ( count( $bits ) !== 2 ) {
				continue;
			}
			$value = trim( array_pop( $bits ) );
			$field = trim( array_pop( $bits ) );
			$this->data[$field] = $value;
		}
	}

	protected function autoSelectForm() {
		if ( isset( $this->args['autoselectform'] ) ) {
			return true;
		}
		return false;
	}
}
