<?php
	global $Session, $DB;
    
	$image_path = imagePath('elements/' . $System['element']['name']);
	$header_sub_tabs = array();
    
    echo
		'<table style="border-bottom: solid 2px #41464b; width: 100%;" border="0" cellspacing="0" cellpadding="0">' .
			'<tr>' .
				'<td>' .
					'<div style="padding: 5px 0px 10px 40px;">' .
                        '<div style="float: left; padding: 10px 40px 10px 40px;">'.
                            '<a href="' . url() . '"><img src="' . $image_path . 'logo.png" title="' . $System['domain']['name'] . '" alt="' . $System['domain']['name'] . '" /></a>'.
                          '</div>'.
    				'</div>' .
					'<table class="header_toolbar" border="0" cellspacing="0" cellpadding="0">' .
						'<tr>' .
							'<td><img src="' . $image_path . 'toolbar_l.gif" alt="" /></td>';
       

    $menus = array();
    $rs_menus = $DB->query('SELECT * FROM menu WHERE !disabled AND !deleted AND !parent ORDER BY rank');
    while ($menu = $rs_menus->next()) {
        $rs_children = $DB->query('SELECT * FROM menu WHERE !disabled AND !deleted AND parent='. $menu['id'] .' ORDER BY rank');
        while ($children = $rs_children->next()) {
            $menu['children'][] = $children;            
        }
        $rs_children->free();
        $menus[] = $menu; 
    }
    $rs_menus->free();
    
    
    
    $header_tabs = array();
    foreach ($menus as $menu) {
        if ($menu['require_login']) {
            if ($group_id == $menu['require_login'] && $user_id) {
                $header_tabs[$menu['name']] = array(
                    'link' => url($System['pages'][$menu['link']]),
                    'pages' => array(),
                    'children' => array()
                );
                
                if(isset($menu['regexp']) || stripos($menu['link'], ',')) {
                    $pages = explode(',', $menu['regexp']);
                    foreach ($pages as $page) {
                        
                        $header_tabs[$menu['name']]['pages'][] = $System['pages'][$page];
                    }
                
                }
                
                if(isset($menu['children'])){            
                    foreach ($menu['children'] as $child) {
                        $header_tabs[$menu['name']]['children'][$System['pages'][$child['link']]] = array('title' => $child['name'], 'link' => url($System['pages'][$child['link']]));
                    }
                }                
            }
        } else {
            $header_tabs[$menu['name']] = array(
                'link' => url($System['pages'][$menu['link']]),
                'pages' => array($System['pages'][$menu['link']]),
                'children' => array()
            );  
            if(isset($menu['children'])){            
                foreach ($menu['children'] as $child) {
                    $header_tabs[$menu['name']]['children'][$System['pages'][$child['link']]] = array('title' => $child['name'], 'link' => url($System['pages'][$child['link']]));
                }
            }
        }
    }
    
		function outputHeaderTabSegment($category_next, $image_path, $text) {
			static $category_previous = 0;
			switch (($category_previous * 3) + ($category_next + 1)) {
				case 5:         echo '<td><img src="' . $image_path . 'toolbar_div.gif" title="" alt="|" /></td>'; break;
				case 3: case 6: echo '<td><img src="' . $image_path . 'toolbar_sl.gif" alt="" /></td>'; break;
				case 7: case 8: echo '<td><img src="' . $image_path . 'toolbar_sr.gif" alt="" /></td>'; break;
			}
			switch ($category_next) {
				case 1: echo '<td class="normal">' . $text . '</td>'; break;
				case 2: echo '<td class="selected">' . $text . '</td>'; break;
			}
			$category_previous = $category_next;
		}
	
		$current_page = $System['page']['name'];
		foreach ($header_tabs as $header_tab_name => $header_tab) {
			$is_current_page = in_array($current_page, $header_tab['pages']) || array_key_exists($current_page, $header_tab['children']);
			outputHeaderTabSegment(($is_current_page ? 2 : 1), $image_path, '<a href="' . $header_tab['link'] . '">' . $header_tab_name . '</a>');
			if ($is_current_page) {
				$header_sub_tabs = &$header_tab['children'];
			}
		}
		outputHeaderTabSegment(0, $image_path, '');
	
		echo
								'<td class="normal" style="width: 100%;">&nbsp;</td>' .
								'<td><img src="' . $image_path . 'toolbar_r.gif" alt="" /></td>' .
							'</tr>' .
						'</table>' .
					'</td>' .
				'</tr>' .
				'<tr>' .
					'<td style="background: url(' . $image_path . 'sub_menu_bg.gif) repeat-x top #00476C; padding: 0px 7px 2px 7px; border-left: solid 1px #00476C; border-right: solid 1px #00476C; border-bottom: solid 1px #00476C;">' .
						'<table style="margin: auto;" class="header_sub_toolbar" border="0" cellspacing="0" cellpadding="0">' .
							'<tr>';
	
		function outputHeaderSubTabSegment($sub_category_next, $image_path, $text)
		{
			static $sub_category_previous = 0;
			echo '<td><img src="' . $image_path . 'sub_' . (($sub_category_previous * 3) + ($sub_category_next + 1)) . '.gif" alt="" /></td>';
			switch ($sub_category_next) {
				case 1: echo '<td class="normal">'   . $text . '</td>'; break;
				case 2: echo '<td class="selected">' . $text . '</td>'; break;
			}
			$sub_category_previous = $sub_category_next;
		}
		foreach ($header_sub_tabs as $header_sub_index => $header_sub_item) {
			outputHeaderSubTabSegment(($header_sub_index == $current_page ? 2 : 1), $image_path, '<a href="' . $header_sub_item['link'] . '">' . $header_sub_item['title'] . '</a>');
		}
		outputHeaderSubTabSegment(0, $image_path, '');
	
		echo
							'</tr>' .
						'</table>' .
					'</td>' .
				'</tr>' .
			'</table>';
?>
