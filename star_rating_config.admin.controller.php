<?php
/* Copyright (C) NAVER <http://www.navercorp.com> */
/**
 * The admin view class of the star_rating_config module
 *
 * @author NAVER (developers@xpressengine.com)
 */
class star_rating_configAdminController extends star_rating_config
{
	/**
	 * Initialization
	 *
	 * @return void
	 */
	function init()
	{
	}

	/**
	 * Save Settings
	 *
	 * @return mixed
	 */
	function procStar_rating_configAdminInsertConfig()
	{
		// Get configurations (using module model object)
		$oModuleModel = getModel('module');
		$config = $oModuleModel->getModuleConfig('star_rating_config');

		$args->skin = Context::get('skin');
		$args->target = Context::get('target');
		$args->target_module_srl = Context::get('target_module_srl');
		if(!$args->target_module_srl) $args->target_module_srl = '';
		$args->skin_vars = $config->skin_vars;

		$oModuleController = getController('module');
		$output = $oModuleController->insertModuleConfig('star_rating_config',$args);

		$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'module', 'admin', 'act', 'dispStar_rating_configAdminContent');
		return $this->setRedirectUrl($returnUrl, $output);
	}

	/**
	 * Save the skin information
	 *
	 * @return mixed
	 */
	function procStar_rating_configAdminInsertSkin()
	{
		// Get configurations (using module model object)
		$oModuleModel = getModel('module');
		$config = $oModuleModel->getModuleConfig('star_rating_config');

		$args->skin = $config->skin;
		$args->target_module_srl = $config->target_module_srl;
		// Get skin information (to check extra_vars)
		$skin_info = $oModuleModel->loadSkinInfo($this->module_path, $config->skin);
		// Check received variables (delete the basic variables such as mo, act, module_srl, page)
		$obj = Context::getRequestVars();
		unset($obj->act);
		unset($obj->module_srl);
		unset($obj->page);
		// Separately handle if the extra_vars is an image type in the original skin_info
		if($skin_info->extra_vars)
		{
			foreach($skin_info->extra_vars as $vars)
			{
				if($vars->type!='image') continue;

				$image_obj = $obj->{$vars->name};
				// Get a variable on a request to delete
				$del_var = $obj->{"del_".$vars->name};
				unset($obj->{"del_".$vars->name});
				if($del_var == 'Y')
				{
					FileHandler::removeFile($module_info->{$vars->name});
					continue;
				}
				// Use the previous data if not uploaded
				if(!$image_obj['tmp_name'])
				{
					$obj->{$vars->name} = $module_info->{$vars->name};
					continue;
				}
				// Ignore if the file is not successfully uploaded, and check uploaded file
				if(!is_uploaded_file($image_obj['tmp_name']) || !checkUploadedFile($image_obj['tmp_name']))
				{
					unset($obj->{$vars->name});
					continue;
				}
				// Ignore if the file is not an image
				if(!preg_match("/\.(jpg|jpeg|gif|png)$/i", $image_obj['name']))
				{
					unset($obj->{$vars->name});
					continue;
				}
				// Upload the file to a path
				$path = sprintf("./files/attach/images/%s/", $module_srl);
				// Create a directory
				if(!FileHandler::makeDir($path)) return false;

				$filename = $path.$image_obj['name'];
				// Move the file
				if(!move_uploaded_file($image_obj['tmp_name'], $filename))
				{
					unset($obj->{$vars->name});
					continue;
				}
				// Change a variable
				unset($obj->{$vars->name});
				$obj->{$vars->name} = $filename;
			}
		}
		// Serialize and save 
		$args->skin_vars = serialize($obj);

		$oModuleController = getController('module');
		$output = $oModuleController->insertModuleConfig('star_rating_config',$args);

		$this->setMessage('success_updated', 'info');

		$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'module', 'admin', 'act', 'dispStar_rating_configAdminSkinInfo');
		return $this->setRedirectUrl($returnUrl, $output);
	}
	
	
	//각 별점 설정 저장하기
	function procStar_rating_configAdminInsertModuleConfig()
	{
		$args = Context::getRequestVars();

        /*
		    $oModuleModel = getModel('module');
            $module_path = sprintf("./modules/%s/", 'board');//$val->module);
            $style_default = $oModuleModel->loadSkinInfo($module_path, 'skin');//$val->skin);
            
            $st = $style_default->extra_vars[7]->options;
		$configTypeList = array();
		foreach($st as $va) {
			$configTypeList[] = 'star_max_'.$va->value;
			$configTypeList[] = 'star_skin_'.$va->value;
		}*/
		
		
		$keys = array();
		foreach($args as $key => $val){
			if(strpos($key,'star_') !== false){
				$keys[] = $key;
			}
		}
		
		//$keys = array_keys($args);
//file_put_contents('somefilea.txt', print_r($args, true));
//file_put_contents('somefile.txt', print_r($keys, true));
		
		//$configTypeList = array('star_max_list','star_max_webzine','star_max_gallery','star_max_card', 'star_skin');
		$configTypeList = $keys;//array('star_max','star_skin');
		
		
		foreach($configTypeList AS $config)
		{
			if(is_array($args->{$config}))
			{
				foreach($args->{$config} AS $key=>$value)
				{
					$module_config[$key][$config] = $value;
				}
			}
		}

//$module_config = $args;

		// DB의 module_part_config 에 저장되며 
		// module(star_point), module_srl(416), config(해당 모듈의 설정값)으로 저장됨
		// 별점 위젯에서는 이 부분의 설정값을 바로 가져와야 할 듯

		$oModuleController = getController('module');
		if(count($module_config))
		{
			foreach($module_config as $module_srl => $config)
			{
				$oModuleController->insertModulePartConfig('star_rating_config',$module_srl,$config);
			}
		}

		$this->setMessage('success_updated');
		if(!in_array(Context::getRequestMethod(),array('XMLRPC','JSON')))
		{
			$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'module', 'admin', 'act', 'dispStar_rating_configAdminModuleConfig');
			header('location:'.$returnUrl);
			return;
		}
	}
}
/* End of file star_rating_config.admin.controller.php */
/* Location: ./modules/star_rating_config/star_rating_config.admin.controller.php */
