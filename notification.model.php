<?php
/*! Copyright (C) Eunsoo Lee. All rights reserved. */

use Notification\Entity\Item\Plugin;

class notificationModel extends notification
{
	public function getPlugin($name)
	{
		return new Plugin($name);
	}

	public function getPluginList(stdClass $args)
	{
		return Plugin::getInstanceList($args);
	}
}

/* End of file notification.model.php */
/* Location: ./modules/notification/notification.model.php */
