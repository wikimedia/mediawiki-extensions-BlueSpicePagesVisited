<?php

namespace BlueSpice\PagesVisited\Data;

use BlueSpice\Data\NoWriterException;
use BlueSpice\Services;

class Store extends \BlueSpice\WhoIsOnline\Data\Store {

	/**
	 *
	 * @return Reader
	 */
	public function getReader() {
		return new Reader(
			Services::getInstance()->getDBLoadBalancer()
		);
	}

	/**
	 *
	 * @throws NoWriterException
	 */
	public function getWriter() {
		throw new NoWriterException();
	}
}
