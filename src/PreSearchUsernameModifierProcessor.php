<?php

namespace MediaWiki\Extension\LDAPProvider;

use InvalidArgumentException;
use LogicException;
use UnexpectedValueException;

class PreSearchUsernameModifierProcessor {

	/**
	 *
	 * @var array
	 */
	private $factoryCallbackRegistry = [];

	/**
	 *
	 * @param array $factoryCallbackRegistry
	 */
	public function __construct( $factoryCallbackRegistry ) {
		$this->factoryCallbackRegistry = $factoryCallbackRegistry;
	}

	/**
	 *
	 * @param string $username
	 * @param string[] $modifierKeys
	 * @return string
	 */
	public function process( $username, $modifierKeys ) {
		$modifiers = $this->makeModifiers( $modifierKeys );
		$modifiedUsername = $username;
		foreach ( $modifiers as $modifier ) {
			$modifiedUsername = $modifier->modify( $modifiedUsername );
		}
		return $modifiedUsername;
	}

	/**
	 *
	 * @param string[] $modifierKeys
	 * @return IPreSearchUsernameModifier[]
	 */
	private function makeModifiers( $modifierKeys ) {
		$modifiers = [];
		foreach ( $modifierKeys as $modifierKey ) {
			$modifiers[] = $this->makePreSearchUsernameModifier( $modifierKey );
		}
		return $modifiers;
	}

	/**
	 * @param string $modifierKey
	 * @return IPreSearchUsernameModifier
	 * @throws InvalidArgumentException
	 * @throws LogicException
	 * @throws UnexpectedValueException
	 */
	private function makePreSearchUsernameModifier( $modifierKey ) {
		if ( !isset( $this->factoryCallbackRegistry[$modifierKey] ) ) {
			throw new InvalidArgumentException(
				"No factory callback set for "
				. "pre-search-username-modifier-key ' $modifierKey'"
			);
		}
		if ( !is_callable( $this->factoryCallbackRegistry[$modifierKey] ) ) {
			throw new LogicException(
				"Provided factory callback for "
				. "pre-search-username-modifier-key ' $modifierKey' is not callable!"
			);
		}

		$modifier = call_user_func( $this->factoryCallbackRegistry[$modifierKey] );
		if ( !$modifier instanceof IPreSearchUsernameModifier ) {
			throw new UnexpectedValueException(
				"Factory callback for pre-search-username-modifier-key ' $modifierKey' "
				. "did not return a valid 'IPreSearchUsernameModifier' object!"
			);
		}
		return $modifier;
	}
}
