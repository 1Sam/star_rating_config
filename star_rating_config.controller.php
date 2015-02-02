<?php
/* Copyright (C) 1SamOnline <http://1sam.kr> */
/**
 * The view class of the star_rating_config module
 *
 * @author 1Sam (csh@korea.com)
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

		// 글 삭제시 별점 log 삭제
	   $oModuleController->insertTrigger('document.deleteDocument', 'star_rating_config', 'controller', 'triggerDelete_star_rating_log', 'after');

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

		// 글 삭제시 별점 log 삭제
		if(!$oModuleModel->getTrigger('document.deleteDocument', 'star_rating_config', 'controller', 'triggerDelete_star_rating_log', 'after')) return true;

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
	    $oModuleController = &getController('module');

		//화면 표시전
		if(!$oModuleModel->getTrigger('display', 'star_rating_config', 'controller', 'triggerDisplay_star_rating', 'before')) {
			$oModuleController->insertTrigger('display', 'star_rating_config', 'controller', 'triggerDisplay_star_rating', 'before');
		}
		
		// 글 삭제시 별점 log 삭제
		if(!$oModuleModel->getTrigger('document.deleteDocument', 'star_rating_config', 'controller', 'triggerDelete_star_rating_log', 'after'))
    {
        $oModuleController->insertTrigger('document.deleteDocument', 'star_rating_config', 'controller', 'triggerDelete_star_rating_log', 'after');
    }


		$config = $oModuleModel->getModuleConfig('message');

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
