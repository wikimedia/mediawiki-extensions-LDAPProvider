<?php

namespace MediaWiki\Extension\LDAPProvider\UserGroupsRequest;

use MediaWiki\Extension\LDAPProvider\ClientConfig;
use MediaWiki\Extension\LDAPProvider\EscapedString;
use MediaWiki\Extension\LDAPProvider\GroupList;
use MediaWiki\Extension\LDAPProvider\UserGroupsRequest;
use MWException;

class Configurable extends UserGroupsRequest {

	/**
	 * @param string $username to get the groups for
	 * @return GroupList
	 */
	public function getUserGroups( $username ) {
		$baseDN = $this->config->get( ClientConfig::GROUP_BASE_DN );
		$dn = 'dn';

		if ( $baseDN === '' ) {
			$baseDN = null;
		}

		$ret = [];
		$objectClass = $this->config->get( ClientConfig::GROUP_OBJECT_CLASS );
		if ( empty( trim( $objectClass ) ) ) {
			throw new MWException( sprintf(
				"Parameter %s must be set when configurable groups request is used",
				ClientConfig::GROUP_OBJECT_CLASS
			) );
		}
		$groupAttribute = $this->config->get( ClientConfig::GROUP_ATTRIBUTE );

		$callback = $this->getGroupAttributeValueCallback();
		$groupAttributeValue = $callback( $username );

		$groups = $this->ldapClient->search(
			"(&(objectclass=$objectClass)($groupAttribute=$groupAttributeValue))",
			$baseDN, [ $dn ]
		);

		foreach ( $groups as $key => $value ) {
			if ( is_int( $key ) ) {
				$ret[] = $value[$dn];
			}
		}
		return new GroupList( $ret );
	}

	/**
	 * Returns callback function to get {@link ClientConfig::GROUP_ATTRIBUTE} value.
	 * With this callback, there could be different ways to calculate that "group attribute" value.
	 * For example, in some cases just username can be used, like that:
	 *
	 * <code>
	 * function groupAttributeValueCallback( $username ) {
	 * 		return new \MediaWiki\Extension\LDAPProvider\EscapedString( $username );
	 * }
	 * </code>
	 *
	 * Logic may be more complicated, that's just simple example.
	 *
	 * @return callable
	 */
	private function getGroupAttributeValueCallback(): callable {
		$callback = function ( $username ) {
			return new EscapedString( $this->ldapClient->getUserDN( $username ) );
		};

		if ( $this->config->has( ClientConfig::GROUP_ATTRIBUTE_VALUE_CALLBACK ) ) {
			$customCallback = $this->config->get( ClientConfig::GROUP_ATTRIBUTE_VALUE_CALLBACK );
			if ( is_callable( $customCallback ) ) {
				$callback = $customCallback;
			}
		}

		return $callback;
	}
}
