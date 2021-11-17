<?php

namespace MediaWiki\Extension\Forms\Target;

use FormatJson;
use HashConfig;
use MediaWiki\Extension\Forms\ITarget;
use RequestContext;
use Status;
use User;

class Email implements ITarget {
	/**
	 * @var string
	 */
	protected $form;

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var User
	 */
	protected $user;

	/**
	 * @var array
	 */
	protected $receivers;

	/**
	 * @param array $receivers
	 * @param User $user
	 * @param string $form
	 * @param string $title
	 */
	protected function __construct( $receivers, $user, $form, $title = '' ) {
		$this->receivers = $receivers;
		$this->form = $form;
		$this->title = $title;
		$this->user = $user;
	}

	/**
	 * @param HashConfig $config
	 * @return ITarget
	 */
	public static function factory( HashConfig $config ) {
		if ( !$config->has( 'form' ) ) {
			return null;
		}
		$title = '';
		if ( $config->has( 'title' ) ) {
			$config->get( 'title' );
		}

		$receivers = $config->get( 'receivers' );
		$receivers = explode( "\n", $receivers );

		$user = RequestContext::getMain()->getUser();
		return new static( $receivers, $user, $config->get( 'form' ), $title );
	}

	/**
	 *
	 * @param array $formsubmittedData
	 * @param string|null $summary
	 * @return Status
	 */
	public function execute( $formsubmittedData, $summary ) {
		$mails = [];
		foreach ( $this->receivers as $receiver ) {
			$mails[] = new \MailAddress( $receiver );
		}
		\UserMailer::send(
			$mails,
			$GLOBALS['wgPasswordSender'],
			$this->title,
			FormatJson::encode( $formsubmittedData )
		);
		return Status::newGood();
	}

	/**
	 * Action that occurs after form instance is saved
	 *
	 * Can be overridden by specifying target.afterAction in form definition
	 *
	 * Example - redirecting to a page:
	 * return [
	 *    "type" => "redirect",
	 *    "url" => Title::newMainPage()->getLocalUrl()
	 * ]
	 *
	 * @return array|false
	 */
	public function getDefaultAfterAction() {
		return [
		 "type" => "redirect",
		 "url" => \Title::newMainPage()->getLocalUrl()
		];
	}
}
