<?php

namespace MediaWiki\Extension\LDAPProvider\Tests\PreSearchUsernameModifier;

use MediaWiki\Extension\LDAPProvider\PreSearchUsernameModifier\RemoveSpaces;
use PHPUnit\Framework\TestCase;

class RemoveSpacesTest extends TestCase {

	/**
	 * @covers \MediaWiki\Extension\LDAPProvider\PreSearchUsernameModifier\RemoveSpaces::modify
	 * @return void
	 */
	public function testModify() {
		$modifier = new RemoveSpaces();

		$modifiedUsername = $modifier->modify( 'Some User' );

		$this->assertEquals( 'SomeUser', $modifiedUsername );
	}

	/**
	 * @covers \MediaWiki\Extension\LDAPProvider\PreSearchUsernameModifier\RemoveSpaces::factory
	 * @return void
	 */
	public function testFactory() {
		$modifier = RemoveSpaces::newInstance();

		$this->assertInstanceOf( RemoveSpaces::class, $modifier );
	}
}
