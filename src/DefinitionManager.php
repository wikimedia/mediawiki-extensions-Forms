<?php

namespace MediaWiki\Extension\Forms;

use MediaWiki\Content\TextContent;
use MediaWiki\Extension\Forms\Content\FormDefinitionContent;
use MediaWiki\Json\FormatJson;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;

class DefinitionManager {
	public const TYPE_ABSTRACT = 'abstract';
	public const TYPE_CONCRETE = 'concrete';
	public const TYPE_PARTIAL = 'partial';
	public const TYPE_SYSTEM = 'system';

	public const LANG_JS = 'js';
	public const LANG_JSON = 'json';

	public const SOURCE_ATTRIBUTE = 'attribute';
	public const SOURCE_WIKIPAGE = 'wikipage';

	/**
	 * @var array
	 */
	protected $definitions = [];

	/** @var MediaWikiServices */
	protected $services = null;

	public function __construct() {
		// Load all definitions registered
		$this->services = MediaWikiServices::getInstance();
		$this->loadDefinitions();
	}

	/**
	 * Checks if given definition exists
	 *
	 * @param string $definition
	 * @return bool
	 */
	public function definitionExists( $definition ) {
		return isset( $this->definitions[$definition] );
	}

	/**
	 * Returns definition by the given key
	 *
	 * @param string $definition
	 * @param string $validForTime Get definition as it was on timestamp
	 * @return string Definition at given ts or latest definition if versionining does not apply or is invalid
	 * @throws \MWException
	 */
	public function getDefinition( $definition, $validForTime = '' ) {
		if ( !$this->definitionExists( $definition ) ) {
			throw new \MWException( 'Definition does not exist' );
		}
		if ( $validForTime !== '' && $this->definitionIsWikipage( $definition ) ) {
			$title = $this->getTitleFromDefinitionName( $definition );
			if ( $title instanceof Title && $title->exists() ) {
				$revisionManager = $this->services->getService( 'FormsRevisionManager' );
				$revId = $revisionManager->getRevisionForTime( $title, $validForTime );
				if ( $revId ) {
					// TODO: This is duplicate code - not too bad, but could be better
					$rev = $this->services->getRevisionStore()->getRevisionById( $revId );
					if ( $title->getArticleID() === $rev->getPageId() ) {
						$content = $rev->getContent( 'main' );
						if ( $content instanceof FormDefinitionContent ) {
							return $content->getText();
						}
					}
				}
			}
		}

		return $this->definitions[$definition]['content'];
	}

	/**
	 * @param string $definitionName
	 * @return bool
	 */
	public function definitionIsWikipage( $definitionName ) {
		if ( $this->definitionExists( $definitionName ) === false ) {
			return false;
		}
		if ( $this->definitions[$definitionName]['source'] === static::SOURCE_WIKIPAGE ) {
			return true;
		}
		return false;
	}

	/**
	 * Get the latest Revision ID of the form definition,
	 * if form is defined as a wikipage
	 *
	 * @param string $definitionName
	 * @return int
	 */
	public function getLatestDefinitionRev( $definitionName ) {
		if ( !$this->definitionIsWikipage( $definitionName ) ) {
			return 0;
		}
		$defTitle = $this->getTitleFromDefinitionName( $definitionName );
		if ( !$defTitle instanceof Title || !$defTitle->exists() ) {
			return 0;
		}
		return $defTitle->getLatestRevID();
	}

	/**
	 * Get all registered definitions, grouped by type
	 *
	 * @return array
	 */
	public function getAllDefinitionKeys() {
		$names = [
			static::TYPE_ABSTRACT => [],
			static::TYPE_CONCRETE => [],
			static::TYPE_PARTIAL => [],
			static::TYPE_SYSTEM => []
		];
		foreach ( $this->definitions as $name => $info ) {
			$type = $info['type'];
			if ( !isset( $names[$type] ) ) {
				$names[$type] = [];
			}
			$names[$type][] = $name;
		}
		return $names;
	}

	/**
	 * Get definition keys for a given type
	 *
	 * @param string $type
	 * @return array
	 */
	public function getDefinitionKeys( $type = self::TYPE_CONCRETE ) {
		$names = [];
		foreach ( $this->definitions as $name => $info ) {
			if ( $info['type'] === $type ) {
				$names[] = $name;
			}
		}
		return $names;
	}

	/**
	 * Get the scripting lang of the definition
	 *
	 * @param string $name
	 * @return string
	 * @throws \MWException
	 */
	public function getDefinitionLang( $name ) {
		if ( !$this->definitionExists( $name ) ) {
			throw new \MWException( 'Definition does not exist' );
		}
		$info = $this->definitions[$name];
		return $info['lang'];
	}

	protected function loadDefinitions() {
		$this->loadFromAttribute();
		$this->loadFromContentModel();
	}

	/**
	 * @param string $name
	 * @return Title
	 */
	private function getTitleFromDefinitionName( $name ) {
		 // TODO: No hardcoded extension
		return Title::newFromText( "$name.form" );
	}

	/**
	 * Parses actual definition contents to determine the type
	 * @param string $definition
	 * @param string $lang
	 * @return string
	 */
	private function parseDefinitionType( $definition, $lang ) {
		if ( $lang === static::LANG_JS ) {
			$matches = [];
			$keywords = implode( '|', [
				static::TYPE_ABSTRACT,
				static::TYPE_PARTIAL,
				static::TYPE_SYSTEM
			] );
			preg_match_all( "/($keywords)((\s)*:(\s)*)(true)/i", $definition, $matches );
			if ( isset( $matches[1] ) && count( $matches[1] ) > 0 ) {
				if ( in_array( static::TYPE_ABSTRACT, $matches[1] ) ) {
					// If form is declared as abstract disregard all other type declarations
					// should not happen anyway, but just to make sure
					return static::TYPE_ABSTRACT;
				} elseif ( in_array( static::TYPE_PARTIAL, $matches[1] ) ) {
					return static::TYPE_PARTIAL;
				} else {
					return static::TYPE_SYSTEM;
				}
			}
			return static::TYPE_CONCRETE;
		} elseif ( $lang === static::LANG_JSON ) {
			$decoded = FormatJson::decode( $definition, 1 );
			if ( isset( $decoded[static::TYPE_ABSTRACT] ) && $decoded[static::TYPE_ABSTRACT] ) {
				return static::TYPE_ABSTRACT;
			}
			if ( isset( $decoded[static::TYPE_PARTIAL] ) && $decoded[static::TYPE_PARTIAL] ) {
				return static::TYPE_PARTIAL;
			}
			if ( isset( $decoded[static::TYPE_SYSTEM] ) && $decoded[static::TYPE_SYSTEM] ) {
				return static::TYPE_SYSTEM;
			}
			return static::TYPE_CONCRETE;
		}

		return static::TYPE_CONCRETE;
	}

	/**
	 * @param string $definition
	 * @param mixed $default
	 * @return mixed
	 */
	private function parseDefinitionLang( $definition, $default ) {
		$matches = [];
		$langs = implode( '|', [
			static::LANG_JS,
			static::LANG_JSON
		] );
		preg_match_all( "/(lang)((\s)*:(\s)*)($langs)/i", $definition, $matches );
		if ( isset( $matches[5] ) && count( $matches[5] ) > 0 ) {
			return $matches[5][0];
		}
		return $default;
	}

	protected function loadFromAttribute() {
		$attribute = \ExtensionRegistry::getInstance()->getAttribute(
			"FormsDefinitions"
		);

		foreach ( $attribute as $key => $definition ) {
			if ( $this->fileExists( $definition ) ) {
				$content = $this->getContent(
					$definition
				);

				$lang = $this->parseDefinitionLang( $content, static::LANG_JS );
				$this->definitions[$key] = [
					'type' => $this->parseDefinitionType( $content, $lang ),
					'source' => static::SOURCE_ATTRIBUTE,
					'lang' => $lang,
					'content' => $content
				];
			}
		}
	}

	protected function loadFromContentModel() {
		$pages = $this->getPages();
		$wikiPageFactory = $this->services->getWikiPageFactory();
		foreach ( $pages as $pageRow ) {
			$page = Title::newFromRow( $pageRow );
			$wikipage = $wikiPageFactory->newFromTitle( $page );
			$content = $wikipage->getContent();

			$name = $content->getTitleWithoutExtension( $page );
			$data = ( $content instanceof TextContent ) ? $content->getText() : '';
			$this->definitions[$name] = [
				'type' => $this->parseDefinitionType( $data, static::LANG_JSON ),
				'source' => static::SOURCE_WIKIPAGE,
				'lang' => static::LANG_JSON,
				'content' => $data
			];

		}
	}

	/**
	 * @return array
	 */
	protected function getPages() {
		$db = $this->services->getDBLoadBalancer()->getConnection( DB_REPLICA );
		$res = $db->select(
			'page',
			'*',
			[ 'page_content_model' => 'FormDefinition' ]
		);

		return $res;
	}

	/**
	 * @param string $definition
	 * @return bool
	 */
	protected function fileExists( $definition ) {
		return file_exists( $this->expandPath( $definition ) );
	}

		/**
		 * @param string $definition
		 * @return string
		 */
	protected function getContent( $definition ) {
		return file_get_contents( $this->expandPath( $definition ) );
	}

	/**
	 * @param string $definition
	 * @return string
	 */
	protected function expandPath( $definition ) {
		return dirname( dirname( __DIR__ ) ) . "/$definition";
	}
}
