<?php

namespace MediaWiki\Extension\LDAPProvider\Tests\PreSearchUsernameModifier;

use MediaWiki\Extension\LDAPProvider\PreSearchUsernameModifier\GenericCallback;
use PHPUnit\Framework\TestCase;

class GenericCallbackTest extends TestCase {

	/**
	 * @covers \MediaWiki\Extension\LDAPProvider\PreSearchUsernameModifier\GenericCallback::modify
	 * @return void
	 */
	public function testModify() {
		$modifier = new GenericCallback( static function () {
			return 'TEST';
		} );

		$modifiedUsername = $modifier->modify( 'Some User' );

		$this->assertEquals( 'TEST', $modifiedUsername );
	}
}
