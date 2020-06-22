<?php

namespace MediaWiki\Extension\LDAPProvider\UserGroupsRequest;

use MediaWiki\Extension\LDAPProvider\UserGroupsRequest;
use MediaWiki\Extension\LDAPProvider\UserInfoRequest;
use MediaWiki\Extension\LDAPProvider\GroupList;

class UserMemberOf extends UserGroupsRequest {

	/**
	 * @param string $username to get the groups for
	 * @return GroupList
	 */
	public function getUserGroups( $username ) {
		$userInfoRequest = new UserInfoRequest( $this->ldapClient, $this->config );
		$res = $userInfoRequest->getUserInfo( $username );

		$groupDNs = $res['memberof'];
		/**
		 * With some LDAP backends this field may be just a string, if only one
		 * group is assigned
		 */
		if ( !is_array( $groupDNs ) ) {
			$groupDNs = [ $groupDNs ];
		}

		return new GroupList( $groupDNs );
	}

}
