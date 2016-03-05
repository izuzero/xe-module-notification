<?php
/*! Copyright (C) Eunsoo Lee. All rights reserved. */

class notification extends ModuleObject
{
	public static $default_config = array();

	protected static $columns = array();

	protected static $indexes = array();

	protected static $triggers = array();

	private static $classes = array(
		'classes/Polyfill/__autoload.php',
		'classes/Entity/Item/Page.php',
		'classes/Entity/Item/Plugin.php',
		'classes/Entity/Item/InstantNotification.php',
		'classes/Entity/PublishSubscribeModel/Standalone.php',
		'classes/Entity/PublishSubscribeModel/ServerSentEvents.php',
		'classes/Entity/PublishSubscribeModel/SocketIO.php'
	);

	public function __construct()
	{
		$module_path = ModuleHandler::getModulePath('notification');

		foreach (self::$classes as $class)
		{
			require_once __DIR__ . '/' . $class;
		}
	}

	public function moduleInstall()
	{
		$this->moduleUpdate();
	}

	public function moduleUninstall()
	{
		$oModuleModel = getModel('module');
		$oModuleController = getController('module');

		foreach (self::$triggers as $trigger)
		{
			if ($oModuleModel->getTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]))
			{
				$oModuleController->deleteTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]);
			}
		}
	}

	public function checkUpdate()
	{
		$oDB = DB::getInstance();
		$oModuleModel = getModel('module');

		foreach (self::$columns as $column)
		{
			if (!$oDB->isColumnExists($column[0], $column[1]))
			{
				return TRUE;
			}
		}

		foreach (self::$indexes as $index)
		{
			if (!$oDB->isIndexExists($index[0], $index[1]))
			{
				return TRUE;
			}
		}

		foreach (self::$triggers as $trigger)
		{
			if (!$oModuleModel->getTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]))
			{
				return TRUE;
			}
		}

		return FALSE;
	}

	public function moduleUpdate()
	{
		$oDB = DB::getInstance();
		$oModuleModel = getModel('module');
		$oModuleController = getController('module');

		foreach (self::$columns as $column)
		{
			if (!$oDB->isColumnExists($column[0], $column[1]))
			{
				$oDB->addColumn($column[0], $column[1], $column[2], $column[3], $column[4], $column[5]);
			}
		}

		foreach (self::$indexes as $index)
		{
			if (!$oDB->isIndexExists($index[0], $index[1]))
			{
				$oDB->addIndex($index[0], $index[1], $index[2], $index[3]);
			}
		}

		foreach (self::$triggers as $trigger)
		{
			if (!$oModuleModel->getTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]))
			{
				$oModuleController->insertTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]);
			}
		}
	}
}

/* End of file notification.class.php */
/* Location: ./modules/notification/notification.class.php */
