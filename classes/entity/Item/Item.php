<?php
/*! Copyright (C) Eunsoo Lee. All rights reserved. */

namespace Notification\Entity\Item;

abstract class Item implements ItemInterface
{
	protected $variables = array();

	public function get($key)
	{
		return $this->variables[$key];
	}

	public function gets(array $keys)
	{
		$variables = array();

		foreach ($keys as $key)
		{
			$variables[$key] = $this->variables[$key];
		}

		return $variables;
	}

	public function getAll()
	{
		return $this->variables;
	}

	public function set($key, $val)
	{
		$this->variables[$key] = $val;
	}

	public function sets(array $args)
	{
		foreach ($args as $key => $val)
		{
			$this->variables[$key] = $val;
		}
	}

	public function truncate()
	{
		$this->variables = array();
	}

	public function existOf($key)
	{
		return array_key_exists($key, $this->variables);
	}
}

/* End of file Item.php */
/* Location: ./modules/notification/classes/entity/Item/Item.php */
