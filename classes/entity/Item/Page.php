<?php
/*! Copyright (C) Eunsoo Lee. All rights reserved. */

namespace Notification\Entity\Item;
require_once __DIR__ . '/Item.php';
require_once __DIR__ . '/PageHandler.php';

class Page extends Item
{
	private $handler = NULL;

	protected $list = array();

	public function __construct(array $list, $page = 1, $paginate = 10, $countWith = 20)
	{
		if (!isset($page))
		{
			$page = 1;
		}

		if (!isset($paginate))
		{
			$paginate = 10;
		}

		if (!isset($countWith))
		{
			$countWith = 20;
		}

		$this->list = $list;
		$this->initialize($page, $paginate, $countWith);
	}

	public function initialize($page = 1, $paginate = 10, $countWith = 20)
	{
		$this->set('countWith', $countWith = (int)$countWith);
		$this->set('count', $count = count($this->list));

		$pages = $count > 0 ? floor(($count - 1) / $countWith) + 1 : 1;

		$this->handler = new PageHandler($page, $pages, $paginate);
	}

	public function get($key)
	{
		return $this->handler->existOf($key) ? $this->handler->get($key) : parent::get($key);
	}

	public function gets(array $keys)
	{
		return array_merge(parent::gets($keys), $this->handler->gets($keys));
	}

	public function getAll()
	{
		return array_merge(parent::getAll(), $this->handler->getAll());
	}

	public function existOf($key)
	{
		return parent::existOf($key) || $this->handler->existOf($key);
	}

	public function setPage($page)
	{
		return $this->handler->setPage($page);
	}

	public function prevPage()
	{
		return $this->handler->prevPage();
	}

	public function nextPage()
	{
		return $this->handler->nextPage();
	}

	public function getNavigator()
	{
		return clone $this->handler;
	}

	public function getList()
	{
		$page = $this->get('page');
		$countWith = $this->get('countWith');

		return $page > 0 ? array_slice($this->list, ($page - 1) * $countWith, $countWith, TRUE) : array();
	}
}

/* End of file Page.php */
/* Location: ./modules/notification/classes/entity/Item/Page.php */
