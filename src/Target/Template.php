<?php

namespace MediaWiki\Extension\Forms\Target;

use MediaWiki\Config\HashConfig;
use MediaWiki\Content\Content;
use MediaWiki\Content\TextContent;
use MediaWiki\Extension\Forms\Content\FormDataContent;
use MediaWiki\Extension\Forms\INonNativeTarget;
use MediaWiki\Extension\Forms\ITarget;
use MediaWiki\Storage\PageUpdater;
use MediaWiki\Title\Title;

class Template extends TitleTarget implements INonNativeTarget {
	/** @var Title */
	protected $template;

	/** @var array|null */
	private ?array $leftoverData;

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

		if ( $this->targetTitle->exists() ) {
			$text = $this->getPageText( $this->targetTitle );
			$text = $this->replaceTemplate( $text, $this->getTemplateCall( $templateData ) );
		} else {
			$text = $this->getTemplateCall( $templateData );
		}

		if ( !empty( $unsortedData ) ) {
			$this->leftoverData = $unsortedData;
		}

		return $text;
	}

	protected function setUpdaterContent( PageUpdater $updater ) {
		$formDataContent = new FormDataContent( json_encode( array_merge( [
			'form' => $this->data['_form'],
			'template' => $this->template->getPrefixedDBkey(),
			'_target' => 'template',
			'form_rev' => $this->data['_form_rev']
		], $this->leftoverData ?? [] ) ) );
		$this->leftoverData = null;
		$updater->setContent( FORM_DATA_REVISION_SLOT, $formDataContent );
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
		$wp = $this->services->getWikiPageFactory()->newFromTitle( $this->template );
		$content = $wp->getContent();
		$text = ( $content instanceof TextContent ) ? $content->getText() : '';
		$matches = [];
		preg_match_all( '/{{{(.*?)(\|.*?|)}}}/', $text, $matches );
		if ( isset( $matches[1] ) ) {
			return $matches[1];
		}

		return [];
	}

	/**
	 * @param Content $content
	 * @return array
	 */
	public function getFormDataFromContent( Content $content ): array {
		$text = $content->getText();
		if ( $text === '' ) {
			return [];
		}
		$matches = $this->matchTemplate( $text );
		if ( isset( $matches[2] ) ) {
			$values = [];
			foreach ( $matches[2] as $match ) {
				$match = trim( $match, "\n| " );
				$parts = explode( '|', $match );
				foreach ( $parts as $part ) {
					$part = trim( $part );
					if ( str_contains( $part, '=' ) ) {
						[ $key, $value ] = explode( '=', $part, 2 );
						$values[$key] = trim( $value );
					}
				}
			}
			return $values;
		}
		return [];
	}

	/**
	 * @param string $text
	 * @return array
	 */
	private function matchTemplate( string $text ): array {
		$templateNames = [];
		if ( $this->template->getNamespace() === NS_TEMPLATE ) {
			$templateNames[] = $this->template->getDBkey();
			$templateNames[] = $this->template->getText();
		} elseif ( $this->template->getNamespace() === NS_MAIN ) {
			$templateNames[] = ':' . $this->template->getText();
			$templateNames[] = ':' . $this->template->getPrefixedDBkey();

		} else {
			$templateNames[] = $this->template->getPrefixedDBkey();
			$templateNames[] = $this->template->getPrefixedText();
		}
		$matches = [];
		preg_match_all( '/{{(' . implode( '|', $templateNames ) . ')(.*?)}}/s', $text, $matches );
		return $matches;
	}

	/**
	 * @param Title $title
	 * @return string
	 */
	private function getPageText( Title $title ): string {
		$wikipage = $this->services->getWikiPageFactory()->newFromTitle( $title );
		$content = $wikipage->getContent();
		if ( !( $content instanceof TextContent ) ) {
			return '';
		}
		return $content->getText();
	}

	/**
	 * @param string $text
	 * @param string $template
	 * @return string
	 */
	private function replaceTemplate( string $text, string $template ): string {
		$matches = $this->matchTemplate( $text );
		if ( isset( $matches[0] ) ) {
			foreach ( $matches[0] as $match ) {
				$text = str_replace( $match, $template, $text );
			}
		}
		return $text;
	}

}
