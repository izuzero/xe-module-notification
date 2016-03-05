<?php
/*! Copyright (C) Eunsoo Lee. All rights reserved. */

namespace Notification\Entity\Item;

interface ItemInterface
{
	public function get($key);
	public function gets(array $keys);
	public function getAll();
	public function set($key, $val);
	public function sets(array $args);
	public function truncate();
	public function existOf($key);
}

/* End of file ItemInterface.php */
/* Location: ./modules/notification/classes/Entity/Item/ItemInterface.php */
