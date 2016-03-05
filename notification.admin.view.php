<?php
/*! Copyright (C) Eunsoo Lee. All rights reserved. */

class notificationAdminView extends notification
{
	public function init()
	{
		$this->setTplPath();
	}

	public function dispNotificationAdminPlugins()
	{
		$req = new stdClass();
		$req->page = Context::get('page');
		$req->paginate = Context::get('paginate');
		$req->countWith = Context::get('countWith');

		$this->setPluginList('plugin_list', $req);
	}

	protected function setTplPath()
	{
		$this->setTemplatePath($this->module_path . 'tpl');
		$this->setTemplateFile(lcfirst(str_replace('dispNotificationAdmin', '', $this->act)));
	}

	protected function setPluginList($key, stdClass $req)
	{
		Context::set($key, getModel('notification')->getPluginList($req));
	}
}

/* End of file notification.admin.view.php */
/* Location: ./modules/notification/notification.admin.view.php */
