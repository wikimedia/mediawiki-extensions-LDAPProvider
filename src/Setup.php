<?php

namespace MediaWiki\Extension\LDAPProvider;

class Setup {

	/**
	 * @SuppressWarnings( SuperGlobals )
	 * @return void
	 */
	public static function init() {
		// Set dummy config for WMF CI environment
		if ( !empty( $GLOBALS['wgWikimediaJenkinsCI'] ) ) {
			$GLOBALS['LDAPProviderDomainConfigs'] =
				__DIR__ . '/../docs/ldapprovider.json';
		}
	}

}
