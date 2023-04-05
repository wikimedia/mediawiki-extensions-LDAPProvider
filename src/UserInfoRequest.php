<?php

namespace MediaWiki\Extension\LDAPProvider;

use Config;
use MWException;

class UserInfoRequest {

	/**
	 *
	 * @var Client
	 */
	protected $ldapClient = null;

	/**
	 *
	 * @var Config
	 */
	protected $config = null;

	/**
	 *
	 * @var string
	 */
	protected $userBaseDN = '';

	/**
	 *
	 * @var string
	 */
	protected $searchAttribute = '';

	/**
	 * @var string[]
	 */
	protected $userInfoAttributes = null;

	/**
	 * @param Client $ldapClient to use
	 * @param Config $config for retrieving config from
	 */
	public function __construct( Client $ldapClient, Config $config ) {
		$this->ldapClient = $ldapClient;
		$this->config = $config;
		$this->userBaseDN = $config->get( ClientConfig::USER_BASE_DN );
		$this->searchAttribute = $config->get(
			ClientConfig::USER_DN_SEARCH_ATTR
		);
		$this->userInfoAttributes = $config->get( ClientConfig::USER_INFO_ATTRIBUTES );
	}

	/**
	 * @param string $username to get info for
	 * @return array
	 */
	public function getUserInfo( $username ) {
		$escapedUserName = new EscapedString( $username );
		// We need to do a subbase search for the entry
		$filter = "({$this->searchAttribute}=$escapedUserName)";

		// We explicitly put "memberof" here because it's an operational
		// attribute in some servers.
		if ( !in_array( 'memberof', $this->userInfoAttributes ) ) {
			$this->userInfoAttributes[] = 'memberof';
		}

		$entry = $this->ldapClient->search(
			$filter,
			$this->userBaseDN,
			$this->userInfoAttributes
		);

		$count = $entry['count'];
		if ( $count == 0 ) {
			return [];
		}

		if ( $count > 1 ) {
			throw new MWException(
				wfMessage( "ldapprovider-more-than-one" )->params( $filter )->plain()
			);
		}

		$res = [];
		foreach ( $entry[0] as $key => $value ) {
			if ( $key === 'dn' ) {
				$res[$key] = $value;
			} elseif ( !is_int( $key ) && $key !== "count" ) {
				if ( $value['count'] === 1 ) {
					$res[$key] = $value[0];
				} else {
					$res[$key] = array_filter(
						$value,
						static function ( $thisKey ) {
							return is_int( $thisKey );
						},
						ARRAY_FILTER_USE_KEY
					);
				}
			}
		}

		return $res;
	}
}
