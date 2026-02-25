<?php

namespace MediaWiki\Extension\LDAPProvider\UserGroupsRequest;

use MediaWiki\Extension\LDAPProvider\ClientConfig;
use MediaWiki\Extension\LDAPProvider\GroupList;
use MediaWiki\Extension\LDAPProvider\UserGroupsRequest;

class UserGidNumber extends UserGroupsRequest {

	/**
	 * @param string $username to get the groups for
	 * @return GroupList
	 */
	public function getUserGroups( $username ) {
		$userInfo = $this->ldapClient->getUserInfo( $username );

		if ( !isset( $userInfo['gidnumber'] ) ) {
			$this->logger->debug(
				__METHOD__ . ": no 'gidnumber' attribute found for {username}, returning empty group list.",
				[ 'username' => $username ]
			);
			return new GroupList( [] );
		}

		$gid = $userInfo['gidnumber'];
		$dn = 'dn';
		$groupBaseDN = $this->config->get( ClientConfig::GROUP_BASE_DN );
		if ( $groupBaseDN === '' ) {
			$groupBaseDN = null;
		}

		$this->logger->debug(
			__METHOD__ . ': searching primary group by gidNumber={gid} for {username} in {baseDN}.',
			[ 'gid' => $gid, 'username' => $username, 'baseDN' => $groupBaseDN ?? 'null' ]
		);

		$groups = $this->ldapClient->search(
			"(&(objectclass=posixGroup)(gidnumber=$gid))",
			$groupBaseDN, [ $dn ]
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
