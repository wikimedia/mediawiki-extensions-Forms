<?php

namespace MediaWiki\Extension\Forms\Target;

use DataPreprocessor;
use HashConfig;
use MailAddress;
use MediaWiki\Extension\Forms\ITarget;
use MediaWiki\MediaWikiServices;
use MediaWiki\User\User;
use Message;
use RequestContext;
use Status;
use UserMailer;

class Email implements ITarget {
	/**
	 * @var string
	 */
	protected $form;

	/**
	 * @var string
	 */
	protected $subject;

	/**
	 * @var Message
	 */
	protected $body;

	/**
	 * @var User
	 */
	protected $user;

	/**
	 * @var array
	 */
	protected $receivers;

	/**
	 * @var array
	 */
	protected $senders;

	/**
	 * @var array
	 */
	protected $receiverEmails;

	/**
	 * @var DataPreprocessor
	 */
	protected $preprocessor;

	/** @var MediaWikiServices */
	protected $services = null;

	/**
	 * @param array $receivers
	 * @param string $subject
	 * @param string $body
	 * @param User $user
	 * @param string $form
	 * @param array $receiverEmails
	 * @param array $senders
	 * @param DataPreprocessor $preprocessor
	 */
	protected function __construct(
		$receivers,
		$subject,
		$body,
		$user,
		$form,
		$receiverEmails,
		$senders,
		$preprocessor ) {
		$this->receivers = $receivers;
		$this->subject = $subject;
		$this->body = $body;
		$this->form = $form;
		$this->user = $user;

		$this->receiverEmails = $receiverEmails;
		$this->senders = $senders;
		$this->preprocessor = $preprocessor;
		$this->services = MediaWikiServices::getInstance();
	}

	/**
	 * @param HashConfig $config
	 * @return ITarget|null
	 */
	public static function factory( HashConfig $config ) {
		if ( !$config->has( 'form' ) ) {
			return null;
		}

		$receivers = $config->get( 'receivers' );
		$receivers = explode( "\n", $receivers );

		$subject = $config->get( 'subject' );
		$body = $config->get( 'body' );

		$user = RequestContext::getMain()->getUser();

		$services = MediaWikiServices::getInstance();
		$mainConfig = $services->getMainConfig();
		$receiverEmails = $mainConfig->get( 'FormsTargetEMailRecipients' );
		$senders = $mainConfig->get( 'PasswordSender' );

		$preprocessor = $services->getService( 'FormsDataPreprocessor' );

		return new static(
			$receivers,
			$subject,
			$body,
			$user,
			$config->get( 'form' ),
			$receiverEmails,
			$senders,
			$preprocessor
		);
	}

	/**
	 *
	 * @param array $formsubmittedData
	 * @param string|null $summary
	 * @return Status
	 */
	public function execute( $formsubmittedData, $summary ) {
		$mails = $this->getReceivers();
		if ( !$mails ) {
			return Status::newFatal( 'forms-error-target-mail-no-mail' );
		}

		$status = UserMailer::send(
			$mails,
			new MailAddress( $this->senders ),
			$this->getMailSubject( $formsubmittedData ),
			$this->getMailBody( $formsubmittedData )
		);

		if ( $status->isOK() ) {
			return Status::newGood( [
				'id' => 0,
				'data' => $formsubmittedData
			] );
		} else {
			return Status::newFatal( 'forms-error-target-mail-send-failed' );
		}
	}

	/**
	 *
	 * @return array
	 */
	private function getReceivers() {
		$mails = [];
		$userFactory = $this->services->getUserFactory();
		foreach ( $this->receivers as $receiver ) {
			$address = $this->receiverEmails[ $receiver ];

			if ( in_array( $address, $this->receiverEmails ) ) {
				if ( $this->isUser( $address ) ) {
					$user = $userFactory->newFromName( $address );
					$addressFromUser = MailAddress::newFromUser( $user );
					$mails[] = $addressFromUser;
				}
				if ( $this->isMail( $address ) ) {
					$mails[] = new MailAddress( $address );
				}
			}
		}
		return $mails;
	}

	/**
	 *
	 * @return false
	 */
	public function getDefaultAfterAction() {
		return false;
	}

	/**
	 *
	 * @param array $formData
	 * @return string
	 */
	private function getMailSubject( $formData ) {
		$mailbody = $this->preprocessor->preprocess( $formData, $this->subject, $this->user );
		return $mailbody;
	}

	/**
	 *
	 * @param array $formData
	 * @return string
	 */
	private function getMailBody( $formData ) {
		$mailbody = $this->preprocessor->preprocess( $formData, $this->body, $this->user );
		return $mailbody;
	}

	/**
	 *
	 * @param string $username
	 * @return bool
	 */
	private function isUser( $username ) {
		$user = $this->services->getUserFactory()->newFromName( $username );
		if ( $user instanceof User && $user->isRegistered() && $user->isEmailConfirmed() ) {
			return true;
		}
		return false;
	}

	/**
	 *
	 * @param string $address
	 * @return bool
	 */
	private function isMail( $address ) {
		$matches = [];
		$isMail = preg_match(
			'([a-z0-9_.-]+([a-z0-9_.-]+)*\@[a-z0-9_-]+([a-z0-9_.-]+)*.[a-z]+)',
			$address,
			$matches
		);
		if ( $isMail ) {
			return true;
		}
		return false;
	}
}
