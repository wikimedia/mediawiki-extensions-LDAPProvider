<?php

namespace MediaWiki\Extension\LDAPProvider\Hook;

use MediaWiki\Installer\Hook\LoadExtensionSchemaUpdatesHook;

class LoadExtensionSchemaUpdates implements LoadExtensionSchemaUpdatesHook {

	/**
	 * @inheritDoc
	 */
	public function onLoadExtensionSchemaUpdates( $updater ) {
		$dbType = $updater->getDB()->getType();
		$base = dirname( __DIR__, 2 );

		$updater->addExtensionTable(
			'ldap_domains',
			"$base/db/$dbType/ldap.sql"
		);
	}
}
