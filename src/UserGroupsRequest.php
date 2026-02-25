<?php

namespace MediaWiki\Extension\LDAPProvider;

use MediaWiki\Config\Config;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

abstract class UserGroupsRequest implements LoggerAwareInterface {

	/**
	 * @var Client
	 */
	protected $ldapClient = null;

	/**
	 * @var Config
	 */
	protected $config = null;

	/**
	 * @var string
	 */
	protected $groupBaseDN = '';

	/**
	 * @var LoggerInterface
	 */
	protected $logger = null;

	/**
	 * @param Client $ldapClient to use
	 * @param Config $config will be delivered here
	 */
	public function __construct( $ldapClient, Config $config ) {
		$this->ldapClient = $ldapClient;
		$this->config = $config;
		$this->groupBaseDN = $config->get( ClientConfig::GROUP_BASE_DN );
		$this->logger = new NullLogger();
	}

	/**
	 * @param LoggerInterface $logger
	 * @return void
	 */
	public function setLogger( LoggerInterface $logger ): void {
		$this->logger = $logger;
	}

	/**
	 *
	 * @param Client $ldapClient The client to be used
	 * @param Config $config The config to be used
	 * @return UserGroupsRequest
	 */
	public static function factory( $ldapClient, Config $config ) {
		$request = new static( $ldapClient, $config );
		return $request;
	}

	/**
	 * @param string $username to get the groups for
	 * @return GroupList
	 */
	abstract public function getUserGroups( $username );
}
