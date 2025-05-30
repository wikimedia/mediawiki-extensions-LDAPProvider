<?php

namespace MediaWiki\Extension\LDAPProvider;

use Exception;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Wikimedia\AtEase\AtEase;

/**
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */

class PlatformFunctionWrapper implements LoggerAwareInterface {

	/** @var resource */
	private $linkID;

	/**
	 * @var self[]
	 */
	private static $conn = [];

	/** @var LoggerInterface */
	private $logger = null;

	public function __construct() {
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
	 * Set the value of the given option
	 * @link http://php.net/manual/en/function.ldap-set-option.php
	 * @param int $option
	 * The parameter option can be one of:
	 * Option                       Type    Available since
	 * LDAP_OPT_DEREF               integer
	 * LDAP_OPT_SIZELIMIT           integer
	 * LDAP_OPT_TIMELIMIT           integer
	 * LDAP_OPT_NETWORK_TIMEOUT     integer PHP 5.3.0
	 * LDAP_OPT_PROTOCOL_VERSION    integer
	 * LDAP_OPT_ERROR_NUMBER        integer
	 * LDAP_OPT_REFERRALS           bool
	 * LDAP_OPT_RESTART             bool
	 * LDAP_OPT_HOST_NAME           string
	 * LDAP_OPT_ERROR_STRING        string
	 * LDAP_OPT_MATCHED_DN          string
	 * LDAP_OPT_SERVER_CONTROLS     array
	 * LDAP_OPT_CLIENT_CONTROLS     array
	 *
	 * LDAP_OPT_SERVER_CONTROLS and LDAP_OPT_CLIENT_CONTROLS require a
	 * list of controls, this means that the value must be an array of
	 * controls. A control consists of an oid identifying the control,
	 * an optional value, and an optional flag for criticality. In PHP
	 * a control is given by an array containing an element with the
	 * key oid and string value, and two optional elements. The
	 * optional elements are key value with string value and key
	 * iscritical with boolean value.  iscritical defaults to FALSE if
	 * not supplied. See draft-ietf-ldapext-ldap-c-api-xx.txt for
	 * details. See also the second example below.
	 *
	 * @param mixed $newval The new value for the specified option
	 * @return bool
	 */
	public function setOption( $option, $newval ) {
		$this->logger->debug( "ldap_set_option( \$linkID, \$option = $option, "
			. "\$newval = $newval );"
		);
		$ret = \ldap_set_option( $this->linkID, $option, $newval );
		$this->logger->debug( "# returns " . ( $ret ? "true" : "false" ) );
		return $ret;
	}

	/**
	 * Bind to LDAP directory
	 * @link http://php.net/manual/en/function.ldap-bind.php
	 * @param string|null $bindRDN [optional]
	 * @param string|null $bindPassword [optional]
	 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
	 * @since 4.0
	 * @since 5.0
	 */
	public function bind( $bindRDN = null, $bindPassword = null ) {
		if ( !$this->linkID ) {
			throw new Exception( "Nothing to bind with!" );
		}
		$this->logger->debug( "ldap_bind( \$linkID, \$bindRDN = '$bindRDN', "
			. "\$bindPassword = 'XXXX' );"
		);
		AtEase::suppressWarnings();
		$ret = \ldap_bind( $this->linkID, $bindRDN, $bindPassword );
		AtEase::restoreWarnings();
		$this->logger->debug( "# returns " . ( $ret ? "true" : "false" ) );
		return $ret;
	}

	/**
	 * Return the LDAP error message of the last LDAP command
	 * @link http://php.net/manual/en/function.ldap-error.php
	 * @return string string error message.
	 */
	public function error() {
		$this->logger->debug( "ldap_error( \$linkID ); "
		);
		$ret = \ldap_error( $this->linkID );
		$this->logger->debug( "# returns $ret" );
		return $ret;
	}

	/**
	 * Return the LDAP error number of the last LDAP command
	 * @link http://php.net/manual/en/function.ldap-errno.php
	 * @return int Return the LDAP error number of the last LDAP
	 * command for this link.
	 */
	public function errno() {
		$this->logger->debug( "ldap_errno( \$linkID ); "
		);
		$ret = \ldap_errno( $this->linkID );
		$this->logger->debug( "# returns $ret" );
		return $ret;
	}

	/**
	 * Start TLS
	 * @link http://php.net/manual/en/function.ldap-start-tls.php
	 * @return bool
	 */
	public function startTLS() {
		$this->logger->debug( "ldap_start_tls( \$linkID ); "
		);
		$ret = \ldap_start_tls( $this->linkID );
		$this->logger->debug( "# returns " . ( $ret ? "true" : "false" ) );
		return $ret;
	}

	/**
	 * Search LDAP tree
	 * @link http://php.net/manual/en/function.ldap-search.php
	 * @param string $baseDN The base DN for the directory.
	 * @param string $filter The search filter can be simple or
	 * advanced, using boolean operators in the format described in
	 * the LDAP documentation (see the Netscape Directory SDK for full
	 * information on filters).
	 * @param array|null $attributes An array of the required attributes,
	 * e.g. array("mail", "sn", "cn").  Note that the "dn" is always
	 * returned irrespective of which attributes types are
	 * requested. Using this parameter is much more efficient than the
	 * default action (which is to return all attributes and their
	 * associated values).  The use of this parameter should therefore
	 * be considered good practice.
	 * @param int|null $attrsonly [optional] Should be set to 1 if only
	 * attribute types are wanted. If set to 0 both attributes types
	 * and attribute values are fetched which is the default
	 * behaviour.
	 * @param int|null $sizelimit [optional] Enables you to limit the count
	 * of entries fetched. Setting this to 0 means no limit.  This
	 * parameter can NOT override server-side preset sizelimit. You
	 * can set it lower though.  Some directory server hosts will be
	 * configured to return no more than a preset number of
	 * entries. If this occurs, the server will indicate that it has
	 * only returned a partial results set. This also occurs if you
	 * use this parameter to limit the count of fetched entries.
	 * @param int|null $timelimit [optional] Sets the number of seconds how
	 * long is spend on the search. Setting this to 0 means no limit.
	 * This parameter can NOT override server-side preset
	 * timelimit. You can set it lower though.
	 * @param int|null $deref [optional] Specifies how aliases should be
	 * handled during the search. It can be one of the following:
	 * LDAP_DEREF_NEVER - (default) aliases are never
	 * dereferenced.
	 * @return resource|bool FALSE on error.
	 */
	public function search(
		$baseDN, $filter, ?array $attributes = null,
		$attrsonly = null, $sizelimit = null, $timelimit = null,
		$deref = null
	) {
		$this->logger->debug( "ldap_search( \$linkID, \$baseDN = '$baseDN', "
			. "\$filter = '$filter', \$attributes = [ '"
			. implode( "', '", $attributes )
			. "' ], \$attrsonly = $attrsonly, "
			. "\$sizelimit = $sizelimit, \$timelimit = $timelimit, "
			. "\$deref = $deref ); "
		);
		AtEase::suppressWarnings();
		$ret = \ldap_search(
			$this->linkID, $baseDN, $filter, $attributes, $attrsonly,
			$sizelimit, $timelimit, $deref
		);
		AtEase::restoreWarnings();
		$retDbg = "a resource";
		if ( $ret === false ) {
			$retDbg = "an error (" . \ldap_error( $this->linkID ) . ")";
		}
		$this->logger->debug( "# returns $retDbg" );
		return $ret;
	}

	/**
	 * Get all result entries
	 * @link http://php.net/manual/en/function.ldap-get-entries.php
	 * @param resource $resultID result identifier?
	 * @return array a complete result information in a
	 * multi-dimensional array on success and FALSE on error.  The
	 * structure of the array is as follows.  The attribute index is
	 * converted to lowercase. (Attributes are case-insensitive for
	 * directory servers, but not when used as array indices.)
	 *
	 * return_value["count"]     number of entries in the result
	 * return_value[0]           refers to the details of first entry
	 * return_value[i]["dn"]     DN of the ith entry in the result
	 * return_value[i]["count"]  number of attributes in ith entry
	 * return_value[i][j]        NAME of the jth attribute in the ith entry
	 *                           in the result
	 * return_value[i]["attribute"]["count"] number of values for
	 *                           attribute in ith entry
	 * return_value[i]["attribute"][j]       jth value of attribute in ith entry
	 */
	public function getEntries( $resultID ) {
		$this->logger->debug( "ldap_get_entries( \$linkID, \$resultID ); "
		);
		$ret = \ldap_get_entries( $this->linkID, $resultID );
		$this->logger->debug( "# returns: " . var_export( $ret, true ) );
		return $ret;
	}

	/**
	 * Connect to an LDAP server
	 * @link http://php.net/manual/en/function.ldap-connect.php
	 * @param string|null $uri [optional] This field supports using a
	 * full LDAP URI of the form ldap://hostname:port or
	 * ldaps://hostname:port for SSL encryption.  Note that
	 * hostname:port is not a supported LDAP URI as the schema is
	 * missing.
	 * @return resource a positive LDAP link identifier when the
	 * provided hostname/port combination or LDAP URI seems
	 * plausible. It's a syntactic check of the provided parameters
	 * but the server(s) will not be contacted! If the syntactic check
	 * fails it returns <b>FALSE</b>.  When OpenLDAP 2.x.x is used,
	 * <b>ldap_connect</b> will always return a resource as it does
	 * not actually connect but just initializes the connecting
	 * parameters. The actual connect happens with the next calls to
	 * ldap_* funcs, usually with <b>ldap_bind</b>.  </p> <p> If no
	 * arguments are specified then the link identifier of the already
	 * opened link will be returned.
	 */
	public function connect( $uri = null ) {
		if ( $this->linkID ) {
			throw new Exception( "already connected" );
		}
		$this->logger->debug( "ldap_connect( \$uri = '$uri' ); "
		);
		$this->linkID = \ldap_connect( $uri );
		if ( $this->linkID === false ) {
			throw new Exception( "$uri is not a valid LDAP URI" );
		}
		$this->logger->debug( "# __METHOD__ returns a link id" );
		return $this->linkID;
	}

	/**
	 *
	 * @param string|null $uri
	 * @return PlatformFunctionWrapper
	 */
	public static function getConnection( $uri = null ) {
		$uri = $uri ?: '';
		if ( !isset( self::$conn[$uri] ) ) {
			$ldap = new self;
			$ldap->connect( $uri );
			self::$conn[$uri] = $ldap;
		}
		return self::$conn[$uri];
	}

	/**
	 * Escape a string for use in an LDAP filter or DN
	 *
	 * @param string $value to escape
	 * @param string|null $ignore characters to ignore
	 * @param int $flags context: LDAP_ESCAPE_FILTER or LDAP_ESCAPE_DN
	 * @return string
	 */
	public function escape( $value, $ignore = null, $flags = 0 ) {
		$this->logger->debug( "ldap_escape( \$value = '$value', "
			. "\$ignore = '$ignore', \$flags = $flags );"
		);
		$ret = \ldap_escape( $value, $ignore, $flags );
		$this->logger->debug( "# returns $ret" );
		return $ret;
	}

	/**
	 * Count the number of entries in a search.
	 *
	 * @param resource $result ldap result to count
	 * @return int
	 */
	public function count( $result ) {
		$this->logger->debug( "ldap_count_entries( \$linkiID, \$result = [resource] );"
		);
		$ret = \ldap_count_entries( $this->linkID, $result );
		$this->logger->debug( "# returns $ret" );
		return $ret;
	}

	/**
	 * Are we connected?
	 * @return bool
	 */
	public function isConnected() {
		return $this->linkID !== null;
	}
}
