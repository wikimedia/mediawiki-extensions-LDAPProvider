<?php

namespace MediaWiki\Extension\LDAPProvider\Tests;

use HashConfig;
use MediaWiki\Extension\LDAPProvider\UserDomainStore;
use MediaWikiIntegrationTestCase;

/**
 * @group Database
 */
class UserDomainStoreTest extends MediaWikiIntegrationTestCase {

	protected function setUp(): void {
		$this->tablesUsed[] = 'ldap_domains';
		parent::setUp();

		$this->db->insert( 'ldap_domains', [
			'domain' => 'SOMEDOMAIN',
			'user_id' => self::getTestSysop()->getUser()->getId()
		] );
	}

	/**
	 * @covers MediaWiki\Extension\LDAPProvider\UserDomainStore::getDomainForUser
	 */
	public function testGetDomainForUser() {
		$store = new UserDomainStore(
			$this->getServiceContainer()->getDBLoadBalancer()
		);
		$domain = $store->getDomainForUser( self::getTestSysop()->getUser() );

		$this->assertEquals(
			'somedomain', $domain, 'Should deliver the domain'
		);
	}

	/**
	 * @covers MediaWiki\Extension\LDAPProvider\UserDomainStore::setDomainForUser
	 */
	public function testSetDomainForUser() {
		$store = new UserDomainStore(
			$this->getServiceContainer()->getDBLoadBalancer()
		);
		$store->setDomainForUser(
			self::getTestUser()->getUser(), 'ANOTHERDOMAIN'
		);
		$this->assertSelect(
			'ldap_domains',
			[ 'domain' ],
			[ 'user_id' => self::getTestUser()->getUser()->getId() ],
			[
				[ 'ANOTHERDOMAIN' ]
			]
		);
	}

	/**
	 * @covers MediaWiki\Extension\LDAPProvider\UserDomainStore::getDomainForUser
	 */
	public function testGetDomainForUserWithDefaultDomainSuccess() {
		$store = new UserDomainStore(
			$this->getServiceContainer()->getDBLoadBalancer(),
			new HashConfig( [
				'DefaultDomain' => 'SOMEOTHERDOMAIN'
			] )
		);
		$domain = $store->getDomainForUser( self::getTestUser()->getUser() );

		$this->assertEquals(
			'someotherdomain', $domain, 'Should deliver the default domain'
		);
	}

	/**
	 * @covers MediaWiki\Extension\LDAPProvider\UserDomainStore::getDomainForUser
	 */
	public function testGetDomainForUserWithDefaultDomainFail() {
		$store = new UserDomainStore(
			$this->getServiceContainer()->getDBLoadBalancer()
		);
		$domain = $store->getDomainForUser( self::getTestUser()->getUser() );

		$this->assertNull(
			 $domain, 'Should deliver the `null`'
		);
	}
}
