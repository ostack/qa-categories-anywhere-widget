<?php
/*
	Question2Answer by Gideon Greenspan and contributors
	http://www.question2answer.org/

	File: qa-plugin/basic-adsense/qa-basic-adsense.php
	Description: Widget module class for AdSense widget plugin


	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	More about this license: http://www.question2answer.org/license.php
*/

class qa_basic_category_anywhere
{
	private $has_echo_cat_num=0;
	
	public function allow_template($template)
	{
		return $template != 'admin';
	}

	public function option_default($option)
	{
		switch ($option) {
			case 'is_show_sub_category':
				return false;
			case 'max_category_to_show':	
			    return 10;
			case 'which_category_to_show':	
			    return '';
			case 'header_css_style':	
			    return '.qa_cat_head_self_def{
						padding: 10px;
						background: #9b59b7;
						color: #fff;
						margin: 0 0 5px 0;
						font-size: 20px;
						font-weight:800;
						}';
			case 'category_list_css_style':	
			    return '.qa_cat_link_self_def {
							background-color: #c0392b;
							margin: 0 0 5px 0;
							font-size: 15px;
							font-weight: 500;
							color: #fff;
							padding: 2px 1em;
							display: inline-block;
						}
						.qa_ul_link_self_def {
						}
						.qa_ul_link_self_def a :hover,.qa_ul_link_self_def a:active,.qa_ul_link_self_def a:visited{
							color: #fff;
							border-bottom: 1px dotted #fff;
						}';
		}
	}

	public function allow_region($region)
	{
		return in_array($region, array('main', 'side', 'full'));
	}
	
	
	public function admin_form()
	{
		$saved = false;
		if (qa_clicked('save_change')) {
			qa_opt('is_show_sub_category',(int)qa_post_text('is_show_sub_category'));
			qa_opt('max_category_to_show', (int)qa_post_text('max_category_to_show'));
			qa_opt('which_category_to_show', qa_post_text('which_category_to_show'));
			qa_opt('header_css_style', qa_post_text('header_css_style'));
			qa_opt('category_list_css_style', qa_post_text('category_list_css_style'));
			$saved = true;
		}elseif (qa_clicked('donate_zhao_guangyue')) {
				qa_redirect_raw('https://paypal.me/guangyuezhao');
		}

		$form = array(
			'ok' => $saved ? 'All oprations have been saved' : null,
			'buttons' => array(
				array(
					'label' => 'Save',
					'tags' => 'name="save_change"',
				),array(
					'label' => 'Donate',
					'tags' => 'NAME="donate_zhao_guangyue"',
				)
			),
			'fields' => array(
				'is_show_sub_category'=>array(
					'label' => 'Is show sub category',
					'type' => 'checkbox',
					'value' => (int)qa_opt('is_show_sub_category'),
					'tags' => 'name="is_show_sub_category"',
			    ),
			    'max_category_to_show'=>array(
					'label' => 'max category to show 0 means not limit:',
					'tags' => 'NAME="max_category_to_show"',
					'value' => (int)qa_opt('max_category_to_show'),
					'type' => 'number',
				),
			    'which_category_to_show'=>array(
					'label' => 'Which category to show,separate category name with commas, empty for all (e.g AA,BB):',
					'tags' => 'NAME="which_category_to_show"',
					'value' => qa_opt('which_category_to_show'),
					'type' => 'text',
				),
			    'header_css_style'=>array(
					'label' => 'Css style for category header (Do not change css name [ qa_cat_head_self_def ] it was used in php code):',
					'tags' => 'NAME="header_css_style"',
					'value' => qa_opt('header_css_style'),
					'rows' => 10,
					'type' => 'textarea',
				),
			    'category_list_css_style'=>array(
					'label' => 'Css style for category list (Do not change css name [ qa_cat_link_self_def ] it was used in php code):',
					'tags' => 'NAME="category_list_css_style"',
					'value' => qa_opt('category_list_css_style'),
					'rows' => 10,
					'type' => 'textarea',
				),
				'hope_donate'=>array(
					'label' => '<span style="color:#f90; font-size:16px; text-align:center;">Hope you can donate <strong>$1</strong> for my work!</br> Thanks very much!</span>',
					'type' => 'custom',
					'tags' => 'NAME="hope_donate"',
				),
			),
		);
		return $form;
	}
	
	public function output_widget($region, $place, $themeobject, $template, $request, $qa_content)
	{   
		$this->themeobject = $themeobject;
		$this->add_self_def_style($themeobject);
		if (isset($qa_content['navigation']['cat'])) {
			$nav = $qa_content['navigation']['cat'];
		} else {
			$selectspec = qa_db_category_nav_selectspec(null, true, false, true);
			$navcategories = qa_db_single_select($selectspec);
			$nav = qa_category_navigation($navcategories);
		}
		
		$this->themeobject->output('<h2 class="qa_cat_head_self_def">' . qa_lang_html('main/nav_categories') . ' </h2>');
		$this->nav_list($themeobject,$nav, 0);
		$this->themeobject->output('</br>');
	}
	
	public function nav_list($outPutCtent,$navigation, $level = null)
	{
		$this->has_echo_cat_num = 0;
		$outPutCtent->output('<ul class="qa_ul_link_self_def qa-browse-cat-list' . (isset($level) ? (' qa-browse-cat-list-' . $level) : '') . '">');
		foreach ($navigation as $key => $navlink) {
			$this->nav_item($outPutCtent, $navlink, $level);
		}
		
		$outPutCtent->output('</ul>');
	}
	
	public function nav_item($outPutCtent, $navlink, $level = null)
	{
		$this->nav_link($outPutCtent,$navlink);

		$subnav = isset($navlink['subnav']) ? $navlink['subnav'] : array();
 		if (is_array($subnav) && count($subnav) > 0 && qa_opt('is_show_sub_category')) {
			foreach ($subnav as $key => $navlink) {
				$this->nav_item($outPutCtent,$navlink, $level);
			}
		}
	}
	
	public function nav_link($outPutCtent,$navlink)
	{
		//echo qa_opt('max_category_to_show');
		$max_category_to_show = qa_opt('max_category_to_show');
		if($max_category_to_show == 0 || $this->has_echo_cat_num < $max_category_to_show){
			if (isset($navlink['url'])) {
				if($this->is_cat_in_user_def($navlink['label'])){
					$statNum = strlen(@$navlink['note']) > 0 ? $navlink['note']:'';
					$outPutCtent->output(
						'<a href="' . $navlink['url'] . '" class="qa_cat_link_self_def ">' .$navlink['label']." ".$statNum.'</a>'
					);
					$this->has_echo_cat_num = $this->has_echo_cat_num + 1;	
				}
			} else {
				$outPutCtent->output(
					'<span class="qa-' . $class . '-nolink' . (@$navlink['selected'] ? (' qa-' . $class . '-selected') : '') .
					(strlen(@$navlink['popup']) ? (' title="' . $navlink['popup'] . '"') : '') .
					'>' . $navlink['label'] . '</span>'
				);
				$this->has_echo_cat_num = $this->has_echo_cat_num + 1;	
			}
            		
		}
	}
	
	public function add_self_def_style($outPutCtent){
		$outPutCtent->output('<style>',qa_opt('header_css_style'),'</style>');
		$outPutCtent->output('<style>',qa_opt('category_list_css_style'),'</style>');
	}
	
	public function is_cat_in_user_def($cat_name){
	    if(trim(qa_opt('which_category_to_show')=='')){
           return true;
		}else{
			$user_show_cat = explode(',',qa_opt('which_category_to_show'));	
			foreach($user_show_cat as $value){
				if(trim($value)==$cat_name){
					return true;
				}
			}
			return false;
		}
	}
}
