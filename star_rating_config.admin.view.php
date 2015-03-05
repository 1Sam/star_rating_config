<?php
/* Copyright (C) NAVER <http://www.navercorp.com> */
/**
 * The admin view class of the star_rating_config module
 *
 * @author NAVER (developers@xpressengine.com)
 */
class star_rating_configAdminView extends star_rating_config
{
	/**
	 * Cofiguration of integration serach module
	 *
	 * @var object module config
	 */
	var $config = null;

	/**
	 * Initialization
	 *
	 * @return void
	 */
	function init()
	{
		// Get configurations (using module model object)
		$oModuleModel = getModel('module');
		$this->config = $oModuleModel->getModuleConfig('star_rating_config');
		Context::set('config',$this->config);

		$this->setTemplatePath($this->module_path."/tpl/");
	}

	/**
	 * Module selection and skin set
	 *
	 * @return Object
	 */
	function dispStar_rating_configAdminContent()
	{
		// Get a list of skins(themes)
		$oModuleModel = getModel('module');
		$skin_list = $oModuleModel->getSkins($this->module_path);
		Context::set('skin_list',$skin_list);
		// Get a list of module categories
		$module_categories = $oModuleModel->getModuleCategories();
		// Generated mid Wanted list
		$obj = new stdClass();
		$obj->site_srl = 0;

		// Shown below as obsolete comments - modify by cherryfilter
		/*$mid_list = $oModuleModel->getMidList($obj);
		// module_category and module combination
		if($module_categories) {
		foreach($mid_list as $module_srl => $module) {
		$module_categories[$module->module_category_srl]->list[$module_srl] = $module; 
		}
		} else {
		$module_categories[0]->list = $mid_list;
		}

		Context::set('mid_list',$module_categories);*/

		$security = new Security();
		$security->encodeHTML('skin_list..title');

		// Sample Code
		Context::set('sample_code', htmlspecialchars('<img class="zbxe_widget_output" widget="star_rating" document_srl="{$document->document_srl}"|cond="$document_srl!=$document->document_srl" />', ENT_COMPAT | ENT_HTML401, 'UTF-8', false) );
		//Context::set('sample_code', 'sample_code' );

		$this->setTemplateFile("index");
	}



	function dispStar_rating_configAdminModuleConfig()
	{
		// Get a list of mid
		$oModuleModel = getModel('module');
		$columnList = array('module_srl', 'mid', 'browser_title','module','skin');
		$mid_list = $oModuleModel->getMidList(null, $columnList);
		Context::set('mid_list', $mid_list);

		Context::set('module_config', $oModuleModel->getModulePartConfigs('star_rating_config'));
		
		//Security
		$security = new Security();			
		$security->encodeHTML('mid_list..browser_title','mid_list..mid');

		// Set the template
		$this->setTemplateFile('module_config');

		/*
		//게시판의 스킨 리스트(종류) 불러오기
		$module_default_style = array();
		
		foreach($mid_list as $mid_) {


		$selected_module = $mid_->module;//board Context::get('selected_module');
		$skin = $mid_->skin;//rest_defaul Context::get('skin');
		// Get modules/skin information
		$module_path = sprintf("./modules/%s/", $selected_module);
		//if(!is_dir($module_path)) $this->stop("msg_invalid_request");

		//$skin_info_xml = sprintf("%sskins/%s/skin.xml", $module_path, $skin);
		//if(!file_exists($skin_info_xml)) $this->stop("msg_invalid_request");

		//$oModuleModel = getModel('module');
		$skin_info = $oModuleModel->loadSkinInfo($module_path, $skin);
		//$module_default_style[] = $oModuleModel->loadSkinInfo($module_path, $skin);
		//기본 게시판의 레스트 디펄트 스킨일 경우는 아래와 같은 보기 방식임
		
		$module_default_style[] = $skin_info->extra_vars[7]->options;
		//Context::set('skin_info',$skin_info->extra_vars[7]);
			
		}
		Context::set('skin_info',$module_default_style);
		*/
		
		/*
		$selected_module = 'board';//Context::get('selected_module');
		$skin = 'rest_default';//Context::get('skin');
		// Get modules/skin information
		$module_path = sprintf("./modules/%s/", $selected_module);
		if(!is_dir($module_path)) $this->stop("msg_invalid_request");

		//$skin_info_xml = sprintf("%sskins/%s/skin.xml", $module_path, $skin);
		//if(!file_exists($skin_info_xml)) $this->stop("msg_invalid_request");

		//$oModuleModel = getModel('module');
		$skin_info = $oModuleModel->loadSkinInfo($module_path, $skin);
		//기본 게시판의 레스트 디펄트 스킨일 경우는 아래와 같은 보기 방식임
		Context::set('skin_info',$skin_info->extra_vars[7]);
		*/	


	}


	/**
	 * Skin Settings
	 *
	 * @return Object
	 */
	function dispStar_rating_configAdminSkinInfo()
	{
		$oModuleModel = getModel('module');
		$skin_info = $oModuleModel->loadSkinInfo($this->module_path, $this->config->skin);
		$skin_vars = unserialize($this->config->skin_vars);
		// value for skin_info extra_vars
		if(count($skin_info->extra_vars))
		{
			foreach($skin_info->extra_vars as $key => $val)
			{
				$name = $val->name;
				$type = $val->type;
				$value = $skin_vars->{$name};
				if($type=="checkbox"&&!$value) $value = array();
				$skin_info->extra_vars[$key]->value= $value;
			}
		}
		Context::set('skin_info', $skin_info);
		Context::set('skin_vars', $skin_vars);

		$config = $oModuleModel->getModuleConfig('star_rating_config');
		Context::set('module_info', unserialize($config->skin_vars));

		$security = new Security();
		$security->encodeHTML('skin_info...');

		$this->setTemplateFile("skin_info");
	}
}
/* End of file star_rating_config.admin.view.php */
/* Location: ./modules/star_rating_config/star_rating_config.admin.view.php */
