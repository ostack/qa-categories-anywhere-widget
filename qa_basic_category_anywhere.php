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
	public function allow_template($template)
	{
		return $template != 'admin';
	}


	public function allow_region($region)
	{
		return in_array($region, array('main', 'side', 'full'));
	}
	
	
	public function admin_form()
	{
		$saved = false;
		if (qa_clicked('save_button')) {
			$saved = true;
		}elseif (qa_clicked('donate_zhao_guangyue')) {
				qa_redirect_raw('https://paypal.me/guangyuezhao');
		}



		$form = array(
			'ok' => $saved ? 'All oprations have been saved' : null,
			'buttons' => array(
				array(
						'label' => 'Donate',
						'tags' => 'NAME="donate_zhao_guangyue"',
				)
			),
			'fields' => array(
				array(
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
		if (isset($qa_content['navigation']['cat'])) {
			$nav = $qa_content['navigation']['cat'];
		} else {
			$selectspec = qa_db_category_nav_selectspec(null, true, false, true);
			$navcategories = qa_db_single_select($selectspec);
			$nav = qa_category_navigation($navcategories);
		}
		
		$this->themeobject->output('<h2>' . qa_lang_html('main/nav_categories') . ' </h2>');
		$this->nav_list($themeobject,$nav, 'browse-cat', 0);
		$this->themeobject->output('</br>');
	}
	
	public function nav_list($outPutCtent,$navigation, $class, $level = null)
	{
		$outPutCtent->output('<ul class="qa-' . $class . '-list' . (isset($level) ? (' qa-' . $class . '-list-' . $level) : '') . '">');
		
		foreach ($navigation as $key => $navlink) {
			$this->nav_item($outPutCtent,$key, $navlink, $class, $level);
		}
		
		$outPutCtent->output('</ul>');
	}
	
	public function nav_item($outPutCtent,$key, $navlink, $class, $level = null)
	{
		$suffix = strtr($key, array( // map special character in navigation key
			'$' => '',
			'/' => '-',
		));

		$this->nav_link($outPutCtent,$navlink, $class);

		$subnav = isset($navlink['subnav']) ? $navlink['subnav'] : array();
 		if (is_array($subnav) && count($subnav) > 0) {
			foreach ($subnav as $key => $navlink) {
				$this->nav_item($outPutCtent,$key, $navlink, $class, $level);
			}
		}
	}
	
	public function nav_link($outPutCtent,$navlink, $class)
	{
		if (isset($navlink['url'])) {
			$statNum = strlen(@$navlink['note']) > 0 ? $navlink['note']:'';
			$outPutCtent->output(
				'<a href="' . $navlink['url'] . '" class="qa-' . $class . '-link ">' .$navlink['label'] ." ".$statNum.'</a>'
			);
		} else {
			$outPutCtent->output(
				'<span class="qa-' . $class . '-nolink' . (@$navlink['selected'] ? (' qa-' . $class . '-selected') : '') .
				(strlen(@$navlink['popup']) ? (' title="' . $navlink['popup'] . '"') : '') .
				'>' . $navlink['label'] . '</span>'
			);
		} 
	}
}
