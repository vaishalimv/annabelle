<?php
namespace Drc\Mobileapp\Api\Customer;

interface CustomAddressInterface{
	/**
	 * Returns greeting message to user
	 *
	 * @api
	 * @param string $name Users name.
	 * @return string Greeting message with users name.
	 */
	public function name($name);

	/**
	 * POST for attribute api
	 * @param mixed $param
	 * @return array
	 */

	 public function addressUpdate($params);
}