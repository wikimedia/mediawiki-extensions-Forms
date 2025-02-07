<?php
namespace MediaWiki\Extension\Forms\Tag;

use MediaWiki\Html\Html;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\PPFrame;

abstract class FormTag {
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
	 * @var Parser
	 */
	protected $parser = null;

	/**
	 *
	 * @var PPFrame
	 */
	protected $frame = null;

	/**
	 *
	 * @param string $input
	 * @param array $args
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @return mixed
	 */
	public static function callback( $input, array $args, Parser $parser, PPFrame $frame ) {
		$tagHandler = new static( $input, $args, $parser, $frame );

		return $tagHandler->handle();
	}

	/**
	 *
	 * @param string $input
	 * @param array $args
	 * @param Parser $parser
	 * @param PPFrame $frame
	 */
	public function __construct( $input, $args, Parser $parser, PPFrame $frame ) {
		$this->input = $input;
		$this->args = $args;
		$this->parser = $parser;
		$this->frame = $frame;
	}

	abstract public function handle();

	/**
	 * @param string $message
	 * @return string
	 */
	protected function error( $message ) {
		return Html::element( 'div', [
			"class" => "ext-forms-tag-error"
		], wfMessage( $message )->plain() );
	}
}
