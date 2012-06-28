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

require_once dirname(__FILE__).'/include/config.inc.php';
require_once dirname(__FILE__).'/include/hosts.inc.php';
require_once dirname(__FILE__).'/include/graphs.inc.php';
require_once dirname(__FILE__).'/include/forms.inc.php';

$page['title'] = _('Configuration of graphs');
$page['file'] = 'graphs.php';
$page['hist_arg'] = array();
$page['scripts'] = array();

require_once dirname(__FILE__).'/include/page_header.php';

// VAR	TYPE	OPTIONAL	FLAGS	VALIDATION	EXCEPTION
$fields = array(
	'parent_discoveryid' =>	array(T_ZBX_INT, O_OPT, P_SYS,		DB_ID,				null),
	'groupid' =>			array(T_ZBX_INT, O_OPT, P_SYS,		DB_ID,				null),
	'hostid' =>				array(T_ZBX_INT, O_OPT, P_SYS,		DB_ID,				null),
	'copy_type' =>			array(T_ZBX_INT, O_OPT, P_SYS,		IN('0,1'),			'isset({copy})'),
	'copy_mode' =>			array(T_ZBX_INT, O_OPT, P_SYS,		IN('0'),			null),
	'graphid' =>			array(T_ZBX_INT, O_OPT, P_SYS,		DB_ID,				'(isset({form})&&({form}=="update"))'),
	'name' =>				array(T_ZBX_STR, O_OPT, null,		NOT_EMPTY,			'isset({save})', _('Name')),
	'width' =>				array(T_ZBX_INT, O_OPT, null,		BETWEEN(20, 65535),	'isset({save})', _('Width').' (min:20, max:65535)'),
	'height' =>				array(T_ZBX_INT, O_OPT, null,		BETWEEN(20, 65535),	'isset({save})', _('Height').' (min:20, max:65535)'),
	'ymin_type' =>			array(T_ZBX_INT, O_OPT, null,		IN('0,1,2'),		null),
	'ymax_type' =>			array(T_ZBX_INT, O_OPT, null,		IN('0,1,2'),		null),
	'graphtype' =>			array(T_ZBX_INT, O_OPT, null,		IN('0,1,2,3'),		'isset({save})'),
	'yaxismin' =>			array(T_ZBX_DBL, O_OPT, null,		null,				'isset({save})&&(({graphtype}==0)||({graphtype}==1))'),
	'yaxismax' =>			array(T_ZBX_DBL, O_OPT, null,		null,				'isset({save})&&(({graphtype}==0)||({graphtype}==1))'),
	'graph3d' =>			array(T_ZBX_INT, O_OPT, P_NZERO,	IN('0,1'),			null),
	'legend' =>				array(T_ZBX_INT, O_OPT, P_NZERO,	IN('0,1'),			null),
	'ymin_itemid' =>		array(T_ZBX_INT, O_OPT, null,		DB_ID,				'isset({save})&&isset({ymin_type})&&({ymin_type}==3)'),
	'ymax_itemid' =>		array(T_ZBX_INT, O_OPT, null,		DB_ID,				'isset({save})&&isset({ymax_type})&&({ymax_type}==3)'),
	'percent_left' =>		array(T_ZBX_DBL, O_OPT, null,		BETWEEN(0, 100),	null, _('Percentile line (Left)')),
	'percent_right' =>		array(T_ZBX_DBL, O_OPT, null,		BETWEEN(0, 100),	null, _('Percentile line (Right)')),
	'visible' =>			array(T_ZBX_INT, O_OPT, null,		BETWEEN(0, 1),		null),
	'items' =>				array(T_ZBX_STR, O_OPT, null,		null,				null),
	'showworkperiod' =>		array(T_ZBX_INT, O_OPT, null,		IN('1'),			null),
	'showtriggers' =>		array(T_ZBX_INT, O_OPT, null,		IN('1'),			null),
	'group_graphid' =>		array(T_ZBX_INT, O_OPT, null,		DB_ID,				null),
	'copy_targetid' =>		array(T_ZBX_INT, O_OPT, null,		DB_ID,				null),
	'filter_groupid' =>		array(T_ZBX_INT, O_OPT, P_SYS,		DB_ID,				'isset({copy})&&(isset({copy_type})&&({copy_type}==0))'),
	// actions
	'go' =>					array(T_ZBX_STR, O_OPT, P_SYS|P_ACT, null,				null),
	'save' =>				array(T_ZBX_STR, O_OPT, P_SYS|P_ACT, null,				null),
	'clone' =>				array(T_ZBX_STR, O_OPT, P_SYS|P_ACT, null,				null),
	'copy' =>				array(T_ZBX_STR, O_OPT, P_SYS|P_ACT, null,				null),
	'delete' =>				array(T_ZBX_STR, O_OPT, P_SYS|P_ACT, null,				null),
	'cancel' =>				array(T_ZBX_STR, O_OPT, P_SYS,		null,				null),
	'form' =>				array(T_ZBX_STR, O_OPT, P_SYS,		null,				null),
	'form_refresh' =>		array(T_ZBX_INT, O_OPT, null,		null,				null)
);
$percentVisible = get_request('visible');
if (!isset($percentVisible['percent_left'])) {
	unset($_REQUEST['percent_left']);
}
if (!isset($percentVisible['percent_right'])) {
	unset($_REQUEST['percent_right']);
}

check_fields($fields);
validate_sort_and_sortorder('name', ZBX_SORT_UP);

$_REQUEST['go'] = get_request('go', 'none');
$_REQUEST['items'] = get_request('items', array());
$_REQUEST['graph3d'] = get_request('graph3d', 0);
$_REQUEST['legend'] = get_request('legend', 0);

/*
 * Permissions
 */
if (!empty($_REQUEST['parent_discoveryid'])) {
	$discovery_rule = API::DiscoveryRule()->get(array(
		'itemids' => $_REQUEST['parent_discoveryid'],
		'output' => API_OUTPUT_EXTEND,
		'editable' => true
	));
	$discovery_rule = reset($discovery_rule);
	if (!$discovery_rule) {
		access_deny();
	}

	$_REQUEST['hostid'] = $discovery_rule['hostid'];

	if (isset($_REQUEST['graphid'])) {
		$graphPrototype = API::GraphPrototype()->get(array(
			'graphids' => $_REQUEST['graphid'],
			'output' => API_OUTPUT_SHORTEN,
			'editable' => true,
			'preservekeys' => true
		));
		if (empty($graphPrototype)) {
			access_deny();
		}
	}
}
elseif (!empty($_REQUEST['graphid'])) {
	$options = array(
		'nodeids' => get_current_nodeid(true),
		'filter' => array('flags' => ZBX_FLAG_DISCOVERY_NORMAL),
		'graphids' => $_REQUEST['graphid'],
		'editable' => true,
		'preservekeys' => true
	);
	$graphs = API::Graph()->get($options);
	if (empty($graphs)) {
		access_deny();
	}
}
elseif (!empty($_REQUEST['hostid'])) {
	$options = array(
		'hostids' => $_REQUEST['hostid'],
		'output' => API_OUTPUT_EXTEND,
		'templated_hosts' => true,
		'editable' => true,
		'preservekeys' => true
	);
	$hosts = API::Host()->get($options);
	if(empty($hosts)) {
		access_deny();
	}
}

/*
 * Actions
 */
if (isset($_REQUEST['clone']) && isset($_REQUEST['graphid'])) {
	unset($_REQUEST['graphid']);
	$_REQUEST['form'] = 'clone';
}
elseif (isset($_REQUEST['save'])) {
	$result = true;

	$items = get_request('items', array());
	if (!empty($items)) {
		$itemids = array();
		foreach ($items as $number => $item) {
			if (!empty($item['itemid'])) {
				$itemids[$item['itemid']] = $item['itemid'];
				$items[$number]['color'] = strtoupper($item['color']);
			}
			else {
				$result = false;
			}
		}
	}
	else {
		$result = false;
	}

	if (!$result) {
		info(_('Items required for graph.'));
	}

	if (!empty($itemids) && $result) {
		$dbItems = API::Item()->get(array(
			'nodeids' => get_current_nodeid(true),
			'itemids' => $itemids,
			'webitems' => true,
			'editable' => true
		));
		$dbItems = zbx_toHash($dbItems, 'itemid');

		foreach ($itemids as $itemid) {
			if (!isset($dbItems[$itemid])) {
				access_deny();
			}
		}

		if (!isset($_REQUEST['ymin_type'])) {
			$_REQUEST['ymin_type'] = 0;
		}
		if (!isset($_REQUEST['ymax_type'])) {
			$_REQUEST['ymax_type'] = 0;
		}
		if (!isset($_REQUEST['yaxismin'])) {
			$_REQUEST['yaxismin'] = 0;
		}
		if (!isset($_REQUEST['yaxismax'])) {
			$_REQUEST['yaxismax'] = 0;
		}

		$showworkperiod = isset($_REQUEST['showworkperiod']) ? 1 : 0;
		$showtriggers = isset($_REQUEST['showtriggers']) ? 1 : 0;

		$visible = get_request('visible');

		$percent_left = 0;
		if (isset($visible['percent_left'])) {
			$percent_left = get_request('percent_left', 0);
		}

		$percent_right = 0;
		if (isset($visible['percent_right'])) {
			$percent_right = get_request('percent_right', 0);
		}

		if ($_REQUEST['ymin_itemid'] != 0 && $_REQUEST['ymax_type'] == GRAPH_YAXIS_TYPE_ITEM_VALUE) {
			$_REQUEST['yaxismin'] = 0;
		}
		if ($_REQUEST['ymax_itemid'] != 0 && $_REQUEST['ymax_type'] == GRAPH_YAXIS_TYPE_ITEM_VALUE) {
			$_REQUEST['yaxismax'] = 0;
		}

		$graph = array(
			'name' => $_REQUEST['name'],
			'width' => $_REQUEST['width'],
			'height' => $_REQUEST['height'],
			'ymin_type' => $_REQUEST['ymin_type'],
			'ymax_type' => $_REQUEST['ymax_type'],
			'yaxismin' => $_REQUEST['yaxismin'],
			'yaxismax' => $_REQUEST['yaxismax'],
			'ymin_itemid' => $_REQUEST['ymin_itemid'],
			'ymax_itemid' => $_REQUEST['ymax_itemid'],
			'show_work_period' => get_request('showworkperiod', 0),
			'show_triggers' => get_request('showtriggers', 0),
			'graphtype' => $_REQUEST['graphtype'],
			'show_legend' => get_request('legend', 1),
			'show_3d' => get_request('graph3d', 0),
			'percent_left' => $percent_left,
			'percent_right' => $percent_right,
			'gitems' => $items
		);

		if (!empty($_REQUEST['parent_discoveryid'])) {
			$graph['flags'] = ZBX_FLAG_DISCOVERY_CHILD;
		}

		if (isset($_REQUEST['graphid'])) {
			$graph['graphid'] = $_REQUEST['graphid'];

			$result = !empty($_REQUEST['parent_discoveryid'])
				? API::GraphPrototype()->update($graph)
				: API::Graph()->update($graph);

			if ($result) {
				add_audit(AUDIT_ACTION_UPDATE, AUDIT_RESOURCE_GRAPH, 'Graph ID ['.$_REQUEST['graphid'].'] Graph ['.$_REQUEST['name'].']');
			}
		}
		else {
			$result = !empty($_REQUEST['parent_discoveryid'])
				? API::GraphPrototype()->create($graph)
				: API::Graph()->create($graph);

			if ($result) {
				add_audit(AUDIT_ACTION_ADD, AUDIT_RESOURCE_GRAPH, 'Graph ['.$_REQUEST['name'].']');
			}
		}

		if ($result) {
			unset($_REQUEST['form']);
		}
	}

	if (isset($_REQUEST['graphid'])) {
		show_messages($result, _('Graph updated'), _('Cannot update graph'));
	}
	else {
		show_messages($result, _('Graph added'), _('Cannot add graph'));
	}
}
elseif (isset($_REQUEST['delete']) && isset($_REQUEST['graphid'])) {
	$result = !empty($_REQUEST['parent_discoveryid'])
		? API::GraphPrototype()->delete($_REQUEST['graphid'])
		: API::Graph()->delete($_REQUEST['graphid']);

	if ($result) {
		unset($_REQUEST['form']);
	}
	show_messages($result, _('Graph deleted'), _('Cannot delete graph'));
}
elseif ($_REQUEST['go'] == 'delete' && isset($_REQUEST['group_graphid'])) {
	$go_result = !empty($_REQUEST['parent_discoveryid'])
		? API::GraphPrototype()->delete($_REQUEST['group_graphid'])
		: API::Graph()->delete($_REQUEST['group_graphid']);

	show_messages($go_result, _('Graphs deleted'), _('Cannot delete graphs'));
}
elseif ($_REQUEST['go'] == 'copy_to' && isset($_REQUEST['copy']) && isset($_REQUEST['group_graphid'])) {
	if (!empty($_REQUEST['copy_targetid']) && isset($_REQUEST['copy_type'])) {
		$go_result = true;

		$options = array(
			'editable' => true,
			'nodes' => get_current_nodeid(true),
			'templated_hosts' => true
		);

		// hosts
		if ($_REQUEST['copy_type'] == 0) {
			$options['hostids'] = $_REQUEST['copy_targetid'];
		}
		// groups
		else {
			zbx_value2array($_REQUEST['copy_targetid']);

			$dbGroups = API::HostGroup()->get(array(
				'groupids' => $_REQUEST['copy_targetid'],
				'nodes' => get_current_nodeid(true),
				'editable' => true
			));
			$dbGroups = zbx_toHash($dbGroups, 'groupid');

			foreach ($_REQUEST['copy_targetid'] as $groupid) {
				if (!isset($dbGroups[$groupid])) {
					access_deny();
				}
			}

			$options['groupids'] = $_REQUEST['copy_targetid'];
		}

		$dbHosts = API::Host()->get($options);

		DBstart();
		foreach ($_REQUEST['group_graphid'] as $graphid) {
			foreach ($dbHosts as $host) {
				$go_result &= (bool) copy_graph_to_host($graphid, $host['hostid']);
			}
		}
		$go_result = DBend($go_result);

		show_messages($go_result, _('Graphs copied'), _('Cannot copy graphs'));
		$_REQUEST['go'] = 'none2';
	}
	else {
		error(_('No target selected.'));
	}
	show_messages();
}
if ($_REQUEST['go'] != 'none' && isset($go_result) && $go_result) {
	$url = new CUrl();
	$path = $url->getPath();
	insert_js('cookie.eraseArray("'.$path.'")');
}

/*
 * Display
 */
$pageFilter = new CPageFilter(array(
	'groups' => array(
		'not_proxy_hosts' => true,
		'editable' => true
	),
	'hosts' => array(
		'editable' => true,
		'templated_hosts' => true
	),
	'groupid' => get_request('groupid', null),
	'hostid' => get_request('hostid', null)
));
$_REQUEST['groupid'] = $pageFilter->groupid;
$_REQUEST['hostid'] = $pageFilter->hostid;

if ($_REQUEST['go'] == 'copy_to' && isset($_REQUEST['group_graphid'])) {
	// render view
	$graphView = new CView('configuration.copy.elements', getCopyElementsFormData('group_graphid'));
	$graphView->render();
	$graphView->show();
}
elseif (isset($_REQUEST['form'])) {
	$data = array(
		'form' => get_request('form'),
		'form_refresh' => get_request('form_refresh', 0),
		'graphid' => get_request('graphid', 0),
		'parent_discoveryid' => get_request('parent_discoveryid'),
		'group_gid' => get_request('group_gid', array()),
		'hostid' => get_request('hostid', 0),
		'is_template' => isTemplate(get_request('hostid', 0)),
		'normal_only' => get_request('normal_only')
	);

	if (!empty($data['graphid']) && !isset($_REQUEST['form_refresh'])) {
		$options = array(
			'graphids' => $data['graphid'],
			'output' => API_OUTPUT_EXTEND
		);
		$graph = !empty($data['parent_discoveryid'])
			? API::GraphPrototype()->get($options)
			: API::Graph()->get($options);

		$graph = reset($graph);

		$data['name'] = $graph['name'];
		$data['width'] = $graph['width'];
		$data['height'] = $graph['height'];
		$data['ymin_type'] = $graph['ymin_type'];
		$data['ymax_type'] = $graph['ymax_type'];
		$data['yaxismin'] = $graph['yaxismin'];
		$data['yaxismax'] = $graph['yaxismax'];
		$data['ymin_itemid'] = $graph['ymin_itemid'];
		$data['ymax_itemid'] = $graph['ymax_itemid'];
		$data['showworkperiod'] = $graph['show_work_period'];
		$data['showtriggers'] = $graph['show_triggers'];
		$data['graphtype'] = $graph['graphtype'];
		$data['legend'] = $graph['show_legend'];
		$data['graph3d'] = $graph['show_3d'];
		$data['percent_left'] = $graph['percent_left'];
		$data['percent_right'] = $graph['percent_right'];
		$data['templateid'] = $graph['templateid'];
		$data['templates'] = array();

		// templates
		if (!empty($data['templateid'])) {
			$parentGraphid = $data['templateid'];
			do {
				$parentGraph = get_graph_by_graphid($parentGraphid);
				$parentTemplate = get_hosts_by_graphid($parentGraph['graphid']);
				$parentTemplate = DBfetch($parentTemplate);

				$data['templates'][] = new CLink($parentTemplate['host'],
					'graphs.php?form=update&graphid='.$parentGraph['graphid'].'&hostid='.$parentTemplate['hostid'].url_param('parent_discoveryid'));
				$data['templates'][] = SPACE.RARR.SPACE;

				$parentGraphid = $parentGraph['templateid'];
			} while ($parentGraphid != 0);
			$data['templates'] = array_reverse($data['templates']);
			array_shift($data['templates']);
		}

		// items
		$data['items'] = API::GraphItem()->get(array(
			'graphids' => $data['graphid'],
			'sortfield' => 'gitemid',
			'output' => API_OUTPUT_EXTEND,
			'expandData' => true
		));
	}
	else {
		$data['name'] = get_request('name', '');
		$data['graphtype'] = get_request('graphtype', GRAPH_TYPE_NORMAL);

		if ($data['graphtype'] == GRAPH_TYPE_PIE || $data['graphtype'] == GRAPH_TYPE_EXPLODED) {
			$data['width'] = get_request('width', 400);
			$data['height'] = get_request('height', 300);
		}
		else {
			$data['width'] = get_request('width', 900);
			$data['height'] = get_request('height', 200);
		}

		$data['ymin_type'] = get_request('ymin_type', GRAPH_YAXIS_TYPE_CALCULATED);
		$data['ymax_type'] = get_request('ymax_type', GRAPH_YAXIS_TYPE_CALCULATED);
		$data['yaxismin'] = get_request('yaxismin', 0.00);
		$data['yaxismax'] = get_request('yaxismax', 100.00);
		$data['ymin_itemid'] = get_request('ymin_itemid', 0);
		$data['ymax_itemid'] = get_request('ymax_itemid', 0);
		$data['showworkperiod'] = get_request('showworkperiod', 0);
		$data['showtriggers'] = get_request('showtriggers', 0);
		$data['legend'] = get_request('legend', 0);
		$data['graph3d'] = get_request('graph3d', 0);
		$data['visible'] = get_request('visible');
		$data['percent_left'] = 0;
		$data['percent_right'] = 0;
		$data['visible'] = get_request('visible');
		$data['items'] = get_request('items', array());

		if (isset($data['visible']['percent_left'])) {
			$data['percent_left'] = get_request('percent_left', 0);
		}
		if (isset($data['visible']['percent_right'])) {
			$data['percent_right'] = get_request('percent_right', 0);
		}
	}

	if (empty($data['graphid']) && !isset($_REQUEST['form_refresh'])) {
		$data['legend'] = $_REQUEST['legend'] = 1;
		$data['showworkperiod'] = $_REQUEST['showworkperiod'] = 1;
		$data['showtriggers'] = $_REQUEST['showtriggers'] = 1;
	}

	$_REQUEST['items'] = $data['items'];
	$_REQUEST['name'] = $data['name'];
	$_REQUEST['width'] = $data['width'];
	$_REQUEST['height'] = $data['height'];
	$_REQUEST['ymin_type'] = $data['ymin_type'];
	$_REQUEST['ymax_type'] = $data['ymax_type'];
	$_REQUEST['yaxismin'] = $data['yaxismin'];
	$_REQUEST['yaxismax'] = $data['yaxismax'];
	$_REQUEST['ymin_itemid'] = $data['ymin_itemid'];
	$_REQUEST['ymax_itemid'] = $data['ymax_itemid'];
	$_REQUEST['showworkperiod'] = $data['showworkperiod'];
	$_REQUEST['showtriggers'] = $data['showtriggers'];
	$_REQUEST['graphtype'] = $data['graphtype'];
	$_REQUEST['legend'] = $data['legend'];
	$_REQUEST['graph3d'] = $data['graph3d'];
	$_REQUEST['percent_left'] = $data['percent_left'];
	$_REQUEST['percent_right'] = $data['percent_right'];

	$data['items'] = array_values($data['items']);
	$itemCount = count($data['items']);
	for ($i = 0; $i < $itemCount - 1;) {
		// check if we delete an item
		$next = $i + 1;
		while (!isset($data['items'][$next]) && $next < ($itemCount - 1)) {
			$next++;
		}

		if (isset($data['items'][$next]) && $data['items'][$i]['sortorder'] == $data['items'][$next]['sortorder']) {
			for ($j = $next; $j < $itemCount; $j++) {
				if ($data['items'][$j - 1]['sortorder'] >= $data['items'][$j]['sortorder']) {
					$data['items'][$j]['sortorder']++;
				}
			}
		}

		$i = $next;
	}
	asort_by_key($data['items'], 'sortorder');
	$data['items'] = array_values($data['items']);

	// render view
	$graphView = new CView('configuration.graph.edit', $data);
	$graphView->render();
	$graphView->show();
}
else {
	if (isset($_REQUEST['graphid']) && $_REQUEST['graphid'] == 0) {
		unset($_REQUEST['graphid']);
	}

	$data = array(
		'pageFilter' => $pageFilter,
		'hostid' => get_request('hostid'),
		'parent_discoveryid' => get_request('parent_discoveryid'),
		'graphs' => array()
	);
	if (!empty($_REQUEST['parent_discoveryid'])) {
		$data['discovery_rule'] = $discovery_rule;
	}

	$sortfield = getPageSortField('name');
	$sortorder = getPageSortOrder();

	if ($pageFilter->hostsSelected) {
		$options = array(
			'editable' => true,
			'output' => array('graphid', 'name', 'graphtype'),
			'limit' => $config['search_limit'] + 1
		);
		if ($pageFilter->hostid > 0) {
			$options['hostids'] = $pageFilter->hostid;
		}
		elseif ($pageFilter->groupid > 0) {
			$options['groupids'] = $pageFilter->groupid;
		}
		if (!empty($_REQUEST['parent_discoveryid'])) {
			$options['discoveryids'] = $_REQUEST['parent_discoveryid'];
		}
		$data['graphs'] = !empty($_REQUEST['parent_discoveryid'])
			? API::GraphPrototype()->get($options)
			: API::Graph()->get($options);
	}

	if ($sortfield == 'graphtype') {
		foreach ($data['graphs'] as $gnum => $graph) {
			$data['graphs'][$gnum]['graphtype'] = graphType($graph['graphtype']);
		}
	}
	order_result($data['graphs'], $sortfield, $sortorder);
	$data['paging'] = getPagingLine($data['graphs']);

	$options = array(
		'graphids' => zbx_objectValues($data['graphs'], 'graphid'),
		'output' => array('graphid', 'name', 'templateid', 'graphtype', 'width', 'height'),
		'selectDiscoveryRule' => array('itemid', 'name'),
	);
	if ($pageFilter->hostid == 0) {
		$options['selectHosts'] = array('name');
		$options['selectTemplates'] = array('name');
	}
	$data['graphs'] = !empty($_REQUEST['parent_discoveryid'])
		? API::GraphPrototype()->get($options)
		: API::Graph()->get($options);

	foreach ($data['graphs'] as $gnum => $graph) {
		$data['graphs'][$gnum]['graphtype'] = graphType($graph['graphtype']);
	}
	order_result($data['graphs'], $sortfield, $sortorder);

	// render view
	$graphView = new CView('configuration.graph.list', $data);
	$graphView->render();
	$graphView->show();
}

require_once dirname(__FILE__).'/include/page_footer.php';
