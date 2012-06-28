<?php
/*
** Zabbix
** Copyright (C) 2000-2011 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/
?>
<?php
/**
 * Function: map_link_drawtypes
 *
 * Description:
 *     Return available drawing types for links
 */
function map_link_drawtypes() {
	return array(
		MAP_LINK_DRAWTYPE_LINE,
		MAP_LINK_DRAWTYPE_BOLD_LINE,
		(function_exists('imagesetstyle') ? MAP_LINK_DRAWTYPE_DOT : null),
		MAP_LINK_DRAWTYPE_DASHED_LINE
	);
}

function sysmap_element_types($type = null) {
	$types = array(
		SYSMAP_ELEMENT_TYPE_HOST => _('Host'),
		SYSMAP_ELEMENT_TYPE_HOST_GROUP => _('Host group'),
		SYSMAP_ELEMENT_TYPE_TRIGGER => _('Trigger'),
		SYSMAP_ELEMENT_TYPE_MAP => _('Map'),
		SYSMAP_ELEMENT_TYPE_IMAGE => _('Image')
	);

	if (is_null($type)) {
		natsort($types);
		return $types;
	}
	elseif (isset($types[$type])) {
		return $types[$type];
	}
	else {
		return _('Unknown');
	}
}

function sysmapElementLabel($label = null) {
	$labels = array(
		MAP_LABEL_TYPE_LABEL => _('Label'),
		MAP_LABEL_TYPE_IP => _('IP address'),
		MAP_LABEL_TYPE_NAME => _('Element name'),
		MAP_LABEL_TYPE_STATUS => _('Status only'),
		MAP_LABEL_TYPE_NOTHING => _('Nothing'),
		MAP_LABEL_TYPE_CUSTOM => _('Custom label')
	);

	if (is_null($label)) {
		return $labels;
	}
	elseif (isset($labels[$label])) {
		return $labels[$label];
	}
	else {
		return false;
	}
}

/**
 * Function: map_link_drawtype2str
 *
 * Description:
 *     Represent integer value of links drawing type into the string
 */
function map_link_drawtype2str($drawtype) {
	switch ($drawtype) {
		case MAP_LINK_DRAWTYPE_LINE:
			$drawtype = _('Line');
			break;
		case MAP_LINK_DRAWTYPE_BOLD_LINE:
			$drawtype = _('Bold line');
			break;
		case MAP_LINK_DRAWTYPE_DOT:
			$drawtype = _('Dot');
			break;
		case MAP_LINK_DRAWTYPE_DASHED_LINE:
			$drawtype = _('Dashed line');
			break;
		default:
			$drawtype = _('Unknown');
			break;
	}
	return $drawtype;
}

function getActionMapBySysmap($sysmap) {
	$action_map = new CAreaMap('links'.$sysmap['sysmapid']);

	$areas = populateFromMapAreas($sysmap);
	$map_info = getSelementsInfo($sysmap);
	processAreasCoordinates($sysmap, $areas, $map_info);

	$hostids = array();
	foreach ($sysmap['selements'] as $sid => &$selement) {
		if ($selement['elementtype'] == SYSMAP_ELEMENT_TYPE_HOST) {
			$hostids[$selement['elementid']] = $selement['elementid'];

			// expanding hosts url macros again as some hosts were added from hostgroup areeas
			// and automatic expanding only happens for elements that are defined for map in db
			foreach ($selement['urls'] as $urlid => $url) {
				$selement['urls'][$urlid]['url'] = str_replace('{HOST.ID}', $selement['elementid'], $url['url']);
			}
		}
		if ($selement['elementsubtype'] == SYSMAP_ELEMENT_SUBTYPE_HOST_GROUP_ELEMENTS) {
			unset($sysmap['selements'][$sid]);
		}
	}
	unset($selement);

	$scripts_by_hosts = API::Script()->getScriptsByHosts($hostids);

	$options = array(
		'nodeids' => get_current_nodeid(true),
		'hostids' => $hostids,
		'output' => array('status'),
		'nopermissions' => true,
		'preservekeys' => true,
		'selectScreens' => API_OUTPUT_COUNT,
	);
	$hosts = API::Host()->get($options);


	foreach ($sysmap['selements'] as $db_element) {
		$links_menus = '';
		$menus = '';

		if ($db_element['elementtype'] == SYSMAP_ELEMENT_TYPE_HOST) {
			$host = $hosts[$db_element['elementid']];
			$tools_menus = '';
			foreach ($scripts_by_hosts[$db_element['elementid']] as $script) {
				$str_tmp = zbx_jsvalue('javascript: executeScript('.$db_element['elementid'].', '.
							$script['scriptid'].', '.
							zbx_jsvalue($script['confirmation']).')'
				);

				$tools_menus .= "[".zbx_jsvalue($script['name']).", ".$str_tmp.", null,{'outer' : ['pum_o_item'],'inner' : ['pum_i_item']}],";
			}

			if (!empty($tools_menus)) {
				$menus .= "['"._('Scripts')."',null,null,{'outer' : ['pum_oheader'],'inner' : ['pum_iheader']}],";
				$menus .= $tools_menus;
			}
			if ($host['status'] == HOST_STATUS_MONITORED) {
				$links_menus .= "['"._('Status of triggers')."',\"javascript: redirect('tr_status.php?hostid=".$db_element['elementid']."');\", null,{'outer' : ['pum_o_item'],'inner' : ['pum_i_item']}],";
				if ($host['screens']) {
					$links_menus .= "['"._('Host screens')."',\"javascript: redirect('host_screen.php?hostid=".$db_element['elementid']."');\", null,{'outer' : ['pum_o_item'],'inner' : ['pum_i_item']}],";
				}
			}
		}
		elseif ($db_element['elementtype'] == SYSMAP_ELEMENT_TYPE_MAP) {
			$links_menus .= "['"._('Submap')."',\"javascript: redirect('maps.php?sysmapid=".$db_element['elementid']."');\", null,{'outer' : ['pum_o_item'],'inner' : ['pum_i_item']}],";
		}
		elseif ($db_element['elementtype'] == SYSMAP_ELEMENT_TYPE_TRIGGER) {
			$links_menus .= "['"._('Latest events')."',\"javascript: redirect('events.php?source=0&triggerid=".$db_element['elementid']."&nav_time=".(time() - SEC_PER_WEEK)."');\", null,{'outer' : ['pum_o_item'],'inner' : ['pum_i_item']}],";
		}
		elseif ($db_element['elementtype'] == SYSMAP_ELEMENT_TYPE_HOST_GROUP) {
			$links_menus .= "['"._('Status of triggers')."',\"javascript: redirect('tr_status.php?hostid=0&groupid=".$db_element['elementid']."');\", null,{'outer' : ['pum_o_item'],'inner' : ['pum_i_item']}],";
		}

		if (!empty($links_menus)) {
			$menus .= "['"._('Go to')."',null,null,{'outer' : ['pum_oheader'],'inner' : ['pum_iheader']}],";
			$menus .= $links_menus;
		}

		if (!empty($db_element['urls'])) {
			order_result($db_element['urls'], 'name');
			$menus .= "['"._('URLs')."',null,null,{'outer' : ['pum_oheader'],'inner' : ['pum_iheader']}],";
			foreach ($db_element['urls'] as $url) {
				$menus .= "[".zbx_jsvalue($url['name']).",".zbx_jsvalue($url['url']).", 'nosid'],";
			}
		}

		$menus = trim($menus, ',');
		$menus = 'show_popup_menu(event,['.$menus.'],180); cancelEvent(event);';

		$back = get_png_by_selement($db_element, $map_info[$db_element['selementid']]);
		if (!$back) {
			continue;
		}
		$r_area = new CArea(
			array(
				$db_element['x'],
				$db_element['y'],
				$db_element['x'] + imagesx($back),
				$db_element['y'] + imagesy($back)),
			'', '', 'rect'
		);

		if (!empty($menus)) {
			$r_area->addAction('onclick', 'javascript: '.$menus);
		}

		$action_map->addItem($r_area);
	}

	return $action_map;
}

function get_icon_center_by_selement($element, $info, $map) {
	$x = $element['x'];
	$y = $element['y'];
	$w = $h = 0;

	if (isset($element['elementsubtype']) && $element['elementsubtype'] == SYSMAP_ELEMENT_SUBTYPE_HOST_GROUP_ELEMENTS) {
		if ($element['areatype'] == SYSMAP_ELEMENT_AREA_TYPE_CUSTOM) {
			$w = $element['width'];
			$h = $element['height'];
		}
		else {
			$w = $map['width'];
			$h = $map['height'];
		}
	}
	else {
		$image = get_png_by_selement($element, $info);
		if ($image) {
			$w = imagesx($image);
			$h = imagesy($image);
		}
	}

	$x += $w / 2;
	$y += $h / 2;

	return array($x, $y);
}

function MyDrawLine($image, $x1, $y1, $x2, $y2, $color, $drawtype) {
	if ($drawtype == MAP_LINK_DRAWTYPE_BOLD_LINE) {
		imageline($image, $x1, $y1, $x2, $y2, $color);
		if (abs($x1 - $x2) < abs($y1 - $y2)) {
			$x1++;
			$x2++;
		}
		else {
			$y1++;
			$y2++;
		}
		imageline($image, $x1, $y1, $x2, $y2, $color);
	}
	elseif ($drawtype == MAP_LINK_DRAWTYPE_DASHED_LINE) {
		if (function_exists('imagesetstyle')) {
			// use imagesetstyle + imageline instead of bugged ImageDashedLine
			$style = array(
				$color, $color, $color, $color,
				IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT
			);
			imagesetstyle($image, $style);
			imageline($image, $x1, $y1, $x2, $y2, IMG_COLOR_STYLED);
		}
		else {
			imagedashedline($image, $x1, $y1, $x2, $y2, $color);
		}
	}
	elseif ($drawtype == MAP_LINK_DRAWTYPE_DOT && function_exists('imagesetstyle')) {
		$style = array($color, IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT);
		imagesetstyle($image, $style);
		imageline($image, $x1, $y1, $x2, $y2, IMG_COLOR_STYLED);
	}
	else {
		imageline($image, $x1, $y1, $x2, $y2, $color);
	}
}

function get_png_by_selement($selement, $info) {
	$image = get_image_by_imageid($info['iconid']);
	if (!$image) {
		return get_default_image(true);
	}
	return imagecreatefromstring($image['image']);
}

function convertColor($im, $color) {
	$RGB = array(
		hexdec('0x'.substr($color, 0, 2)),
		hexdec('0x'.substr($color, 2, 2)),
		hexdec('0x'.substr($color, 4, 2))
	);
	return imagecolorallocate($im, $RGB[0], $RGB[1], $RGB[2]);
}

/**
 * Resolve macros and return expanded map label
 * @param array $selement
 * @return string
 */
function resolveMapLabelMacrosAll(array $selement) {
	$label = $selement['label'];

	$resolveHostMacros = false;
	if (($selement['elementtype'] == SYSMAP_ELEMENT_TYPE_HOST
			|| $selement['elementtype'] == SYSMAP_ELEMENT_TYPE_TRIGGER)
			&& (zbx_strpos($label, '{HOST.NAME}') !== false
					|| zbx_strpos($label, '{HOSTNAME}') !== false /* deprecated */
					|| zbx_strpos($label, '{HOST.HOST}') !== false
					|| zbx_strpos($label, '{HOST.DNS}') !== false
					|| zbx_strpos($label, '{HOST.IP}') !== false
					|| zbx_strpos($label, '{IPADDRESS}') !== false /* deprecated */
					|| zbx_strpos($label, '{HOST.CONN}') !== false)) {
		$resolveHostMacros = true;

		if ($selement['elementtype'] == SYSMAP_ELEMENT_TYPE_HOST) {
			$sql = 'SELECT hi.ip,hi.dns,hi.useip,h.host,h.name'.
					' FROM interface hi,hosts h'.
					' WHERE hi.hostid=h.hostid'.
					' AND hi.hostid='.$selement['elementid'];
		}
		else {
			$sql = 'SELECT hi.ip,hi.dns,hi.useip,h.host,h.name' .
					' FROM interface hi,items i,functions f,hosts h'.
					' WHERE h.hostid=hi.hostid'.
					' AND hi.hostid=i.hostid'.
					' AND i.itemid=f.itemid'.
					' AND f.triggerid='.$selement['elementid'];
		}
		$db_host = DBfetch(DBselect($sql));
	}

	$hostParam = ($resolveHostMacros && $selement['elementtype'] == SYSMAP_ELEMENT_TYPE_HOST) ? $db_host['host'] : null;
	$label = resolveMapLabelMacros($label, $hostParam);

	if ($resolveHostMacros) {
		$replace = array(
			'{HOST.NAME}' => $db_host['name'],
			'{HOSTNAME}' => $db_host['name'],
			'{HOST.HOST}' => $db_host['host'],
			'{HOST.DNS}' => $db_host['dns'],
			'{HOST.IP}' => $db_host['ip'],
			'{IPADDRESS}' => $db_host['ip'],
			'{HOST.CONN}' => $db_host['useip'] ? $db_host['ip'] : $db_host['dns'],
		);
		$label = str_replace(array_keys($replace), $replace, $label);
	}

	switch ($selement['elementtype']) {
		case SYSMAP_ELEMENT_TYPE_HOST:
		case SYSMAP_ELEMENT_TYPE_MAP:
		case SYSMAP_ELEMENT_TYPE_TRIGGER:
		case SYSMAP_ELEMENT_TYPE_HOST_GROUP:
			if (zbx_strpos($label, '{TRIGGERS.UNACK}') !== false) {
				$label = str_replace('{TRIGGERS.UNACK}', get_triggers_unacknowledged($selement), $label);
			}
			if (zbx_strpos($label, '{TRIGGERS.PROBLEM.UNACK}') !== false) {
				$label = str_replace('{TRIGGERS.PROBLEM.UNACK}', get_triggers_unacknowledged($selement, true), $label);
			}
			if (zbx_strpos($label, '{TRIGGER.EVENTS.UNACK}') !== false) {
				$label = str_replace('{TRIGGER.EVENTS.UNACK}', get_events_unacknowledged($selement), $label);
			}
			if (zbx_strpos($label, '{TRIGGER.EVENTS.PROBLEM.UNACK}') !== false) {
				$label = str_replace('{TRIGGER.EVENTS.PROBLEM.UNACK}', get_events_unacknowledged($selement, null, TRIGGER_VALUE_TRUE), $label);
			}
			if (zbx_strpos($label, '{TRIGGER.PROBLEM.EVENTS.PROBLEM.UNACK}') !== false) {
				$label = str_replace('{TRIGGER.PROBLEM.EVENTS.PROBLEM.UNACK}', get_events_unacknowledged($selement, TRIGGER_VALUE_TRUE, TRIGGER_VALUE_TRUE), $label);
			}
			if (zbx_strpos($label, '{TRIGGERS.ACK}') !== false) {
				$label = str_replace('{TRIGGERS.ACK}', get_triggers_unacknowledged($selement, null, true), $label);
			}
			if (zbx_strpos($label, '{TRIGGERS.PROBLEM.ACK}') !== false) {
				$label = str_replace('{TRIGGERS.PROBLEM.ACK}', get_triggers_unacknowledged($selement, true, true), $label);
			}
			if (zbx_strpos($label, '{TRIGGER.EVENTS.ACK}') !== false) {
				$label = str_replace('{TRIGGER.EVENTS.ACK}', get_events_unacknowledged($selement, null, null, true), $label);
			}
			if (zbx_strpos($label, '{TRIGGER.EVENTS.PROBLEM.ACK}') !== false) {
				$label = str_replace('{TRIGGER.EVENTS.PROBLEM.ACK}', get_events_unacknowledged($selement, null, TRIGGER_VALUE_TRUE, true), $label);
			}
			if (zbx_strpos($label, '{TRIGGER.PROBLEM.EVENTS.PROBLEM.ACK}') !== false) {
				$label = str_replace('{TRIGGER.PROBLEM.EVENTS.PROBLEM.ACK}', get_events_unacknowledged($selement, TRIGGER_VALUE_TRUE, TRIGGER_VALUE_TRUE, true), $label);
			}
			break;
	}
	return $label;
}

function resolveMapLabelMacros($label, $replaceHost = null) {
	if (null === $replaceHost) {
		$pattern = "/{".ZBX_PREG_HOST_FORMAT.":.+\.(last|max|min|avg)\([0-9]+\)}/Uu";
	}
	else {
		$pattern = "/{(".ZBX_PREG_HOST_FORMAT."|{HOSTNAME}|{HOST.HOST}):.+\.(last|max|min|avg)\([0-9]+\)}/Uu";
	}

	preg_match_all($pattern, $label, $matches);

	foreach ($matches[0] as $expr) {
		$macro = $expr;
		if ($replaceHost !== null) {
			if (zbx_strpos($macro, '{HOSTNAME}') == 1) {
				$macro = substr_replace($macro, $replaceHost, 1, 10);
			}
			elseif (zbx_strpos($macro, '{HOST.HOST}') == 1) {
				$macro = substr_replace($macro, $replaceHost, 1, 11);
			}
		}

		$trigExpr = new CTriggerExpression(array('expression' => $macro));
		if (!empty($trigExpr->errors)) {
			continue;
		}

		$itemHost = reset($trigExpr->data['hosts']);
		$key = reset($trigExpr->data['items']);
		$function = reset($trigExpr->data['functions']);
		$parameter = reset($trigExpr->data['functionParams']);

		$item = API::Item()->get(array(
			'filter' => array(
				'host' => $itemHost,
				'key_' => $key,
				'flags' => array(ZBX_FLAG_DISCOVERY_NORMAL, ZBX_FLAG_DISCOVERY_CREATED)
			),
			'output' => API_OUTPUT_EXTEND
		));
		$item = reset($item);
		if (!$item) {
			$label = str_replace($expr, '???', $label);
			continue;
		}

		switch ($item['value_type']) {
			case ITEM_VALUE_TYPE_FLOAT:
				$history_table = 'history';
				break;
			case ITEM_VALUE_TYPE_UINT64:
				$history_table = 'history_uint';
				break;
			case ITEM_VALUE_TYPE_TEXT:
				$history_table = 'history_text';
				break;
			case ITEM_VALUE_TYPE_LOG:
				$history_table = 'history_log';
				break;
			case ITEM_VALUE_TYPE_STR:
				$history_table = 'history_str';
				break;
			default:
				$history_table = 'history_str';
		}

		if (0 == strcmp($function, 'last')) {
			if (null === $item['lastvalue']) {
				$label = str_replace($expr, '('._('no data').')', $label);
			}
			else {
				switch ($item['value_type']) {
					case ITEM_VALUE_TYPE_FLOAT:
					case ITEM_VALUE_TYPE_UINT64:
						$value = convert_units($item['lastvalue'], $item['units']);
						break;
					default:
						$value = $item['lastvalue'];
				}
				$label = str_replace($expr, $value, $label);
			}
		}
		elseif (0 == strcmp($function, 'min') || 0 == strcmp($function, 'max') || 0 == strcmp($function, 'avg')) {
			if ($item['value_type'] != ITEM_VALUE_TYPE_FLOAT && $item['value_type'] != ITEM_VALUE_TYPE_UINT64) {
				$label = str_replace($expr, '???', $label);
				continue;
			}

			$result = DBselect(
				'SELECT '.$function.'(value) AS value'.
				' FROM '.$history_table.
				' WHERE clock>'.(time() - $parameter).
				' AND itemid='.$item['itemid']
			);
			if (null === ($row = DBfetch($result)) || null === $row['value']) {
				$label = str_replace($expr, '('._('no data').')', $label);
			}
			else {
				$label = str_replace($expr, convert_units($row['value'], $item['units']), $label);
			}
		}
	}
	return $label;
}

function get_map_elements($db_element, &$elements) {
	switch ($db_element['elementtype']) {
		case SYSMAP_ELEMENT_TYPE_HOST_GROUP:
			$elements['hosts_groups'][] = $db_element['elementid'];
			break;
		case SYSMAP_ELEMENT_TYPE_HOST:
			$elements['hosts'][] = $db_element['elementid'];
			break;
		case SYSMAP_ELEMENT_TYPE_TRIGGER:
			$elements['triggers'][] = $db_element['elementid'];
			break;
		case SYSMAP_ELEMENT_TYPE_MAP:
			$db_mapselements = DBselect(
				'SELECT DISTINCT se.elementtype,se.elementid'.
				' FROM sysmaps_elements se'.
				' WHERE se.sysmapid='.$db_element['elementid']
			);
			while ($db_mapelement = DBfetch($db_mapselements)) {
				get_map_elements($db_mapelement, $elements);
			}
			break;
	}
}

function add_elementNames(&$selements) {
	$hostids = array();
	$triggerids = array();
	$mapids = array();
	$hostgroupids = array();
	$imageids = array();

	foreach ($selements as $selement) {
		switch ($selement['elementtype']) {
			case SYSMAP_ELEMENT_TYPE_HOST:
				$hostids[] = $selement['elementid'];
				break;
			case SYSMAP_ELEMENT_TYPE_MAP:
				$mapids[] = $selement['elementid'];
				break;
			case SYSMAP_ELEMENT_TYPE_TRIGGER:
				$triggerids[] = $selement['elementid'];
				break;
			case SYSMAP_ELEMENT_TYPE_HOST_GROUP:
				$hostgroupids[] = $selement['elementid'];
				break;
			case SYSMAP_ELEMENT_TYPE_IMAGE:
				$imageids[] = $selement['iconid_off'];
				break;
		}
	}

	$hosts = API::Host()->get(array(
		'hostids' => $hostids,
		'output' => array('name'),
		'nopermissions' => true,
		'nodeids' => get_current_nodeid(true),
		'preservekeys' => true
	));

	$maps = API::Map()->get(array(
		'mapids' => $mapids,
		'output' => array('name'),
		'nopermissions' => true,
		'nodeids' => get_current_nodeid(true),
		'preservekeys' => true
	));

	$triggers = API::Trigger()->get(array(
		'triggerids' => $triggerids,
		'output' => API_OUTPUT_EXTEND,
		'selectHosts' => array('name'),
		'nopermissions' => true,
		'nodeids' => get_current_nodeid(true),
		'preservekeys' => true
	));

	$hostgroups = API::HostGroup()->get(array(
		'hostgroupids' => $hostgroupids,
		'output' => array('name'),
		'nopermissions' => true,
		'nodeids' => get_current_nodeid(true),
		'preservekeys' => true
	));

	$images = API::image()->get(array(
		'imageids' => $imageids,
		'output' => API_OUTPUT_EXTEND,
		'nopermissions' => true,
		'nodeids' => get_current_nodeid(true),
		'preservekeys' => true
	));

	foreach ($selements as $snum => $selement) {
		switch ($selement['elementtype']) {
			case SYSMAP_ELEMENT_TYPE_HOST:
				$selements[$snum]['elementName'] = $hosts[$selement['elementid']]['name'];
				break;
			case SYSMAP_ELEMENT_TYPE_MAP:
				$selements[$snum]['elementName'] = $maps[$selement['elementid']]['name'];
				break;
			case SYSMAP_ELEMENT_TYPE_TRIGGER:
				$hostname = reset($triggers[$selement['elementid']]['hosts']);
				$selements[$snum]['elementName'] = $hostname['name'].':'.expand_trigger_description_by_data($triggers[$selement['elementid']]);
				break;
			case SYSMAP_ELEMENT_TYPE_HOST_GROUP:
				$selements[$snum]['elementName'] = $hostgroups[$selement['elementid']]['name'];
				break;
			case SYSMAP_ELEMENT_TYPE_IMAGE:
				$selements[$snum]['elementName'] = $images[$selement['iconid_off']]['name'];
				break;
		}
	}
}

function getTriggersInfo($selement, $i) {
	global $colors;

	$info = array(
		'latelyChanged' => $i['latelyChanged'],
		'ack' => $i['ack'],
		'priority' => $i['priority']
	);

	if ($i['problem']) {
		$info['iconid'] = $selement['iconid_on'];
		$info['icon_type'] = SYSMAP_ELEMENT_ICON_ON;
		$info['info'] = array();
		$info['info']['unack'] = array(
			'msg' => _('PROBLEM'),
			'color' => $i['priority'] > 3 ? $colors['Red'] : $colors['Dark Red']
		);
	}
	elseif ($i['trigger_disabled']) {
		$info['iconid'] = $selement['iconid_disabled'];
		$info['icon_type'] = SYSMAP_ELEMENT_ICON_DISABLED;
		$info['info'] = array(
			'status' => array(
				'msg' => _('DISABLED'),
				'color' => $colors['Dark Red']
			)
		);
	}
	else {
		$info['iconid'] = $selement['iconid_off'];
		$info['icon_type'] = SYSMAP_ELEMENT_ICON_OFF;
		$info['info'] = array(
			'unknown' => array(
				'msg' => _('OK'),
				'color' => $colors['Dark Green']
			)
		);
	}

	if ($info['iconid'] == 0) {
		$info['iconid'] = $selement['iconid_off'];
	}

	return $info;
}

function getHostsInfo($selement, $i, $show_unack) {
	global $colors;

	$info = array(
		'latelyChanged' => $i['latelyChanged'],
		'ack' => $i['ack'],
		'priority' => $i['priority'],
		'info' => array()
	);
	$has_problem = false;

	if ($i['problem']) {
		$info['iconid'] = $selement['iconid_on'];
		$info['icon_type'] = SYSMAP_ELEMENT_ICON_ON;

		if (in_array($show_unack, array(EXTACK_OPTION_ALL, EXTACK_OPTION_BOTH))) {
			if ($i['problem'] > 1) {
				$msg = $i['problem'].' '._('Problems');
			}
			elseif (isset($i['problem_title'])) {
				$msg = $i['problem_title'];
			}
			else {
				$msg = '1 '._('Problem');
			}

			$info['info']['problem'] = array(
				'msg' => $msg,
				'color' => $i['priority'] > 3 ? $colors['Red'] : $colors['Dark Red']
			);
		}

		if (in_array($show_unack, array(EXTACK_OPTION_UNACK, EXTACK_OPTION_BOTH)) && $i['problem_unack']) {
			$info['info']['unack'] = array(
				'msg' => $i['problem_unack'].' '._('Unacknowledged'),
				'color' => $colors['Dark Red']
			);
		}

		if ($i['unknown']) {
			$info['info']['unknown'] = array(
				'msg' => $i['unknown'].' '._('Unknown'),
				'color' => $colors['Gray']
			);
		}
		$has_problem = true;
	}

	if ($i['maintenance']) {
		$info['iconid'] = $selement['iconid_maintenance'];
		$info['icon_type'] = SYSMAP_ELEMENT_ICON_MAINTENANCE;
		$info['info']['maintenance'] = array(
			'msg' => _('MAINTENANCE').' ('.$i['maintenance_title'].')',
			'color' => $colors['Orange'],
		);
	}
	elseif ($i['disabled']) {
		$info['iconid'] = $selement['iconid_disabled'];
		$info['icon_type'] = SYSMAP_ELEMENT_ICON_DISABLED;
		$info['info']['status'] = array(
			'msg' => _('DISABLED'),
			'color' => $colors['Dark Red']
		);
	}
	elseif (!$has_problem) {
		$info['iconid'] = $selement['iconid_off'];
		$info['icon_type'] = SYSMAP_ELEMENT_ICON_OFF;
		$info['info']['unknown'] = array(
			'msg' => _('OK'),
			'color' => $colors['Dark Green'],
		);
	}

	if ($info['iconid'] == 0) {
		$info['iconid'] = $selement['iconid_off'];
	}

	return $info;
}

function getHostGroupsInfo($selement, $i, $show_unack) {
	global $colors;

	$info = array(
		'latelyChanged' => $i['latelyChanged'],
		'ack' => $i['ack'],
		'priority' => $i['priority'],
		'info' => array()
	);
	$has_problem = false;
	$has_status = false;

	if ($i['problem']) {
		$info['iconid'] = $selement['iconid_on'];
		$info['icon_type'] = SYSMAP_ELEMENT_ICON_ON;

		if (in_array($show_unack, array(EXTACK_OPTION_ALL, EXTACK_OPTION_BOTH))) {
			if ($i['problem'] > 1) {
				$msg = $i['problem'].' '._('Problems');
			}
			elseif (isset($i['problem_title'])) {
				$msg = $i['problem_title'];
			}
			else {
				$msg = '1 '._('Problem');
			}

			$info['info']['problem'] = array(
				'msg' => $msg,
				'color' => ($i['priority'] > 3) ? $colors['Red'] : $colors['Dark Red']
			);
		}

		if (in_array($show_unack, array(EXTACK_OPTION_UNACK, EXTACK_OPTION_BOTH)) && $i['problem_unack']) {
			$info['info']['unack'] = array(
				'msg' => $i['problem_unack'].' '._('Unacknowledged'),
				'color' => $colors['Dark Red']
			);
		}

		if ($i['unknown']) {
			$info['info']['unknown'] = array(
				'msg' => $i['unknown'].' '._('Unknown'),
				'color' => $colors['Gray']
			);
		}
		$has_problem = true;
	}

	if ($i['maintenance']) {
		if (!$has_problem) {
			$info['iconid'] = $selement['iconid_maintenance'];
			$info['icon_type'] = SYSMAP_ELEMENT_ICON_MAINTENANCE;
		}
		$info['info']['maintenance'] = array(
			'msg' => $i['maintenance'].' '._('Maintenance'),
			'color' => $colors['Orange']
		);
		$has_status = true;
	}
	elseif ($i['disabled']) {
		if (!$has_problem) {
			$info['icon_type'] = SYSMAP_ELEMENT_ICON_DISABLED;
			$info['iconid'] = $selement['iconid_disabled'];
		}
		$info['info']['disabled'] = array(
			'msg' => _('DISABLED'),
			'color' => $colors['Dark Red']
		);
		$has_status = true;
	}

	if (!$has_status && !$has_problem) {
		$info['icon_type'] = SYSMAP_ELEMENT_ICON_OFF;
		$info['iconid'] = $selement['iconid_off'];
		$info['info']['unknown'] = array(
			'msg' => _('OK'),
			'color' => $colors['Dark Green']
		);
	}

	if ($info['iconid'] == 0) {
		$info['iconid'] = $selement['iconid_off'];
	}

	return $info;
}

function getMapsInfo($selement, $i, $show_unack) {
	global $colors;

	$info = array(
		'latelyChanged' => $i['latelyChanged'],
		'ack' => $i['ack'],
		'priority' => $i['priority'],
		'info' => array()
	);

	$has_problem = false;
	$has_status = false;

	if ($i['problem']) {
		$info['iconid'] = $selement['iconid_on'];
		$info['icon_type'] = SYSMAP_ELEMENT_ICON_ON;
		if (in_array($show_unack, array(EXTACK_OPTION_ALL, EXTACK_OPTION_BOTH))) {
			if ($i['problem'] > 1) {
				$msg = $i['problem'].' '._('Problems');
			}
			elseif (isset($i['problem_title'])) {
				$msg = $i['problem_title'];
			}
			else {
				$msg = '1 '._('Problem');
			}

			$info['info']['problem'] = array(
				'msg' => $msg,
				'color' => ($i['priority'] > 3) ? $colors['Red'] : $colors['Dark Red']
			);
		}

		if (in_array($show_unack, array(EXTACK_OPTION_UNACK, EXTACK_OPTION_BOTH)) && $i['problem_unack']) {
			$info['info']['unack'] = array(
				'msg' => $i['problem_unack'].' '._('Unacknowledged'),
				'color' => $colors['Dark Red']
			);
		}

		if ($i['unknown']) {
			$info['info']['unknown'] = array(
				'msg' => $i['unknown'].' '._('Unknown'),
				'color' => $colors['Gray']
			);
		}
		$has_problem = true;
	}

	if ($i['maintenance']) {
		if (!$has_problem) {
			$info['iconid'] = $selement['iconid_maintenance'];
			$info['icon_type'] = SYSMAP_ELEMENT_ICON_MAINTENANCE;
		}
		$info['info']['maintenance'] = array(
			'msg' => $i['maintenance'].' '._('Maintenance'),
			'color' => $colors['Orange']
		);
		$has_status = true;
	}
	elseif ($i['disabled']) {
		if (!$has_problem) {
			$info['icon_type'] = SYSMAP_ELEMENT_ICON_DISABLED;
			$info['iconid'] = $selement['iconid_disabled'];
		}
		$info['info']['disabled'] = array(
			'msg' => _('DISABLED'),
			'color' => $colors['Dark Red']
		);
		$has_status = true;
	}

	if (!$has_status && !$has_problem) {
		$info['icon_type'] = SYSMAP_ELEMENT_ICON_OFF;
		$info['iconid'] = $selement['iconid_off'];
		$info['info']['unknown'] = array(
			'msg' => _('OK'),
			'color' => $colors['Dark Green']
		);
	}

	if ($info['iconid'] == 0) {
		$info['iconid'] = $selement['iconid_off'];
	}

	return $info;
}

function getImagesInfo($selement) {
	return array(
		'iconid' => $selement['iconid_off'],
		'icon_type' => SYSMAP_ELEMENT_ICON_OFF,
		'name' => _('Image'),
		'latelyChanged' => false
	);
}

function getSelementsInfo($sysmap) {
	$config = select_config();
	$show_unack = $config['event_ack_enable'] ? $sysmap['show_unack'] : EXTACK_OPTION_ALL;

	$triggers_map = array();
	$triggers_map_submaps = array();
	$hostgroups_map = array();
	$hosts_map = array();

	if ($sysmap['sysmapid']) {
		$iconMap = API::IconMap()->get(array(
			'sysmapids' => $sysmap['sysmapid'],
			'selectMappings' => API_OUTPUT_EXTEND,
			'output' => API_OUTPUT_EXTEND
		));
		$iconMap = reset($iconMap);
		$hostsToGetInventories = array();
	}

	$selements = zbx_toHash($sysmap['selements'], 'selementid');
	foreach ($selements as $selementid => $selement) {
		$selements[$selementid]['hosts'] = array();
		$selements[$selementid]['triggers'] = array();

		switch ($selement['elementtype']) {
			case SYSMAP_ELEMENT_TYPE_MAP:
				$mapids = array($selement['elementid']);

				while (!empty($mapids)) {
					$maps = API::Map()->get(array(
						'sysmapids' => $mapids,
						'output' => API_OUTPUT_REFER,
						'selectSelements' => API_OUTPUT_EXTEND,
						'nopermissions' => true,
						'nodeids' => get_current_nodeid(true)
					));

					$mapids = array();
					foreach ($maps as $map) {
						foreach ($map['selements'] as $sel) {
							switch ($sel['elementtype']) {
								case SYSMAP_ELEMENT_TYPE_MAP:
									$mapids[] = $sel['elementid'];
									break;
								case SYSMAP_ELEMENT_TYPE_HOST_GROUP:
									$hostgroups_map[$sel['elementid']][$selementid] = $selementid;
									break;
								case SYSMAP_ELEMENT_TYPE_HOST:
									$hosts_map[$sel['elementid']][$selementid] = $selementid;
									break;
								case SYSMAP_ELEMENT_TYPE_TRIGGER:
									$triggers_map_submaps[$sel['elementid']][$selementid] = $selementid;
									break;
							}
						}
					}
				}
				break;
			case SYSMAP_ELEMENT_TYPE_HOST_GROUP:
				$hostgroups_map[$selement['elementid']][$selement['selementid']] = $selement['selementid'];
				break;
			case SYSMAP_ELEMENT_TYPE_HOST:
				$hosts_map[$selement['elementid']][$selement['selementid']] = $selement['selementid'];

				// if we have icon map applied, we need to get inventories for all hosts,
				// where automatic icon selection is enabled.
				if ($sysmap['iconmapid'] && $selement['use_iconmap']) {
					$hostsToGetInventories[] = $selement['elementid'];
				}
				break;
			case SYSMAP_ELEMENT_TYPE_TRIGGER:
				$triggers_map[$selement['elementid']][$selement['selementid']] = $selement['selementid'];
				break;
		}
	}

	// get hosts data
	if ($sysmap['iconmapid']) {
		$hostInventories = API::Host()->get(array(
			'hostids' => $hostsToGetInventories,
			'output' => API_OUTPUT_SHORTEN,
			'nopermissions' => true,
			'preservekeys' => true,
			'selectInventory' => true
		));
	}

	$all_hosts = array();
	if (!empty($hosts_map)) {
		$hosts = API::Host()->get(array(
			'hostids' => array_keys($hosts_map),
			'output' => array('name', 'status', 'maintenance_status', 'maintenanceid'),
			'nopermissions' => true,
			'nodeids' => get_current_nodeid(true)
		));
		$all_hosts = array_merge($all_hosts, $hosts);
		foreach ($hosts as $host) {
			foreach ($hosts_map[$host['hostid']] as $belongs_to_sel) {
				$selements[$belongs_to_sel]['hosts'][$host['hostid']] = $host['hostid'];
			}
		}
	}

	if (!empty($hostgroups_map)) {
		$hosts = API::Host()->get(array(
			'groupids' => array_keys($hostgroups_map),
			'output' => array('name', 'status', 'maintenance_status', 'maintenanceid'),
			'nopermissions' => true,
			'nodeids' => get_current_nodeid(true)
		));
		$all_hosts = array_merge($all_hosts, $hosts);
		foreach ($hosts as $host) {
			foreach ($host['groups'] as $group) {
				foreach ($hostgroups_map[$group['groupid']] as $belongs_to_sel) {
					$selements[$belongs_to_sel]['hosts'][$host['hostid']] = $host['hostid'];

					// add hosts to hosts_map for trigger selection;
					if (!isset($hosts_map[$host['hostid']])) {
						$hosts_map[$host['hostid']] = array();
					}
					$hosts_map[$host['hostid']][$belongs_to_sel] = $belongs_to_sel;
				}
			}
		}
	}
	$all_hosts = zbx_toHash($all_hosts, 'hostid');

	$monitored_hostids = array();
	foreach ($all_hosts as $hostid => $host) {
		if ($host['status'] == HOST_STATUS_MONITORED) {
			$monitored_hostids[$hostid] = $hostid;
		}
	}

	// get triggers data, triggers from current map, select all
	$all_triggers = array();

	if (!empty($triggers_map)) {
		$triggers = API::Trigger()->get(array(
			'nodeids' => get_current_nodeid(true),
			'triggerids' => array_keys($triggers_map),
			'filter' => array('value_flags' => null),
			'output' => API_OUTPUT_EXTEND,
			'nopermissions' => true
		));
		$all_triggers = array_merge($all_triggers, $triggers);

		foreach ($triggers as $trigger) {
			foreach ($triggers_map[$trigger['triggerid']] as $belongs_to_sel) {
				$selements[$belongs_to_sel]['triggers'][$trigger['triggerid']] = $trigger['triggerid'];
			}
		}
	}

	// triggers from submaps, skip dependent
	if (!empty($triggers_map_submaps)) {
		$triggers = API::Trigger()->get(array(
			'nodeids' => get_current_nodeid(true),
			'triggerids' => array_keys($triggers_map_submaps),
			'filter' => array('value_flags' => null),
			'skipDependent' => true,
			'output' => API_OUTPUT_EXTEND,
			'nopermissions' => true
		));
		$all_triggers = array_merge($all_triggers, $triggers);

		foreach ($triggers as $trigger) {
			foreach ($triggers_map_submaps[$trigger['triggerid']] as $belongs_to_sel) {
				$selements[$belongs_to_sel]['triggers'][$trigger['triggerid']] = $trigger['triggerid'];
			}
		}
	}

	// triggers from all hosts/hostgroups, skip dependent
	if (!empty($monitored_hostids)) {
		$triggers = API::Trigger()->get(array(
			'hostids' => $monitored_hostids,
			'output' => array('status', 'value', 'priority', 'lastchange', 'description', 'expression'),
			'nopermissions' => true,
			'filter' => array('value_flags' => null),
			'nodeids' => get_current_nodeid(true),
			'monitored' => true,
			'skipDependent' => true
		));
		$all_triggers = array_merge($all_triggers, $triggers);

		foreach ($triggers as $trigger) {
			foreach ($trigger['hosts'] as $host) {
				foreach ($hosts_map[$host['hostid']] as $belongs_to_sel) {
					$selements[$belongs_to_sel]['triggers'][$trigger['triggerid']] = $trigger['triggerid'];
				}
			}
		}
	}
	$all_triggers = zbx_toHash($all_triggers, 'triggerid');

	$unack_triggerids = API::Trigger()->get(array(
		'triggerids' => array_keys($all_triggers),
		'withLastEventUnacknowledged' => true,
		'output' => API_OUTPUT_SHORTEN,
		'nodeids' => get_current_nodeid(true),
		'nopermissions' => true,
		'monitored' => true,
		'filter' => array('value' => TRIGGER_VALUE_TRUE, 'value_flags' => null)
	));
	$unack_triggerids = zbx_toHash($unack_triggerids, 'triggerid');

	$info = array();
	foreach ($selements as $selementid => $selement) {
		$i = array(
			'disabled' => 0,
			'maintenance' => 0,
			'problem' => 0,
			'problem_unack' => 0,
			'unknown' => 0,
			'priority' => 0,
			'trigger_disabled' => 0,
			'latelyChanged' => false,
			'ack' => true
		);

		foreach ($selement['hosts'] as $hostid) {
			$host = $all_hosts[$hostid];
			$last_hostid = $hostid;

			if ($host['status'] == HOST_STATUS_NOT_MONITORED) {
				$i['disabled']++;
			}
			elseif ($host['maintenance_status'] == HOST_MAINTENANCE_STATUS_ON) {
				$i['maintenance']++;
			}
		}

		foreach ($selement['triggers'] as $triggerid) {
			$trigger = $all_triggers[$triggerid];

			if ($trigger['status'] == TRIGGER_STATUS_DISABLED) {
				$i['trigger_disabled']++;
			}
			else {
				if ($trigger['value'] == TRIGGER_VALUE_TRUE) {
					$i['problem']++;
					$last_problemid = $triggerid;
					if ($i['priority'] < $trigger['priority']) {
						$i['priority'] = $trigger['priority'];
					}
				}

				if (isset($unack_triggerids[$triggerid])) {
					$i['problem_unack']++;
				}

				$config = select_config();
				$i['latelyChanged'] |= ((time() - $trigger['lastchange']) < $config['blink_period']);
			}
		}

		$i['ack'] = (bool) !($i['problem_unack']);

		if ($sysmap['expandproblem'] && ($i['problem'] == 1)) {
			$i['problem_title'] = expand_trigger_description_by_data($all_triggers[$last_problemid]);
		}

		if ($selement['elementtype'] == SYSMAP_ELEMENT_TYPE_HOST && $i['maintenance'] == 1) {
			$mnt = get_maintenance_by_maintenanceid($all_hosts[$last_hostid]['maintenanceid']);
			$i['maintenance_title'] = $mnt['name'];
		}

		switch ($selement['elementtype']) {
			case SYSMAP_ELEMENT_TYPE_MAP:
				$info[$selementid] = getMapsInfo($selement, $i, $show_unack);
				break;
			case SYSMAP_ELEMENT_TYPE_HOST_GROUP:
				$info[$selementid] = getHostGroupsInfo($selement, $i, $show_unack);
				break;
			case SYSMAP_ELEMENT_TYPE_HOST:
				$info[$selementid] = getHostsInfo($selement, $i, $show_unack);

				if ($sysmap['iconmapid'] && $selement['use_iconmap']) {
					$info[$selementid]['iconid'] = getIconByMapping($iconMap, $hostInventories[$selement['elementid']]);
				}
				break;
			case SYSMAP_ELEMENT_TYPE_TRIGGER:
				$info[$selementid] = getTriggersInfo($selement, $i);
				break;
			case SYSMAP_ELEMENT_TYPE_IMAGE:
				$info[$selementid] = getImagesInfo($selement);
				break;
		}
	}

	if ($sysmap['label_format'] == SYSMAP_LABEL_ADVANCED_OFF) {
		$hlabel = $hglabel = $tlabel = $mlabel = ($sysmap['label_type'] == MAP_LABEL_TYPE_NAME);
	}
	else {
		$hlabel = $sysmap['label_type_host'] == MAP_LABEL_TYPE_NAME;
		$hglabel = $sysmap['label_type_hostgroup'] == MAP_LABEL_TYPE_NAME;
		$tlabel = $sysmap['label_type_trigger'] == MAP_LABEL_TYPE_NAME;
		$mlabel = $sysmap['label_type_map'] == MAP_LABEL_TYPE_NAME;
	}

	// get names if needed
	$elems = separateMapElements($sysmap);
	if (!empty($elems['sysmaps']) && $mlabel) {
		$maps = API::Map()->get(array(
			'sysmapids' => zbx_objectValues($elems['sysmaps'], 'elementid'),
			'nopermissions' => true,
			'output' => array('name')
		));
		$maps = zbx_toHash($maps, 'sysmapid');
		foreach ($elems['sysmaps'] as $elem) {
			$info[$elem['selementid']]['name'] = $maps[$elem['elementid']]['name'];
		}
	}
	if (!empty($elems['hostgroups']) && $hglabel) {
		$hostgroups = API::HostGroup()->get(array(
			'groupids' => zbx_objectValues($elems['hostgroups'], 'elementid'),
			'nopermissions' => true,
			'output' => array('name')
		));
		$hostgroups = zbx_toHash($hostgroups, 'groupid');
		foreach ($elems['hostgroups'] as $elem) {
			$info[$elem['selementid']]['name'] = $hostgroups[$elem['elementid']]['name'];
		}
	}

	if (!empty($elems['triggers']) && $tlabel) {
		foreach ($elems['triggers'] as $elem) {
			$info[$elem['selementid']]['name'] = expand_trigger_description_by_data($all_triggers[$elem['elementid']]);
		}
	}
	if (!empty($elems['hosts']) && $hlabel) {
		foreach ($elems['hosts'] as $elem) {
			$info[$elem['selementid']]['name'] = $all_hosts[$elem['elementid']]['name'];
		}
	}

	return $info;
}

function separateMapElements($sysmap) {
	$elements = array(
		'sysmaps' => array(),
		'hostgroups' => array(),
		'hosts' => array(),
		'triggers' => array(),
		'images' => array()
	);

	foreach ($sysmap['selements'] as $selement) {
		switch ($selement['elementtype']) {
			case SYSMAP_ELEMENT_TYPE_MAP:
				$elements['sysmaps'][$selement['selementid']] = $selement;
				break;
			case SYSMAP_ELEMENT_TYPE_HOST_GROUP:
				$elements['hostgroups'][$selement['selementid']] = $selement;
				break;
			case SYSMAP_ELEMENT_TYPE_HOST:
				$elements['hosts'][$selement['selementid']] = $selement;
				break;
			case SYSMAP_ELEMENT_TYPE_TRIGGER:
				$elements['triggers'][$selement['selementid']] = $selement;
				break;
			case SYSMAP_ELEMENT_TYPE_IMAGE:
			default:
				$elements['images'][$selement['selementid']] = $selement;
		}
	}
	return $elements;
}

function drawMapConnectors(&$im, $map, $map_info, $drawAll = false) {
	$selements = $map['selements'];

	foreach ($map['links'] as $link) {
		$selement1 = $selements[$link['selementid1']];
		$selement2 = $selements[$link['selementid2']];

		list($x1, $y1) = get_icon_center_by_selement($selement1, $map_info[$link['selementid1']], $map);
		list($x2, $y2) = get_icon_center_by_selement($selement2, $map_info[$link['selementid2']], $map);

		if (isset($selement1['elementsubtype']) && $selement1['elementsubtype'] == SYSMAP_ELEMENT_AREA_TYPE_CUSTOM) {
			if (!$drawAll && ($selement2['elementsubtype'] != SYSMAP_ELEMENT_SUBTYPE_HOST_GROUP_ELEMENTS)) {
				continue;
			}

			if ($selement1['areatype'] == SYSMAP_ELEMENT_AREA_TYPE_CUSTOM) {
				$w = $selement1['width'];
				$h = $selement1['height'];
			}
			else {
				$w = $map['width'];
				$h = $map['height'];
			}
			list($x1, $y1) = calculateMapAreaLinkCoord($x1, $y1, $w, $h, $x2, $y2);
		}

		if (isset($selement2['elementsubtype']) && $selement2['elementsubtype'] == SYSMAP_ELEMENT_AREA_TYPE_CUSTOM) {
			if (!$drawAll && ($selement1['elementsubtype'] != SYSMAP_ELEMENT_SUBTYPE_HOST_GROUP_ELEMENTS)) {
				continue;
			}

			if ($selement2['areatype'] == SYSMAP_ELEMENT_AREA_TYPE_CUSTOM) {
				$w = $selement2['width'];
				$h = $selement2['height'];
			}
			else {
				$w = $map['width'];
				$h = $map['height'];
			}
			list($x2, $y2) = calculateMapAreaLinkCoord($x2, $y2, $w,$h, $x1, $y1);
		}

		$drawtype = $link['drawtype'];
		$color = convertColor($im, $link['color']);

		$linktriggers = $link['linktriggers'];
		order_result($linktriggers, 'triggerid');

		if (!empty($linktriggers)) {
			$max_severity = 0;

			$triggers = array();
			foreach ($linktriggers as $link_trigger) {
				if ($link_trigger['triggerid'] == 0) {
					continue;
				}

				$id = $link_trigger['linktriggerid'];

				$triggers[$id] = zbx_array_merge($link_trigger, get_trigger_by_triggerid($link_trigger['triggerid']));
				if ($triggers[$id]['status'] == TRIGGER_STATUS_ENABLED && $triggers[$id]['value'] == TRIGGER_VALUE_TRUE) {
					if ($triggers[$id]['priority'] >= $max_severity) {
						$drawtype = $triggers[$id]['drawtype'];
						$color = convertColor($im, $triggers[$id]['color']);
						$max_severity = $triggers[$id]['priority'];
					}
				}
			}
		}
		MyDrawLine($im, $x1, $y1, $x2, $y2, $color, $drawtype);
	}
}

function drawMapSelements(&$im, $map, $map_info) {
	$selements = $map['selements'];

	foreach ($selements as $selementid => $selement) {
		if (isset($selement['elementsubtype']) && $selement['elementsubtype'] == SYSMAP_ELEMENT_SUBTYPE_HOST_GROUP_ELEMENTS) {
			continue;
		}

		$el_info = $map_info[$selementid];
		$img = get_png_by_selement($selement, $el_info);

		$iconX = imagesx($img);
		$iconY = imagesy($img);

		imagecopy($im, $img, $selement['x'], $selement['y'], 0, 0, $iconX, $iconY);
	}
}

function drawMapHighligts(&$im, $map, $map_info) {
	$selements = $map['selements'];

	foreach ($selements as $selementid => $selement) {
		if (isset($selement['elementsubtype']) && $selement['elementsubtype'] == SYSMAP_ELEMENT_SUBTYPE_HOST_GROUP_ELEMENTS) {
			continue;
		}

		$el_info = $map_info[$selementid];
		$img = get_png_by_selement($selement, $el_info);

		$iconX = imagesx($img);
		$iconY = imagesy($img);

		if (($map['highlight'] % 2) == SYSMAP_HIGHLIGHT_ON) {
			$hl_color = null;
			$st_color = null;

			if ($el_info['icon_type'] == SYSMAP_ELEMENT_ICON_ON) {
				$hl_color = hex2rgb(getSeverityColor($el_info['priority']));
			}

			if ($el_info['icon_type'] == SYSMAP_ELEMENT_ICON_MAINTENANCE) {
				$st_color = hex2rgb('FF9933');
			}
			if ($el_info['icon_type'] == SYSMAP_ELEMENT_ICON_DISABLED) {
				$st_color = hex2rgb('EEEEEE');
			}

			$mainProblems = array(
				SYSMAP_ELEMENT_TYPE_HOST_GROUP => 1,
				SYSMAP_ELEMENT_TYPE_MAP => 1
			);

			if (isset($mainProblems[$selement['elementtype']])) {
				if (!is_null($hl_color)) {
					$st_color = null;
				}
			}
			elseif (!is_null($st_color)) {
				$hl_color = null;
			}

			if (!is_null($st_color)) {
				$r = $st_color[0];
				$g = $st_color[1];
				$b = $st_color[2];

				imagefilledrectangle($im,
					$selement['x'] - 2,
					$selement['y'] - 2,
					$selement['x'] + $iconX + 2,
					$selement['y'] + $iconY + 2,
					imagecolorallocatealpha($im, $r, $g, $b, 0)
				);

				// shadow
				imagerectangle($im,
					$selement['x'] - 2 - 1,
					$selement['y'] - 2 - 1,
					$selement['x'] + $iconX + 2 + 1,
					$selement['y'] + $iconY + 2 + 1,
					imagecolorallocate($im, 120, 120, 120)
				);

				imagerectangle($im,
					$selement['x'] - 2 - 2,
					$selement['y'] - 2 - 2,
					$selement['x'] + $iconX + 2 + 2,
					$selement['y'] + $iconY + 2 + 2,
					imagecolorallocate($im, 220, 220, 220)
				);
			}

			if (!is_null($hl_color)) {
				$r = $hl_color[0];
				$g = $hl_color[1];
				$b = $hl_color[2];

				imagefilledellipse($im,
					$selement['x'] + ($iconX / 2),
					$selement['y'] + ($iconY / 2),
					$iconX + 20,
					$iconX + 20,
					imagecolorallocatealpha($im, $r, $g, $b, 0)
				);

				imageellipse($im,
					$selement['x'] + ($iconX / 2),
					$selement['y'] + ($iconY / 2),
					$iconX + 20 + 1,
					$iconX + 20 + 1,
					imagecolorallocate($im, 120, 120, 120)
				);

				$config = select_config();
				if (isset($el_info['ack']) && $el_info['ack'] && $config['event_ack_enable']) {
					imagesetthickness($im, 5);
					imagearc($im,
						$selement['x'] + ($iconX / 2),
						$selement['y'] + ($iconY / 2),
						$iconX + 20 - 3,
						$iconX + 20 - 3,
						0,
						359,
						imagecolorallocate($im, 50, 150, 50)
					);
					imagesetthickness($im, 1);
				}
			}
		}
	}
}

function drawMapSelementsMarks(&$im, &$map, &$map_info) {
	global $colors;

	$selements = $map['selements'];

	foreach ($selements as $selementid => $selement) {
		if (empty($selement)) {
			continue;
		}

		$el_info = $map_info[$selementid];
		if (!$el_info['latelyChanged']) {
			continue;
		}

		// skip host group element containers
		if ($selement['elementsubtype'] == SYSMAP_ELEMENT_SUBTYPE_HOST_GROUP_ELEMENTS) {
			continue;
		}

		$img = get_png_by_selement($selement, $el_info);

		$iconX = imagesx($img);
		$iconY = imagesy($img);

		$hl_color = null;
		$st_color = null;
		if (!isset($_REQUEST['noselements']) && (($map['highlight'] % 2) == SYSMAP_HIGHLIGHT_ON)) {
			if ($el_info['icon_type'] == SYSMAP_ELEMENT_ICON_ON) {
				$hl_color = true;
			}

			if ($el_info['icon_type'] == SYSMAP_ELEMENT_ICON_MAINTENANCE) {
				$st_color = true;
			}
			if ($el_info['icon_type'] == SYSMAP_ELEMENT_ICON_DISABLED) {
				$st_color = true;
			}
		}

		$mainProblems = array(
			SYSMAP_ELEMENT_TYPE_HOST_GROUP => 1,
			SYSMAP_ELEMENT_TYPE_MAP => 1
		);

		if (isset($mainProblems[$selement['elementtype']])) {
			if (!is_null($hl_color)) {
				$st_color = null;
			}
			elseif (!is_null($st_color)) {
				$hl_color = null;
			}
		}

		$markSize = $iconX / 2;
		if ($hl_color) {
			$markSize += 12;
		}
		elseif ($st_color) {
			$markSize += 8;
		}
		else {
			$markSize += 3;
		}

		$marks = 'tlbr';
		if ($map['label_type'] != MAP_LABEL_TYPE_NOTHING) {
			$label_location = $selement['label_location'];
			if (is_null($label_location) || ($label_location < 0)) {
				$label_location = $map['label_location'];
			}

			switch ($label_location) {
				case MAP_LABEL_LOC_TOP:
					$marks = 'lbr';
					break;
				case MAP_LABEL_LOC_LEFT:
					$marks = 'tbr';
					break;
				case MAP_LABEL_LOC_RIGHT:
					$marks = 'tlb';
					break;
				case MAP_LABEL_LOC_BOTTOM:
				default:
					$marks = 'tlr';
			}
		}

		imageVerticalMarks($im, $selement['x'] + ($iconX / 2), $selement['y'] + ($iconY / 2), $markSize, $colors['Red'], $marks);
	}
}

function drawMapLinkLabels(&$im, $map, $map_info, $resolveMacros = true) {
	global $colors;

	$links = $map['links'];
	$selements = $map['selements'];

	foreach ($links as $link) {
		if (empty($link['label'])) {
			continue;
		}

		$selement1 = $selements[$link['selementid1']];
		list($x1, $y1) = get_icon_center_by_selement($selement1, $map_info[$link['selementid1']], $map);

		$selement2 = $selements[$link['selementid2']];
		list($x2, $y2) = get_icon_center_by_selement($selement2, $map_info[$link['selementid2']], $map);

		if (isset($selement1['elementsubtype']) && $selement1['elementsubtype'] == SYSMAP_ELEMENT_AREA_TYPE_CUSTOM) {
			if ($selement1['areatype'] == SYSMAP_ELEMENT_AREA_TYPE_CUSTOM) {
				$w = $selement1['width'];
				$h = $selement1['height'];
			}
			else {
				$w = $map['width'];
				$h = $map['height'];
			}
			list($x1, $y1) = calculateMapAreaLinkCoord($x1, $y1, $w, $h, $x2, $y2);
		}

		if (isset($selement2['elementsubtype']) && $selement2['elementsubtype'] == SYSMAP_ELEMENT_AREA_TYPE_CUSTOM) {
			if ($selement2['areatype'] == SYSMAP_ELEMENT_AREA_TYPE_CUSTOM) {
				$w = $selement2['width'];
				$h = $selement2['height'];
			}
			else {
				$w = $map['width'];
				$h = $map['height'];
			}
			list($x2, $y2) = calculateMapAreaLinkCoord($x2, $y2, $w, $h, $x1, $y1);
		}

		$drawtype = $link['drawtype'];
		$color = convertColor($im, $link['color']);

		$linktriggers = $link['linktriggers'];
		order_result($linktriggers, 'triggerid');

		if (!empty($linktriggers)) {
			$max_severity = 0;

			$triggers = array();
			foreach ($linktriggers as $link_trigger) {
				if ($link_trigger['triggerid'] == 0) {
					continue;
				}
				$id = $link_trigger['linktriggerid'];

				$triggers[$id] = zbx_array_merge($link_trigger, get_trigger_by_triggerid($link_trigger['triggerid']));
				if ($triggers[$id]['status'] == TRIGGER_STATUS_ENABLED && $triggers[$id]['value'] == TRIGGER_VALUE_TRUE) {
					if ($triggers[$id]['priority'] >= $max_severity) {
						$drawtype = $triggers[$id]['drawtype'];
						$color = convertColor($im, $triggers[$id]['color']);
						$max_severity = $triggers[$id]['priority'];
					}
				}
			}
		}

		$label = $link['label'];

		$label = str_replace("\r", '', $label);
		$strings = explode("\n", $label);

		$box_width = 0;
		$box_height = 0;

		foreach ($strings as $snum => $str) {
			$strings[$snum] = $resolveMacros ? resolveMapLabelMacros($str) : $str;
		}

		foreach ($strings as $str) {
			$dims = imageTextSize(8, 0, $str);

			$box_width = ($box_width > $dims['width']) ? $box_width : $dims['width'];
			$box_height += $dims['height'] + 2;
		}

		$boxX_left = round(($x1 + $x2) / 2 - ($box_width / 2) - 6);
		$boxX_right = round(($x1 + $x2) / 2 + ($box_width / 2) + 6);

		$boxY_top = round(($y1 + $y2) / 2 - ($box_height / 2) - 4);
		$boxY_bottom = round(($y1 + $y2) / 2 + ($box_height / 2) + 2);

		switch ($drawtype) {
			case MAP_LINK_DRAWTYPE_DASHED_LINE:
			case MAP_LINK_DRAWTYPE_DOT:
				dashedRectangle($im, $boxX_left, $boxY_top, $boxX_right, $boxY_bottom, $color);
				break;
			case MAP_LINK_DRAWTYPE_BOLD_LINE:
				imagerectangle($im, $boxX_left - 1, $boxY_top - 1, $boxX_right + 1, $boxY_bottom + 1, $color);
			case MAP_LINK_DRAWTYPE_LINE:
			default:
				imagerectangle($im, $boxX_left, $boxY_top, $boxX_right, $boxY_bottom, $color);
		}

		imagefilledrectangle($im, $boxX_left + 1, $boxY_top + 1, $boxX_right - 1, $boxY_bottom - 1, $colors['White']);

		$increasey = 4;
		foreach ($strings as $str) {
			$dims = imageTextSize(8, 0, $str);

			$labelx = ($x1 + $x2) / 2 - ($dims['width'] / 2);
			$labely = $boxY_top + $increasey;

			imagetext($im, 8, 0, $labelx, $labely + $dims['height'], $colors['Black'], $str);

			$increasey += $dims['height'] + 2;
		}
	}
}

function drawMapLabels(&$im, $map, $map_info, $resolveMacros = true) {
	global $colors;

	if ($map['label_type'] == MAP_LABEL_TYPE_NOTHING && $map['label_format'] == SYSMAP_LABEL_ADVANCED_OFF) {
		return;
	}

	$selements = $map['selements'];
	$all_strings = '';
	$label_lines = array();
	$status_lines = array();

	foreach ($selements as $sid => $selement) {
		if (isset($selement['elementsubtype']) && $selement['elementsubtype'] == SYSMAP_ELEMENT_SUBTYPE_HOST_GROUP_ELEMENTS) {
			unset($selements[$sid]);
		}
	}

	// set label type and custom label text for all selements
	foreach ($selements as $selementid => $selement) {
		$selements[$selementid]['label_type'] = $map['label_type'];

		if ($map['label_format'] == SYSMAP_LABEL_ADVANCED_OFF) {
			continue;
		}

		switch ($selement['elementtype']) {
			case SYSMAP_ELEMENT_TYPE_HOST_GROUP:
				$selements[$selementid]['label_type'] = $map['label_type_hostgroup'];
				if ($map['label_type_hostgroup'] == MAP_LABEL_TYPE_CUSTOM) {
					$selements[$selementid]['label'] = $map['label_string_hostgroup'];
				}
				break;
			case SYSMAP_ELEMENT_TYPE_HOST:
				$selements[$selementid]['label_type'] = $map['label_type_host'];
				if ($map['label_type_host'] == MAP_LABEL_TYPE_CUSTOM) {
					$selements[$selementid]['label'] = $map['label_string_host'];
				}
				break;
			case SYSMAP_ELEMENT_TYPE_TRIGGER:
				$selements[$selementid]['label_type'] = $map['label_type_trigger'];
				if ($map['label_type_trigger'] == MAP_LABEL_TYPE_CUSTOM) {
					$selements[$selementid]['label'] = $map['label_string_trigger'];
				}
				break;
			case SYSMAP_ELEMENT_TYPE_MAP:
				$selements[$selementid]['label_type'] = $map['label_type_map'];
				if ($map['label_type_map'] == MAP_LABEL_TYPE_CUSTOM) {
					$selements[$selementid]['label'] = $map['label_string_map'];
				}
				break;
			case SYSMAP_ELEMENT_TYPE_IMAGE:
				$selements[$selementid]['label_type'] = $map['label_type_image'];
				if ($map['label_type_image'] == MAP_LABEL_TYPE_CUSTOM) {
					$selements[$selementid]['label'] = $map['label_string_image'];
				}
				break;
		}
	}

	foreach ($selements as $selementid => $selement) {
		if (!isset($label_lines[$selementid])) {
			$label_lines[$selementid] = array();
		}
		if (!isset($status_lines[$selementid])) {
			$status_lines[$selementid] = array();
		}

		$msg = $resolveMacros ? resolveMapLabelMacrosAll($selement) : $selement['label'];

		$all_strings .= $msg;
		$msgs = explode("\n", $msg);
		foreach ($msgs as $msg) {
			$label_lines[$selementid][] = array('msg' => $msg);
		}

		$el_info = $map_info[$selementid];

		$el_msgs = array('problem', 'unack', 'maintenance', 'unknown', 'ok', 'status');
		foreach ($el_msgs as $caption) {
			if (!isset($el_info['info'][$caption]) || zbx_empty($el_info['info'][$caption]['msg'])) {
				continue;
			}

			$status_lines[$selementid][] = array(
				'msg' => $el_info['info'][$caption]['msg'],
				'color' => $el_info['info'][$caption]['color']
			);

			$all_strings .= $el_info['info'][$caption]['msg'];
		}
	}

	$allLabelsSize = imageTextSize(8, 0, str_replace("\r", '', str_replace("\n", '', $all_strings)));
	$labelFontHeight = $allLabelsSize['height'];
	$labelFontBaseline = $allLabelsSize['baseline'];

	$elementsHostids = array();
	foreach ($selements as $selement) {
		if ($selement['label_type'] != MAP_LABEL_TYPE_IP) {
			continue;
		}
		if ($selement['elementtype'] == SYSMAP_ELEMENT_TYPE_HOST) {
			$elementsHostids[] = $selement['elementid'];
		}
	}

	if (!empty($elementsHostids)) {
		$mapHosts = API::Host()->get(array(
			'hostids' => $elementsHostids,
			'output' => API_OUTPUT_SHORTEN,
			'selectInterfaces' => API_OUTPUT_EXTEND
		));
		$mapHosts = zbx_toHash($mapHosts, 'hostid');
	}

	// draw
	foreach ($selements as $selementid => $selement) {
		if (empty($selement) || $selement['label_type'] == MAP_LABEL_TYPE_NOTHING) {
			continue;
		}

		$el_info = $map_info[$selementid];

		$hl_color = null;
		$st_color = null;
		if (!isset($_REQUEST['noselements']) && (($map['highlight'] % 2) == SYSMAP_HIGHLIGHT_ON)) {
			if ($el_info['icon_type'] == SYSMAP_ELEMENT_ICON_ON) {
				$hl_color = true;
			}

			if ($el_info['icon_type'] == SYSMAP_ELEMENT_ICON_MAINTENANCE) {
				$st_color = true;
			}
			if ($el_info['icon_type'] == SYSMAP_ELEMENT_ICON_DISABLED) {
				$st_color = true;
			}
		}

		if (in_array($selement['elementtype'],
			array(SYSMAP_ELEMENT_TYPE_HOST_GROUP, SYSMAP_ELEMENT_TYPE_MAP)) && !is_null($hl_color)) {
			$st_color = null;
		}
		elseif (!is_null($st_color)) {
			$hl_color = null;
		}

		$label_location = (is_null($selement['label_location']) || ($selement['label_location'] < 0))
				? $map['label_location'] : $selement['label_location'];

		$label = array();
		if ($selement['label_type'] == MAP_LABEL_TYPE_IP && $selement['elementtype'] == SYSMAP_ELEMENT_TYPE_HOST) {
			$interface = reset($mapHosts[$selement['elementid']]['interfaces']);

			$label[] = array('msg' => $interface['ip']);
			$label = array_merge($label, $status_lines[$selementid]);
		}
		elseif ($selement['label_type'] == MAP_LABEL_TYPE_STATUS) {
			$label = $status_lines[$selementid];
		}
		elseif ($selement['label_type'] == MAP_LABEL_TYPE_NAME) {
			$label[] = array('msg' => $el_info['name']);
			$label = array_merge($label, $status_lines[$selementid]);
		}
		else {
			$label = array_merge($label_lines[$selementid], $status_lines[$selementid]);
		}
		if (empty($label)) {
			continue;
		}

		$w = 0;
		foreach ($label as $str) {
			$dims = imageTextSize(8, 0, $str['msg']);
			$w = max($w, $dims['width']);
		}
		$h = count($label) * $labelFontHeight;

		$x = $selement['x'];
		$y = $selement['y'];
		$iconX = $iconY = 0;

		$image = get_png_by_selement($selement, $el_info);
		if ($image) {
			$iconX = imagesx($image);
			$iconY = imagesy($image);
		}

		if (!is_null($hl_color)) {
			$icon_hl = 14;
		}
		elseif (!is_null($st_color)) {
			$icon_hl = 6;
		}
		else {
			$icon_hl = 2;
		}

		switch ($label_location) {
			case MAP_LABEL_LOC_TOP:
				$y_rec = $y - $icon_hl - $h - 6;
				$x_rec = $x + $iconX / 2 - $w / 2;
				break;
			case MAP_LABEL_LOC_LEFT:
				$y_rec = $y - $h / 2 + $iconY / 2;
				$x_rec = $x - $icon_hl - $w;
				break;
			case MAP_LABEL_LOC_RIGHT:
				$y_rec = $y - $h / 2 + $iconY / 2;
				$x_rec = $x + $iconX + $icon_hl;
				break;
			case MAP_LABEL_LOC_BOTTOM:
			default:
				$y_rec = $y + $iconY + $icon_hl;
				$x_rec = $x + $iconX / 2 - $w / 2;
		}

		$increasey = 12;
		foreach ($label as $line) {
			if (zbx_empty($line['msg'])) {
				continue;
			}

			$str = str_replace("\r", '', $line['msg']);
			$color = isset($line['color']) ? $line['color'] : $colors['Black'];

			$dims = imageTextSize(8, 0, $str);

			if ($label_location == MAP_LABEL_LOC_TOP || $label_location == MAP_LABEL_LOC_BOTTOM) {
				$x_label = $x + ceil($iconX / 2) - ceil($dims['width'] / 2);
			}
			elseif ($label_location == MAP_LABEL_LOC_LEFT) {
				$x_label = $x_rec + $w - $dims['width'];
			}
			else {
				$x_label = $x_rec;
			}

			imagefilledrectangle(
				$im,
				$x_label - 1, $y_rec + $increasey - $labelFontHeight + $labelFontBaseline,
				$x_label + $dims['width'] + 1, $y_rec + $increasey + $labelFontBaseline,
				$colors['White']
			);
			imagetext($im, 8, 0, $x_label, $y_rec + $increasey, $color, $str);

			$increasey += $labelFontHeight + 1;
		}
	}
}

/**
 * For each host group which is area for hosts virtual elements as hosts from that host group are created
 *
 * @param array $map
 * @return array areas with area coordinates and selementids
 */
function populateFromMapAreas(array &$map) {
	$areas = array();

	foreach ($map['selements'] as $selement) {
		if ($selement['elementsubtype'] == SYSMAP_ELEMENT_SUBTYPE_HOST_GROUP_ELEMENTS) {
			$area = array(
				'selementids' => array(),
			);

			$origSelement = $selement;

			$hosts = API::host()->get(array(
				'groupids' => $selement['elementid'],
				'sortfield' => 'name',
				'output' => API_OUTPUT_SHORTEN,
				'nopermissions' => true,
				'preservekeys' => true
			));
			$hostsCount = count($hosts);

			if ($hostsCount == 0) {
				continue;
			}

			if ($selement['areatype'] == SYSMAP_ELEMENT_AREA_TYPE_CUSTOM) {
				$area['width'] = $selement['width'];
				$area['height'] = $selement['height'];
				$area['x'] = $selement['x'];
				$area['y'] = $selement['y'];
			}
			else {
				$area['width'] = $map['width'];
				$area['height'] = $map['height'];
				$area['x'] = 0;
				$area['y'] = 0;
			}

			foreach ($hosts as $host) {
				$selement['elementtype'] = SYSMAP_ELEMENT_TYPE_HOST;
				$selement['elementsubtype'] = SYSMAP_ELEMENT_SUBTYPE_HOST_GROUP;
				$selement['elementid'] = $host['hostid'];

				$newSelementid = rand(1, 9999999);
				while (isset($map['selements'][$newSelementid])) {
					$newSelementid += 1;
				};
				$selement['selementid'] = $newSelementid;

				$area['selementids'][$newSelementid] = $newSelementid;
				$map['selements'][$newSelementid] = $selement;
			}

			$areas[] = $area;

			foreach ($map['links'] as $link) {
				// do not multiply links between two areas
				if ($map['selements'][$link['selementid1']]['elementsubtype'] == SYSMAP_ELEMENT_SUBTYPE_HOST_GROUP_ELEMENTS
						&& $map['selements'][$link['selementid2']]['elementsubtype'] == SYSMAP_ELEMENT_SUBTYPE_HOST_GROUP_ELEMENTS) {
					continue;
				}

				$idNumber = null;
				if ($link['selementid1'] == $origSelement['selementid']) {
					$idNumber = 'selementid1';
				}
				elseif ($link['selementid2'] == $origSelement['selementid']) {
					$idNumber = 'selementid2';
				}

				if ($idNumber) {
					foreach ($area['selementids'] as $newSelementid) {
						$newLinkid = rand(1, 9999999);
						while (isset($map['links'][$newLinkid])) {
							$newLinkid += 1;
						};

						$link['linkid'] = $newLinkid;
						$link[$idNumber] = $newSelementid;
						$map['links'][$newLinkid] = $link;
					}
				}
			}
		}
	}

	return $areas;
}

/**
 * Calculates coordinates from elements inside areas
 *
 * @param array $map
 * @param array $areas
 * @param array $mapInfo
 * @return void
 */
function processAreasCoordinates(array &$map, array $areas, array $mapInfo) {
	foreach ($areas as $area) {
		$rowPlaceCount = ceil(sqrt(count($area['selementids'])));

		// offset from area borders
		$area['x'] += 5;
		$area['y'] += 5;
		$area['width'] -= 5;
		$area['height'] -= 5;

		$xOffset = floor($area['width'] / $rowPlaceCount);
		$yOffset = floor($area['height'] / $rowPlaceCount);

		$colNum = 0;
		$rowNum = 0;
		foreach ($area['selementids'] as $selementid) {
			$selement = $map['selements'][$selementid];

			$image = get_png_by_selement($selement, $mapInfo[$selementid]);
			$iconX = imagesx($image);
			$iconY = imagesy($image);

			$label_location = (is_null($selement['label_location']) || ($selement['label_location'] < 0))
				? $map['label_location'] : $selement['label_location'];
			switch ($label_location) {
				case MAP_LABEL_LOC_TOP:
					$newX = $area['x'] + ($xOffset / 2) - ($iconX / 2);
					$newY = $area['y'] + $yOffset - $iconY;
					break;
				case MAP_LABEL_LOC_LEFT:
					$newX = $area['x'] + $xOffset - $iconX;
					$newY = $area['y'] + ($yOffset / 2) - ($iconY / 2);
					break;
				case MAP_LABEL_LOC_RIGHT:
					$newX = $area['x'];
					$newY = $area['y'] + ($yOffset / 2) - ($iconY / 2);
					break;
				case MAP_LABEL_LOC_BOTTOM:
					$newX = $area['x'] + ($xOffset / 2) - ($iconX / 2);
					$newY = $area['y'];
					break;
			}

			$map['selements'][$selementid]['x'] = $newX + ($colNum * $xOffset);
			$map['selements'][$selementid]['y'] = $newY + ($rowNum * $yOffset);

			$colNum++;
			if ($colNum == $rowPlaceCount) {
				$colNum = 0;
				$rowNum++;
			}
		}
	}
}

/**
 * Calculates area connector point on area perimeter
 *
 * @param int $ax x area coordinate
 * @param  $ay y area coordinate
 * @param  $aWidth area width
 * @param  $aHeight area height
 * @param  $x2 x coordinate of connector second element
 * @param  $y2 y coordinate of connector second element
 * @return array contains two values, x and y coordinates of new area connector point
 */
function calculateMapAreaLinkCoord($ax, $ay, $aWidth, $aHeight, $x2, $y2) {
	$dY = abs($y2 - $ay);
	$dX = abs($x2 - $ax);

	$halfHeight = $aHeight / 2;
	$halfWidth = $aWidth / 2;

	if ($dY == 0) {
		$ay = $y2;
		$ax = ($x2 < $ax) ? $ax - $halfWidth : $ax + $halfWidth;
	}
	elseif ($dX == 0) {
		$ay = ($y2 > $ay) ? $ay + $halfHeight : $ay - $halfHeight;
		$ax = $x2;
	}
	else {
		$koef = $halfHeight / $dY;

		$c = $dX * $koef;

		// if point is further than area diagonal, we should use calculations with width instead of height
		if (($halfHeight / $c) > ($halfHeight / $halfWidth)) {
			$ay = ($y2 > $ay) ? $ay + $halfHeight : $ay - $halfHeight;
			$ax = ($x2 < $ax) ? $ax - $c : $ax + $c;
		}
		else {
			$koef = $halfWidth / $dX;

			$c = $dY * $koef;

			$ay = ($y2 > $ay) ? $ay + $c : $ay - $c;
			$ax = ($x2 < $ax) ? $ax - $halfWidth : $ax + $halfWidth;
		}
	}

	return array($ax, $ay);
}

/**
 * @param $iconMap
 * @param $inventory
 * @return int icon id
 */
function getIconByMapping($iconMap, $inventory) {
	$iconid = null;
	$inventories = getHostInventories();

	if (isset($inventory['inventory'])) {
		foreach ($iconMap['mappings'] as $mapping) {
			try {
				$expr = new GlobalRegExp($mapping['expression']);
				if ($expr->match($inventory['inventory'][$inventories[$mapping['inventory_link']]['db_field']])) {
					$iconid = $mapping['iconid'];
					break;
				}
			}
			catch(Exception $e) {
				continue;
			}
		}
	}
	if (null === $iconid) {
		$iconid = $iconMap['default_iconid'];
	}
	return $iconid;
}
?>
