<?php

namespace MediaWiki\Extension\Forms\Target;

use HashConfig;
use MediaWiki\Extension\Forms\ITarget;
use TextContent;
use Title;
use WikiPage;

class Template extends TitleTarget {
	/** @var Title */
	protected $template;

	/**
	 * @param HashConfig $config
	 * @return ITarget
	 */
	public static function factory( HashConfig $config ) {
		$instance = parent::factory( $config );

		$template = $config->get( 'template' );
		$templateTitle = Title::newFromText( $template, NS_TEMPLATE );
		$instance->setTemplate( $templateTitle );

		return $instance;
	}

	/**
	 * @param Title $templateTitle
	 */
	public function setTemplate( Title $templateTitle ) {
		$this->template = $templateTitle;
	}

	/**
	 * @return string
	 */
	protected function getPageFormat() {
		return '';
	}

	/**
	 * @return string
	 */
	protected function getDataForContent() {
		$templateData = [];
		$formData = [];
		$unsortedData = [];

		$templateFields = $this->getTemplateFields();
		foreach ( $this->data as $field => $value ) {
			if ( in_array( $field, $this->reservedFields ) ) {
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

	/**
	 * @param array $data
	 * @return string
	 */
	private function getFormTag( array $data ) {
		$tag = '<formMeta ';
		$values = $this->assocToStringValues( $data );
		$tag .= implode( ' ', $values );
		$tag .= '/>';

		return $tag;
	}

	/**
	 * @param array $data
	 * @return string
	 */
	private function getTemplateCall( array $data ) {
		$values = $this->assocToStringValues( $data );
		$imploded = implode( "\n|", $values );
		$templateName = $this->template->getDBkey();
		return "{{{$templateName}\n|{$imploded}}}";
	}

	/**
	 * @param array $data
	 * @return string
	 */
	private function getUnsortedFields( array $data ) {
		$text = '';
		foreach ( $data as $key => $value ) {
			$text .= "$key: $value\n";
		}

		return $text;
	}

	/**
	 * @param array $data
	 * @return array
	 */
	private function assocToStringValues( array $data ) {
		$values = [];
		foreach ( $data as $key => $value ) {
			$values[] = "$key=$value";
		}

		return $values;
	}

	/**
	 * @return array
	 */
	private function getTemplateFields() {
		if ( $this->template->exists() === false ) {
			return [];
		}
		// TODO: Gotta be better way
		$wp = WikiPage::factory( $this->template );
		$content = $wp->getContent();
		$text = ( $content instanceof TextContent ) ? $content->getText() : '';
		$matches = [];
		preg_match_all( '/{{{(.*?)(\|.*?|)}}}/', $text, $matches );
		if ( isset( $matches[1] ) ) {
			return $matches[1];
		}

		return [];
	}
}
