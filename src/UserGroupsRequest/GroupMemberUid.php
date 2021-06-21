<?php

namespace MediaWiki\Extension\LDAPProvider\UserGroupsRequest;

use MediaWiki\Extension\LDAPProvider\ClientConfig;
use MediaWiki\Extension\LDAPProvider\EscapedString;
use MediaWiki\Extension\LDAPProvider\GroupList;
use MediaWiki\Extension\LDAPProvider\UserGroupsRequest;

class GroupMemberUid extends UserGroupsRequest {

	/**
	 * @param string $username to get the groups for
	 * @return GroupList
	 */
	public function getUserGroups( $username ) {
		$userDN = new EscapedString( $this->ldapClient->getUserDN( $username, 'uid' ) );
		$userUid = $this->ldapClient->getUsername();
		$baseDN = $this->config->get( ClientConfig::GROUP_BASE_DN );
		$dn = 'dn';

		if ( $baseDN === '' ) {
			$baseDN = null;
		}
		if ( $this->config->get( ClientConfig::NESTED_GROUPS ) ) {
			$groups = $this->ldapClient->search(
				"(memberUid:1.2.840.113556.1.4.1941:=$userUid)",
				$baseDN, [ $dn ]
			);
		} else {
			$groups = $this->ldapClient->search(
				"(&(objectclass=posixGroup)(memberUid=$userUid))",
				$baseDN, [ $dn ]
			);
		}
		$ret = [];
		foreach ( $groups as $key => $value ) {
			if ( is_int( $key ) ) {
				$ret[] = $value[$dn];
			}
		}

		// add group from gidnumber to groups list if exists
		$userInfo = $this->ldapClient->getUserInfo( $userUid );
		if ( array_key_exists( 'gidnumber', $userInfo ) ) {
			$gidNumber = $userInfo['gidnumber'];
			$gidGroup = $this->ldapClient->search(
				"(&(objectclass=posixGroup)(gidnumber=$gidNumber))",
				$baseDN, [ $dn ]
			);
			// add group if it was found
			if ( array_key_exists( 0, $gidGroup ) ) {
				$ret[] = $gidGroup[0][$dn];
			}
		}

		return new GroupList( $ret );
	}

}
