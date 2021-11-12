<?php

namespace MediaWiki\Extension\Forms\Target;

use HashConfig;
use Title;
use WikiPage;

class Template extends TitleTarget {
	/** @var Title  */
	protected $template;

	public static function factory( HashConfig $config ) {
		$instance = parent::factory( $config );

		$template = $config->get( 'template' );
		$templateTitle = Title::newFromText( $template, NS_TEMPLATE );
		$instance->setTemplate( $templateTitle );

		return $instance;
	}

	public function setTemplate( Title $templateTitle ) {
		$this->template = $templateTitle;
	}

	protected function getPageFormat() {
		return '';
	}

	protected function getDataForContent() {
		$templateData = [];
		$formData = [];
		$unsortedData = [];

		$templateFields = $this->getTemplateFields();
		foreach ( $this->data as $field => $value ) {
			if ( in_array( $field, $this->reservedFields  ) ){
				$formData[$field] = $value;
			} elseif ( in_array( $field, $templateFields ) || empty( $templateFields ) ) {
				$templateData[$field] = $value;
			} else {
				$unsortedData[$field] = $value;
			}
		}

		$text = $this->getFormTag( $formData );
		$text .= "\n\n" . $this->getTemplateCall( $templateData );
		if ( !empty( $unsortedData ) ) {
			$text .= "\n\n" . $this->getUnsortedFields( $unsortedData );
		}

		return $text;
	}

	private function getFormTag( array $data ) {
		$tag = '<formMeta ';
		$values = $this->assocToStringValues( $data );
		$tag .= implode( ' ', $values );
		$tag .= '/>';

		return $tag;
	}

	private function getTemplateCall( array $data ) {
		$values = $this->assocToStringValues( $data );
		$imploded = implode( "\n|", $values );
		$templateName = $this->template->getDBkey();
		return "{{{$templateName}\n|{$imploded}}}";
	}

	private function getUnsortedFields( array $data ) {
		$text = '';
		foreach ( $data as $key => $value ) {
			$text .= "$key: $value\n";
		}

		return $text;
	}

	private function assocToStringValues( array $data ) {
		$values = [];
		foreach ( $data as $key => $value ) {
			$values[] = "$key=$value";
		}

		return $values;
	}

	private function getTemplateFields() {
		if ( $this->template->exists() === false ) {
			return [];
		}
		//TODO: Gotta be better way
		$wp = WikiPage::factory( $this->template );
		$content = $wp->getContent();
		$text = $content->getNativeData();
		$matches = [];
		preg_match_all( '/{{{(.*?)(\|.*?|)}}}/', $text, $matches );
		if ( isset( $matches[1] ) ) {
			return $matches[1];
		}

		return [];
	}
}
