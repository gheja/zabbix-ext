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
require_once dirname(__FILE__).'/include/nodes.inc.php';

$page['title'] = _('Configuration of nodes');
$page['file'] = 'nodes.php';
$page['hist_arg'] = array();

require_once dirname(__FILE__).'/include/page_header.php';

// VAR	TYPE	OPTIONAL	FLAGS	VALIDATION	EXCEPTION
$fields = array(
	'nodeid' =>			array(T_ZBX_INT, O_NO,	null,	DB_ID,			'(isset({form})&&({form}=="update"))'),
	'new_nodeid' =>		array(T_ZBX_STR, O_OPT, null,	DB_ID.NOT_ZERO,	'isset({save})', _('ID')),
	'name' =>			array(T_ZBX_STR, O_OPT, null,	NOT_EMPTY,		'isset({save})'),
	'ip' =>				array(T_ZBX_IP,	 O_OPT, null,	null,			'isset({save})'),
	'nodetype' =>		array(T_ZBX_INT, O_OPT, null,	IN(ZBX_NODE_CHILD.','.ZBX_NODE_MASTER.','.ZBX_NODE_LOCAL),
		'isset({save})&&!isset({nodeid})'),
	'masterid' => 		array(T_ZBX_INT, O_OPT, null,	DB_ID,	null),
	'port' =>			array(T_ZBX_INT, O_OPT, null,	BETWEEN(1, 65535), 'isset({save})', _('Port')),
	// actions
	'save' =>			array(T_ZBX_STR, O_OPT, P_SYS|P_ACT,	null,	null),
	'delete' =>			array(T_ZBX_STR, O_OPT, P_SYS|P_ACT,	null,	null),
	'cancel' =>			array(T_ZBX_STR, O_OPT, P_SYS|P_ACT,	null,	null),
	'form' =>			array(T_ZBX_STR, O_OPT, P_SYS,	null,	null),
	'form_refresh' =>	array(T_ZBX_INT, O_OPT, null,	null,	null)
);
check_fields($fields);
validate_sort_and_sortorder();

/*
 * Permissions
 */
$available_nodes = get_accessible_nodes_by_user($USER_DETAILS, PERM_READ_LIST);
if (count($available_nodes) == 0) {
	access_deny();
}

/*
 * Actions
 */
if (isset($_REQUEST['save'])) {
	$result = false;

	if (isset($_REQUEST['nodeid'])) {
		$nodeid = get_request('nodeid');

		DBstart();
		$result = update_node($nodeid, get_request('name'), get_request('ip'), get_request('port'));
		$result = DBend($result);

		show_messages($result, _('Node updated'), _('Cannot update node'));
		$audit_action = AUDIT_ACTION_UPDATE;
	}
	else {
		DBstart();
		$nodeid = add_node(get_request('new_nodeid'), get_request('name'), get_request('ip'), get_request('port'), get_request('nodetype'), get_request('masterid'));
		$result = DBend($nodeid);

		show_messages($result, _('Node added'), _('Cannot add node'));
		$audit_action = AUDIT_ACTION_ADD;
	}

	add_audit_if($result, $audit_action, AUDIT_RESOURCE_NODE, 'Node ['.$_REQUEST['name'].'] id ['.$nodeid.']');

	if ($result) {
		unset($_REQUEST['form']);
	}
}
elseif (isset($_REQUEST['delete'])) {
	$node = get_node_by_nodeid($_REQUEST['nodeid']);

	DBstart();
	$result = delete_node($_REQUEST['nodeid']);
	$result = DBend($result);

	show_messages($result, _('Node deleted'), _('Cannot delete node'));

	add_audit_if($result, AUDIT_ACTION_DELETE, AUDIT_RESOURCE_NODE, 'Node ['.$node['name'].'] id ['.$node['nodeid'].']');

	if ($result) {
		unset($_REQUEST['form'], $node);
	}
}

/*
 * Display
 */
if (isset($_REQUEST['form'])) {
	$data = array(
		'form' => get_request('form', 1),
		'form_refresh' => get_request('form_refresh', 0) + 1,
		'nodeid' => get_request('nodeid')
	);

	if (isset($_REQUEST['nodeid']) && !isset($_REQUEST['form_refresh'])) {
		$node = get_node_by_nodeid($data['nodeid']);

		$data['new_nodeid'] = $node['nodeid'];
		$data['name'] = $node['name'];
		$data['ip'] = $node['ip'];
		$data['port'] = $node['port'];
		$data['masterid'] = $node['masterid'];
		$data['nodetype'] = detect_node_type($node['nodeid'], $node['masterid']);
	}
	else {
		$data['new_nodeid'] = get_request('new_nodeid');
		$data['name'] = get_request('name', '');
		$data['ip'] = get_request('ip', '127.0.0.1');
		$data['port'] = get_request('port', 10051);
		$data['nodetype'] = get_request('nodetype', ZBX_NODE_CHILD);
		$data['masterid'] = get_request('masterid', get_current_nodeid(false));
	}

	$data['masterNode'] = DBfetch(DBselect('SELECT n.name FROM nodes n WHERE n.masterid IS NULL AND n.nodetype='.ZBX_NODE_MASTER));

	// render view
	$nodeView = new CView('administration.node.edit', $data);
	$nodeView->render();
	$nodeView->show();
}
else {
	$data = array();

	if (ZBX_DISTRIBUTED) {
		$data['nodes'] = DBselect('SELECT n.* FROM nodes n '.order_by('n.nodeid,n.name,n.ip', 'n.masterid'));
	}

	// render view
	$nodeView = new CView('administration.node.list', $data);
	$nodeView->render();
	$nodeView->show();
}

require_once dirname(__FILE__).'/include/page_footer.php';
?>
