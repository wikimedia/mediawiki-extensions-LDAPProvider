<?php

namespace MediaWiki\Extension\LDAPProvider\UserGroupsRequest;

use MediaWiki\Extension\LDAPProvider\ClientConfig;
use MediaWiki\Extension\LDAPProvider\EscapedString;
use MediaWiki\Extension\LDAPProvider\GroupList;
use MediaWiki\Extension\LDAPProvider\UserGroupsRequest;

class GroupUniqueMember extends UserGroupsRequest {

	/**
	 * @param string $username to get the groups for
	 * @return GroupList
	 */
	public function getUserGroups( $username ) {
		$userDN = new EscapedString( $this->ldapClient->getUserDN( $username ) );
		$baseDN = $this->config->get( ClientConfig::GROUP_BASE_DN );
		$dn = 'dn';

		if ( $baseDN === '' ) {
			$baseDN = null;
		}

		$this->logger->debug(
			__METHOD__ . ': searching groups for {username} (userDN={userDN}, groupBaseDN={baseDN})',
			[
				'username' => $username,
				'userDN' => (string)$userDN,
				'baseDN' => $baseDN ?? 'null',
			]
		);

		$groups = $this->ldapClient->search(
			"(&(objectclass=groupOfUniqueNames)(uniqueMember=$userDN))",
			$baseDN, [ $dn ]
		);
		$ret = [];
		foreach ( $groups as $key => $value ) {
			if ( is_int( $key ) ) {
				$ret[] = $value[$dn];
			}
		}

		$this->logger->debug(
			__METHOD__ . ': found {count} group(s) for {username}.',
			[ 'count' => count( $ret ), 'username' => $username ]
		);

		return new GroupList( $ret );
	}
}
