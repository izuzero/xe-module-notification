<?php
/*! Copyright (C) Eunsoo Lee. All rights reserved. */

namespace Notification\Entity\Item;
require_once 'Item.php';
use stdClass;
use FileHandler;
use ModuleHandler;

class Plugin extends Item
{
	private static $list = array();

	protected $oCache = NULL;

	public function __construct($name = NULL)
	{
		if ($name && is_string($name))
		{
			$this->initialize($name);
		}
	}

	public function __toString()
	{
		return $this->get('name');
	}

	public function initialize($name, array $args = array())
	{
		if (!($name && is_string($name)))
		{
			return FALSE;
		}

		$this->set('name', $name);
		$this->oCache = new Cache('plugins', $name);

		if (!in_array($name, self::getList()))
		{
			return $this->expire();
		}

		if (count($args))
		{
			$args['name'] = $name;
			$this->sets($args);
		}
		else
		{
			$res = $this->oCache->get();

			if (!(is_array($res) && count($res)))
			{
				if (!$this->restore())
				{
					return FALSE;
				}

				$req = new stdClass();
				$req->name = $name;

				$res = (object)self::import($req)->data;

				if ($res->name)
				{
					if ($res->extra_vars)
					{
						$res->extra_vars = unserialize($res->extra_vars);
					}

					$res = (array)$res;
					$this->oCache->set($res);
				}
				else if ($this->save())
				{
					return $this->initialize($name);
				}
				else
				{
					return FALSE;
				}
			}

			$this->sets($res);
		}

		return TRUE;
	}

	public function save()
	{
		$req = $this->getAll();
		$res = self::export((object)$req);

		if ($res->toBool())
		{
			$this->oCache->set(NULL);
		}

		return $res->toBool();
	}

	public function expire()
	{
		$res = self::expireWithName($this->get('name'));

		if ($res->toBool())
		{
			$this->truncate();
			$this->oCache->set(NULL);
		}

		return $res->toBool();
	}

	public function restore()
	{
		$res = self::restoreWithName($this->get('name'));

		if ($res->toBool())
		{
			$this->oCache->set(NULL);
		}

		return $res->toBool();
	}

	public static function vitalize()
	{
		$req = new stdClass();
		$req->names = $list = self::getList();

		$res = self::importsInstalled($req)->data;

		foreach ($res as $key => $val)
		{
			$list = array_diff($list, array($val->name));
		}

		$oPlugins = array();

		foreach ($list as $key => $val)
		{
			$oPlugins[] = new self($val);
		}

		return $oPlugins;
	}

	public static function organize()
	{
		$req = new stdClass();
		$req->names = self::getList();

		return executeQuery('notification.organizePlugins', $req);
	}

	public static function import(stdClass $req)
	{
		if ($req->extra_vars)
		{
			$req->extra_vars = serialize($req->extra_vars);
		}

		return executeQuery('notification.getPlugin', $req);
	}

	public static function importWithName($name)
	{
		$req = new stdClass();
		$req->name = $name;

		return self::import($req);
	}

	public static function imports(stdClass $req)
	{
		return executeQueryArray('notification.getPlugins', $req);
	}

	public static function importsInstalled(stdClass $req)
	{
		return executeQueryArray('notification.getInstalledPlugins', $req);
	}

	public static function importList(stdClass $req)
	{
		return executeQueryArray('notification.getPluginList', $req);
	}

	public static function export(stdClass $req)
	{
		if ($req->extra_vars)
		{
			$req->extra_vars = serialize($req->extra_vars);
		}

		$existColumn = self::importWithName($req->name);
		$exist = !!$existColumn->data;

		if ($exist)
		{
			return executeQuery('notification.updatePlugin', $req);
		}

		return executeQuery('notification.insertPlugin', $req);
	}

	public static function expireWithName($name)
	{
		$req = new stdClass();
		$req->name = $name;

		return executeQuery('notification.uninstallPlugin', $req);
	}

	public static function expireWithNames(array $names)
	{
		$req = new stdClass();
		$req->names = $names;

		return executeQuery('notification.uninstallPlugins', $req);
	}

	public static function restoreWithName($name)
	{
		$req = new stdClass();
		$req->name = $name;

		return executeQuery('notification.restorePlugin', $req);
	}

	public static function restoreWithNames(array $names)
	{
		$req = new stdClass();
		$req->names = $names;

		return executeQuery('notification.restorePlugins', $req);
	}

	public static function getList()
	{
		if (count(self::$list))
		{
			return self::$list;
		}

		$list = array();
		$plugins = sprintf('%splugins', ModuleHandler::getModulePath('notification'));
		$directories = FileHandler::readDir($plugins);

		foreach ($directories as $plugin)
		{
			$path = sprintf('%s/%s', $plugins, $plugin);

			if (is_dir($path) && is_file(sprintf('%s/plugin.xml', $path)))
			{
				$list[] = $plugin;
			}
		}

		natcasesort($list);

		return self::$list = $list;
	}

	public static function getInstanceList(stdClass $req)
	{
		self::organize();
		self::vitalize();

		$list = self::importList($req);

		foreach ($list->data as $key => $val)
		{
			$oPlugin = new self();
			$oPlugin->initialize($val->name, (array)$val);
			$list->data[$key] = $oPlugin;
		}

		return $list;
	}
}

/* End of file Plugin.php */
/* Location: ./modules/notification/classes/entity/Item/Plugin.php */
