<?php
/*! Copyright (C) Eunsoo Lee. All rights reserved. */

namespace Notification\Entity\Item;
require_once __DIR__ . '/Item.php';

class PageHandler extends Item
{
	public function __construct($page, $pages, $paginate = 10)
	{
		$this->initialize($page, $pages, $paginate);
	}

	public function initialize($page, $pages, $paginate = 10)
	{
		$this->set('pages', $pages = (int)$pages);
		$this->set('paginate', $paginate = (int)$paginate);
		$this->set('page', $page = (int)$page);
		$this->set('offset', $page);

		if ($page < 0 || $page > $pages)
		{
			$this->set('page', 0);
		}

		$startpoint = $page - floor($paginate / 2);

		if ($startpoint < 1)
		{
			$startpoint = 1;
		}

		$endpoint = $startpoint + $paginate - 1;

		if ($pages < $endpoint)
		{
			if ($pages > $paginate)
			{
				$startpoint -= $endpoint - $pages;
			}

			$endpoint = $pages;
		}

		$this->set('startpoint', $startpoint);
		$this->set('endpoint', $endpoint);
	}

	public function setPage($page)
	{
		$this->initialize($page, $this->get('pages'), $this->get('paginate'));

		return $this->get('page');
	}

	public function prevPage()
	{
		$this->initialize($this->get('offset') - 1, $this->get('pages'), $this->get('paginate'));

		return $this->get('page');
	}

	public function nextPage()
	{
		$this->initialize($this->get('offset') + 1, $this->get('pages'), $this->get('paginate'));

		return $this->get('page');
	}
}

/* End of file PageHandler.php */
/* Location: ./modules/notification/classes/Entity/Item/PageHandler.php */
