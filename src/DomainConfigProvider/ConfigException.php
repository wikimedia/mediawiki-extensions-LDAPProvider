<?php
/*
 * Copyright (C) 2021  NicheWork, LLC
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301, USA.
 *
 * @author Mark A. Hershberger <mah@nichework.com>
 */

namespace MediaWiki\Extension\LDAPProvider\DomainConfigProvider;

use MWException;

class ConfigException extends MWException {
	/**
	 * @param string $msg Localisation string
	 * @param mixed ...$args any parameters for the message
	 */
	public function __construct( $msg, ...$args ) {
		parent::__construct(
			wfMessage( $msg )->params( ...$args )->plain()
		);
	}
}
