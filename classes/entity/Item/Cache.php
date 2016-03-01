<?php
/*! Copyright (C) Eunsoo Lee. All rights reserved. */

namespace Notification\Entity\Item;
use InvalidArgumentException;
use CacheHandler;

class Cache
{
	private $handler = NULL;

	protected $keystring = NULL;

	public function __construct($group, $key = NULL)
	{
		$this->handler = CacheHandler::getInstance();

		if (!$key)
		{
			$key = $group;
			$group = NULL;
		}

		$key = md5($key);
		$group = md5($group);

		if (!$key)
		{
			throw new InvalidArgumentException('Cache handle failed.');
		}

		if ($group)
		{
			$key = sprintf('%s:%s', $group, $key);
		}

		$this->keystring = $this->handler->getGroupKey('notification', $key);
	}

	public function __toString()
	{
		return $this->keystring;
	}

	protected function handle($event, $args = NULL)
	{
		if (!$this->handler->isSupport())
		{
			return FALSE;
		}

		if (!is_array($args))
		{
			$args = array($args);
		}

		return call_user_func_array(array($this->handler, $event), $args);
	}

	public function get($modified = 0)
	{
		return $this->handle('get', array($this->keystring, $modified));
	}

	public function set($value, $expire = 0)
	{
		if (is_null($value))
		{
			return $this->delete();
		}

		return $this->handle('put', array($this->keystring, $value, $expire));
	}

	public function delete()
	{
		return $this->handle('delete', $this->keystring);
	}

	public function isValid($modified = 0)
	{
		return $this->handle('isValid', array($this->keystring, $modified));
	}

	public static function invalidate()
	{
		return CacheHandler::getInstance()->invalidateGroupKey('notification');
	}
}

/* End of file Cache.php */
/* Location: ./modules/notification/classes/entity/Item/Cache.php */
