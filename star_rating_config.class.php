<?php
/* Copyright (C) NAVER <http://www.navercorp.com> */
/**
 * The view class of the star_rating_config module
 *
 * @author NAVER (developers@xpressengine.com)
 */
class star_rating_config extends ModuleObject
{
	/**
	 * Implement if additional tasks are necessary when installing
	 *
	 * @return Object
	 */
	function moduleInstall()
	{
		// Registered in action forward
		$oModuleController = getController('module');
		$oModuleController->insertActionForward('star_rating_config', 'view', 'IS');
		// 화면 표시전
	    $oModuleController->insertTrigger('display', 'star_rating_config', 'controller', 'triggerDisplay_star_rating', 'before');
 
		return new Object();
	}

	/**
	 * Check methoda whether successfully installed
	 *
	 * @return bool
	 */
	function checkUpdate() 
	{
		$oModuleModel = getModel('module');
		$config = $oModuleModel->getModuleConfig('star_rating_config');

		//화면 표시전
		if(!$oModuleModel->getTrigger('display', 'star_rating_config', 'controller', 'triggerDisplay_star_rating', 'before')) return true;

		if($config->skin)
		{
			$config_parse = explode('.', $config->skin);
			if(count($config_parse) > 1)
			{
				$template_path = sprintf('./themes/%s/modules/star_rating_config/', $config_parse[0]);
				if(is_dir($template_path)) return true;
			}
		}
		return false;
	}

	/**
	 * Execute update
	 *
	 * @return Object
	 */
	function moduleUpdate() 
	{
		$oModuleModel = getModel('module');
		$config = $oModuleModel->getModuleConfig('message');

	    $oModuleController = &getController('module');
		//화면 표시전
		if(!$oModuleModel->getTrigger('display', 'star_rating_config', 'controller', 'triggerDisplay_star_rating', 'before')) {
			$oModuleController->insertTrigger('display', 'star_rating_config', 'controller', 'triggerDisplay_star_rating', 'before');
		}



		if($config->skin)
		{
			$config_parse = explode('.', $config->skin);
			if(count($config_parse) > 1)
			{
				$template_path = sprintf('./themes/%s/modules/star_rating_config/', $config_parse[0]);
				if(is_dir($template_path))
				{
					$config->skin = implode('|@|', $config_parse);
					$oModuleController = getController('module');
					$oModuleController->updateModuleConfig('star_rating_config', $config);
				}
			}
		}

		return new Object(0, 'success_updated');
	}

	/**
	 * Re-generate the cache file
	 *
	 * @return void
	 */
	function recompileCache()
	{
	}
}
/* End of file star_rating_config.class.php */
/* Location: ./modules/star_rating_config/star_rating_config.class.php */
