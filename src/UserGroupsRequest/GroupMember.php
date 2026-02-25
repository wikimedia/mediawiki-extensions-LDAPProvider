<?php

namespace MediaWiki\Extension\LDAPProvider\UserGroupsRequest;

use MediaWiki\Extension\LDAPProvider\ClientConfig;
use MediaWiki\Extension\LDAPProvider\EscapedString;
use MediaWiki\Extension\LDAPProvider\GroupList;
use MediaWiki\Extension\LDAPProvider\UserGroupsRequest;

class GroupMember extends UserGroupsRequest {

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

		$objectClass = $this->config->has( ClientConfig::GROUP_OBJECT_CLASS )
			? $this->config->get( ClientConfig::GROUP_OBJECT_CLASS )
			: 'group';

		$nested = $this->config->get( ClientConfig::NESTED_GROUPS );
		$this->logger->debug(
			__METHOD__ . ': searching groups for {username}'
			. ' (userDN={userDN}, groupBaseDN={baseDN}, objectClass={objectClass}, nestedGroups={nested})',
			[
				'username' => $username,
				'userDN' => (string)$userDN,
				'baseDN' => $baseDN ?? 'null',
				'objectClass' => $objectClass,
				'nested' => $nested ? 'true' : 'false',
			]
		);

		if ( $nested ) {
			if ( $objectClass !== 'group' ) {
				$this->logger->warning(
					__METHOD__ . ": nestedgroups with objectClass={objectClass} uses an"
					. " AD-specific matching rule (OID 1.2.840.113556.1.4.1941)"
					. " which is not supported on non-AD servers.",
					[ 'objectClass' => $objectClass ]
				);
			}
			$groups = $this->ldapClient->search(
				"(member:1.2.840.113556.1.4.1941:=$userDN)",
				$baseDN, [ $dn ]
			);
		} else {
			$groups = $this->ldapClient->search(
				"(&(objectclass=$objectClass)(member=$userDN))",
				$baseDN, [ $dn ]
			);
		}

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
