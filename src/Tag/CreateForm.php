<?php
namespace MediaWiki\Extension\Forms\Tag;

class CreateForm extends FormTag {
	/** @var array  */
	protected $sizes = [
		'small' => "50%",
		'medium' => "75%",
		'large' => "100%"
	];

	/** @var array  */
	protected $data = [];

	public function handle() {
		$this->parser->getOutput()->updateCacheExpiry(0);
		$out = $this->parser->getOutput();

		$this->parseData();

		$forms = $this->parseForm();
		if ( empty( $forms ) ) {
			return $this->error( 'forms-error-no-form-specified' );
		}

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

		return explode( '|', $this->args['form'] );
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
