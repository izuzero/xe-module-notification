<?php
/*! Copyright (C) Eunsoo Lee. All rights reserved. */

namespace Notification\Entity\Item;
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
			return $this->handle('delete', $this->keystring);
		}

		return $this->handle('put', array($this->keystring, $value, $expire));
	}

	public function isValid($modified = 0)
	{
		return $this->handle('isValid', array($this->keystring, $modified));
	}

	public function invalidate()
	{
		return $this->handle('invalidateGroupKey', 'notification');
	}

	public static function delete($group, $key = NULL)
	{
		$oCache = new self($group, $key);

		return $oCache->set(NULL);
	}
}

/* End of file Cache.php */
/* Location: ./modules/notification/classes/entity/Item/Cache.php */
