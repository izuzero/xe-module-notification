<?php
/*! Copyright (C) Eunsoo Lee. All rights reserved. */

namespace Notification\Entity\Item;
require_once 'Item.php';
use stdClass;
use FileHandler;
use ModuleHandler;
use XmlParser;

class Plugin extends Item
{
	private static $list = array();

	protected $oCache = NULL;

	protected $oInfo = NULL;

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
			$this->oCache->delete();
			$this->oInfo = NULL;
		}

		return $res->toBool();
	}

	public function expire()
	{
		$this->truncate();
		$this->oCache->delete();
		$this->oInfo = NULL;

		return TRUE;
	}

	public function getInfo()
	{
		if (isset($this->oInfo))
		{
			return $this->oInfo;
		}

		$name = $this->get('name');
		$extra_vars = $this->get('extra_vars');

		if (!is_object($extra_vars))
		{
			$extra_vars = new stdClass();
		}

		return $this->oInfo = self::getXmlInfo($name, $extra_vars);
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

	public static function getList()
	{
		if (count(self::$list))
		{
			return self::$list;
		}

		$list = array();
		$plugins = sprintf('%splugins/', ModuleHandler::getModulePath('notification'));
		$directories = FileHandler::readDir($plugins);

		foreach ($directories as $plugin)
		{
			$path = sprintf('%s%s/', $plugins, $plugin);

			if (is_dir($path) && is_file(sprintf('%splugin.xml', $path)))
			{
				$list[] = $plugin;
			}
		}

		natcasesort($list);

		return self::$list = $list;
	}

	public static function getInstanceList(stdClass $req)
	{
		$list = self::getList();
		$oPlugins = array();

		foreach ($list as $plugin)
		{
			$oPlugin = new self($plugin);

			$target = $oPlugin->getAll();
			$condition = (array)$req;

			if (!array_diff_assoc(array_intersect_key($target, $condition), $condition))
			{
				$oPlugins[] = $oPlugin;
			}
		}

		return new Page($oPlugins, $req->page, $req->paginate, $req->countWith);
	}

	public static function getXmlInfo($name, stdClass $args)
	{
		$path = sprintf('%splugins/%s/', ModuleHandler::getModulePath('notification'), $name);
		$file = sprintf('%splugin.xml', $path);

		if (!file_exists($file))
		{
			return new stdClass();
		}

		$oXmlParser = new XmlParser();
		$oXml = $oXmlParser->loadXmlFile($file);

		if ($oXml->plugin)
		{
			$oXml = $oXml->plugin;
		}
		else
		{
			return new stdClass();
		}

		$oXmlInfo = new stdClass();
		$oXmlInfo->title = $oXml->title->body;
		$oXmlInfo->colorset = array();

		if ($oXml->version && $oXml->attrs->version == '1.0')
		{
			$oDate = new stdClass();

			sscanf($oXml->date->body, '%d-%d-%d', $oDate->y, $oDate->m, $oDate->d);

			$oXmlInfo->author = array();
			$oXmlInfo->date = sprintf('%04d%02d%02d', $oDate->y, $oDate->m, $oDate->d);
			$oXmlInfo->version = $oXml->version->body;
			$oXmlInfo->license = $oXml->license->body;
			$oXmlInfo->license_link = $oXml->license->attrs->link;
			$oXmlInfo->description = $oXml->description->body;
			$oXmlInfo->extra_vars = array();

			$authors = $oXml->author;

			if (!is_array($authors))
			{
				$authors = array($authors);
			}

			foreach ($authors as $author)
			{
				$oAuthor = new stdClass();
				$oAuthor->name = $author->name->body;
				$oAuthor->email_address = $author->attrs->email_address;
				$oAuthor->homepage = $author->attrs->link;

				$oXmlInfo->author[] = $oAuthor;
			}

			if ($oXml->extra_vars)
			{
				$groups = $oXml->extra_vars->group;

				if (!$groups)
				{
					$groups = $oXml->extra_vars;
				}

				if (!is_array($groups))
				{
					$groups = array($groups);
				}

				foreach ($groups as $group)
				{
					$extra_vars = $group->var;

					if (!$extra_vars)
					{
						continue;
					}

					if (!is_array($extra_vars))
					{
						$extra_vars = array($extra_vars);
					}

					foreach ($extra_vars as $key => $val)
					{
						$extra_var = new stdClass();

						if (!$val->attrs->type)
						{
							$val->attrs->type = 'text';
						}

						$extra_var->group = $group->title->body;
						$extra_var->name = $val->attrs->name;
						$extra_var->title = $val->title->body;
						$extra_var->type = $val->attrs->type;
						$extra_var->description = $val->description->body;
						$extra_var->default = $val->attrs->default;

						if ($extra_var->name)
						{
							$extra_var->value = $args->{$extra_var->name}->value;
						}

						if (strpos($extra_var->value, '|@|') !== FALSE)
						{
							$extra_var->value = explode('|@|', $extra_var->value);
						}

						if (is_array($val->options))
						{
							$length = count($val->options);

							for ($i = 0; $i < $length; $i++)
							{
								$extra_var->options[$i] = new stdClass();
								$extra_var->options[$i]->title = $val->options[$i]->title->body;
								$extra_var->options[$i]->value = $val->options[$i]->attrs->value;
							}
						}
						else
						{
							$extra_var->options[0] = new stdClass();
							$extra_var->options[0]->title = $val->options->title->body;
							$extra_var->options[0]->value = $val->options->attrs->value;
						}

						$oXmlInfo->extra_vars[] = $extra_var;
					}
				}
			}
		}

		$colorset = $oXml->colorset->color;

		if ($colorset)
		{
			if (!is_array($colorset))
			{
				$colorset = array($colorset);
			}

			foreach ($colorset as $color)
			{
				$name = $color->attrs->name;
				$title = $color->title->body;
				$screenshot = $color->attrs->src;

				if ($screenshot)
				{
					$screenshot = sprintf('%s%s', $path, $screenshot);

					if (!file_exists($screenshot))
					{
						$screenshot = '';
					}
				}
				else
				{
					$screenshot = '';
				}

				$oColor = new stdClass();
				$oColor->name = $name;
				$oColor->title = $title;
				$oColor->screenshot = $screenshot;

				$oXmlInfo->colorset[] = $oColor;
			}
		}

		$thumbnail = sprintf('%sthumbnail.png', $path);

		if (!file_exists($thumbnail))
		{
			$thumbnail = NULL;
		}

		$oXmlInfo->thumbnail = $thumbnail;

		return $oXmlInfo;
	}
}

/* End of file Plugin.php */
/* Location: ./modules/notification/classes/entity/Item/Plugin.php */
