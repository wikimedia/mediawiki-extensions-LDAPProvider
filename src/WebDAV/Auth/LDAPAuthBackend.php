<?php

namespace MediaWiki\Extension\LDAPProvider\WebDAV\Auth;

use MediaWiki\Extension\LDAPProvider\ClientFactory;
use MediaWiki\Extension\LDAPProvider\Config;
use MediaWiki\Extension\LDAPProvider\LDAPNoDomainConfigException as NoDomain;
use MediaWiki\Extension\WebDAV\WebDAVCredentialAuthProvider;
use MediaWiki\MediaWikiServices;
use MWException;
use User;

class LDAPAuthBackend implements WebDAVCredentialAuthProvider {
	/**
	 * @inheritDoc
	 */
	public function getValidatedUser( $username, $password ) {
		$username = utf8_encode( $username );
		$password = utf8_encode( $password );

		list( $username, $domain ) = $this->parseUsername( $username );
		$user = MediaWikiServices::getInstance()->getUserFactory()->newFromName( $username );
		if ( !$user instanceof User ) {
			return null;
		}
		if ( $this->authenticate( $user, $password, $domain ) ) {
			return $user;
		}

		return null;
	}

	/**
	 * @param User $user
	 * @param string $password
	 * @param string $domain
	 * @return bool
	 * @throws MWException
	 */
	private function authenticate( User $user, $password, $domain ) {
		$ldapClient = null;
		try {
			$ldapClient = ClientFactory::getInstance()->getForDomain( $domain );
		} catch ( NoDomain $e ) {
			return false;
		}

		if ( $ldapClient->canBindAs( $user->getName(), $password ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get username and domain
	 *
	 * @param string $username
	 * @return array
	 */
	private function parseUsername( $username ) {
		if ( strpos( $username, '\\' ) !== false ) {
			$bits = explode( '\\', $username );
			return [
				$bits[1],
				$bits[0]
			];
		}
		if ( strpos( $username, '@' ) !== false ) {
			return explode( '@', $username );
		}

		$config = Config::newInstance();
		return [
			$username,
			$config->get( 'DefaultDomain' ),
		];
	}
}
