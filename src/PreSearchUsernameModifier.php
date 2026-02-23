<?php

namespace MediaWiki\Extension\LDAPProvider;

abstract class PreSearchUsernameModifier implements IPreSearchUsernameModifier {

	/**
	 * @return IPreSearchUsernameModifier
	 */
	public static function newInstance() {
		// @phan-suppress-next-line PhanTypeInstantiateAbstractStatic
		return new static();
	}
}
