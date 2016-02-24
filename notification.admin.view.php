<?php
/*! Copyright (C) Eunsoo Lee. All rights reserved. */

class notificationAdminView extends notification
{
	public function init()
	{
		$this->__setTplPath();
	}

	public function dispNotificationAdminPlugins()
	{
	}

	protected function __setTplPath()
	{
		$this->setTemplatePath($this->module_path . 'tpl');
		$this->setTemplateFile(lcfirst(str_replace('dispNotificationAdmin', '', $this->act)));
	}
}

/* End of file notification.admin.view.php */
/* Location: ./modules/notification/notification.admin.view.php */
