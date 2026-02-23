<?php

namespace MediaWiki\Extension\LDAPProvider;

use MediaWiki\User\User;
use MediaWiki\User\UserIdentity;
use Wikimedia\Rdbms\ILoadBalancer;

class UserDomainStore {

	/**
	 * @var ILoadBalancer
	 */
	protected $loadbalancer = null;

	/**
	 * @var Config
	 */
	protected $config = null;

	/**
	 * @param ILoadBalancer $loadbalancer to use
	 * @param Config|null $config
	 */
	public function __construct( ILoadBalancer $loadbalancer, $config = null ) {
		$this->loadbalancer = $loadbalancer;
		$this->config = $config ?? Config::newInstance();
	}

	/**
	 * @param User $user to get domain for
	 * @return string|null
	 */
	public function getDomainForUser( User $user ) {
		$userId = $user->getId();
		if ( $userId != 0 ) {
			$dbr = $this->loadbalancer->getConnection( DB_REPLICA );
			$row = $dbr->selectRow(
				'ldap_domains',
				[ 'domain' ],
				[ 'user_id' => $userId ],
				__METHOD__ );

			if ( $row ) {
				return strtolower( $row->domain );
			}
		}

		$defaultDomain = $this->config->get( Config::DEFAULT_DOMAIN );
		if ( $defaultDomain ) {
			return strtolower( $defaultDomain );
		}

		return null;
	}

	/**
	 * @param UserIdentity $user to set
	 * @param string $domain to set user to
	 * @return bool
	 */
	public function setDomainForUser( $user, $domain ) {
		$userId = $user->getId();
		if ( $userId != 0 ) {
			$dbw = $this->loadbalancer->getConnection( DB_PRIMARY );
			$dbw->delete(
				'ldap_domains',
				[
					'user_id' => $userId
				],
				__METHOD__
			);
			return $dbw->insert(
				'ldap_domains',
				[
					'domain' => $domain,
					'user_id' => $userId
				],
				__METHOD__
			);
		}
		return false;
	}
}
