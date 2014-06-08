<?php

namespace Mp3\External;

interface ProviderInterface
{
	public function __construct($file_path);
	/**
	 * @return array()
	 */
	public function execute();
}
