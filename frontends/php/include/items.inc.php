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
 * Convert windows events type constant in to the string representation
 *
 * @param int $logtype
 * @return string
 */
function get_item_logtype_description($logtype) {
	switch ($logtype) {
		case ITEM_LOGTYPE_INFORMATION:
			return _('Information');
		case ITEM_LOGTYPE_WARNING:
			return _('Warning');
		case ITEM_LOGTYPE_ERROR:
			return _('Error');
		case ITEM_LOGTYPE_FAILURE_AUDIT:
			return _('Failure Audit');
		case ITEM_LOGTYPE_SUCCESS_AUDIT:
			return _('Success Audit');
		default:
			return _('Unknown');
	}
}

/**
 * Convert windows events type constant in to the CSS style name
 *
 * @param int $logtype
 * @return string
 */
function get_item_logtype_style($logtype) {
	switch ($logtype) {
		case ITEM_LOGTYPE_INFORMATION:
			return 'information';
		case ITEM_LOGTYPE_WARNING:
			return 'warning';
		case ITEM_LOGTYPE_ERROR:
			return 'high';
		case ITEM_LOGTYPE_FAILURE_AUDIT:
			return 'high';
		case ITEM_LOGTYPE_SUCCESS_AUDIT:
			return 'information';
		default:
			return 'normal';
	}
}

/**
 * Get item type string name by item type number, or array of all item types if null passed
 *
 * @param int|null $type
 * @return array|string
 */
function item_type2str($type = null) {
	$types = array(
		ITEM_TYPE_ZABBIX => _('Zabbix agent'),
		ITEM_TYPE_ZABBIX_ACTIVE => _('Zabbix agent (active)'),
		ITEM_TYPE_SIMPLE => _('Simple check'),
		ITEM_TYPE_SNMPV1 => _('SNMPv1 agent'),
		ITEM_TYPE_SNMPV2C => _('SNMPv2 agent'),
		ITEM_TYPE_SNMPV3 => _('SNMPv3 agent'),
		ITEM_TYPE_SNMPTRAP => _('SNMP trap'),
		ITEM_TYPE_INTERNAL => _('Zabbix internal'),
		ITEM_TYPE_TRAPPER => _('Zabbix trapper'),
		ITEM_TYPE_AGGREGATE => _('Zabbix aggregate'),
		ITEM_TYPE_EXTERNAL => _('External check'),
		ITEM_TYPE_DB_MONITOR => _('Database monitor'),
		ITEM_TYPE_IPMI => _('IPMI agent'),
		ITEM_TYPE_SSH => _('SSH agent'),
		ITEM_TYPE_TELNET => _('TELNET agent'),
		ITEM_TYPE_JMX => _('JMX agent'),
		ITEM_TYPE_CALCULATED => _('Calculated'),
		ITEM_TYPE_HTTPTEST => _('Web monitoring')
	);
	if (is_null($type)) {
		return $types;
	}
	elseif (isset($types[$type])) {
		return $types[$type];
	}
	else {
		return _('Unknown');
	}
}

function item_value_type2str($type = null) {
	$types = array(
		ITEM_VALUE_TYPE_UINT64 => _('Numeric (unsigned)'),
		ITEM_VALUE_TYPE_FLOAT => _('Numeric (float)'),
		ITEM_VALUE_TYPE_STR => _('Character'),
		ITEM_VALUE_TYPE_LOG => _('Log'),
		ITEM_VALUE_TYPE_TEXT => _('Text')
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

function item_data_type2str($type = null) {
	$types = array(
		ITEM_DATA_TYPE_BOOLEAN => _('Boolean'),
		ITEM_DATA_TYPE_OCTAL => _('Octal'),
		ITEM_DATA_TYPE_DECIMAL => _('Decimal'),
		ITEM_DATA_TYPE_HEXADECIMAL => _('Hexadecimal')
	);
	if (is_null($type)) {
		return $types;
	}
	elseif (isset($types[$type])) {
		return $types[$type];
	}
	else {
		return _('Unknown');
	}
}

function item_status2str($type = null) {
	$types = array(
		ITEM_STATUS_ACTIVE => _('Enabled'),
		ITEM_STATUS_DISABLED => _('Disabled'),
		ITEM_STATUS_NOTSUPPORTED => _('Not supported')
	);
	if (is_null($type)) {
		return $types;
	}
	elseif (isset($types[$type])) {
		return $types[$type];
	}
	else {
		return _('Unknown');
	}
}

function item_status2style($status) {
	switch ($status) {
		case ITEM_STATUS_ACTIVE:
			return 'off';
		case ITEM_STATUS_DISABLED:
			return 'on';
		case ITEM_STATUS_NOTSUPPORTED:
		default:
			return 'unknown';
	}
}

/**
 * Returns the name of the given interface type.
 *
 * @param int $type
 *
 * @return null
 */
function interfaceType2str($type) {
	$interfaceGroupLabels = array(
		INTERFACE_TYPE_AGENT => _('Agent'),
		INTERFACE_TYPE_SNMP => _('SNMP'),
		INTERFACE_TYPE_JMX => _('JMX'),
		INTERFACE_TYPE_IPMI => _('IPMI'),
	);

	return isset($interfaceGroupLabels[$type]) ? $interfaceGroupLabels[$type] : null;
}

function itemTypeInterface($type = null) {
	$types = array(
		ITEM_TYPE_SNMPV1 => INTERFACE_TYPE_SNMP,
		ITEM_TYPE_SNMPV2C => INTERFACE_TYPE_SNMP,
		ITEM_TYPE_SNMPV3 => INTERFACE_TYPE_SNMP,
		ITEM_TYPE_SNMPTRAP => INTERFACE_TYPE_SNMP,
		ITEM_TYPE_IPMI => INTERFACE_TYPE_IPMI,
		ITEM_TYPE_ZABBIX => INTERFACE_TYPE_AGENT,
		ITEM_TYPE_SIMPLE => INTERFACE_TYPE_ANY,
		ITEM_TYPE_EXTERNAL => INTERFACE_TYPE_ANY,
		ITEM_TYPE_SSH => INTERFACE_TYPE_ANY,
		ITEM_TYPE_TELNET => INTERFACE_TYPE_ANY,
		ITEM_TYPE_JMX => INTERFACE_TYPE_JMX
	);
	if (is_null($type)) {
		return $types;
	}
	elseif (isset($types[$type])) {
		return $types[$type];
	}
	else {
		return false;
	}
}

function update_item_status($itemids, $status) {
	zbx_value2array($itemids);
	$result = true;

	$db_items = DBselect('SELECT i.* FROM items i WHERE '.DBcondition('i.itemid', $itemids));
	while ($item = DBfetch($db_items)) {
		$old_status = $item['status'];
		if ($status != $old_status) {
			$result &= DBexecute('UPDATE items SET status='.$status.
				($status != ITEM_STATUS_NOTSUPPORTED ? ",error=''" : '').
				' WHERE itemid='.$item['itemid']);
			if ($result) {
				$host = get_host_by_hostid($item['hostid']);
				$item_new = get_item_by_itemid($item['itemid']);
				add_audit_ext(AUDIT_ACTION_UPDATE, AUDIT_RESOURCE_ITEM, $item['itemid'], $host['host'].':'.$item['name'], 'items', $item, $item_new);
			}
		}
	}
	return $result;
}

function copyItemsToHosts($srcItemIds, $dstHostIds) {
	$srcItems = API::Item()->get(array(
		'itemids' => $srcItemIds,
		'output' => array(
			'type', 'snmp_community', 'snmp_oid', 'name', 'key_', 'delay', 'history', 'trends', 'status', 'value_type',
			'trapper_hosts', 'units', 'multiplier', 'delta', 'snmpv3_securityname', 'snmpv3_securitylevel', 'snmpv3_authpassphrase',
			'snmpv3_privpassphrase', 'formula', 'logtimefmt', 'valuemapid', 'delay_flex', 'params', 'ipmi_sensor', 'data_type',
			'authtype', 'username', 'password', 'publickey', 'privatekey', 'flags', 'filter', 'port', 'description', 'inventory_link'
		),
		'filter' => array('flags' => ZBX_FLAG_DISCOVERY_NORMAL),
		'selectApplications' => API_OUTPUT_REFER
	));
	foreach ($srcItems as &$srcItem) {
		if ($srcItem['status'] == ITEM_STATUS_NOTSUPPORTED) {
			$srcItem['status'] = ITEM_STATUS_ACTIVE;
		}
	}

	$dstHosts = API::Host()->get(array(
		'output' => array('hostid', 'host', 'status'),
		'selectInterfaces' => array('interfaceid', 'type', 'main'),
		'hostids' => $dstHostIds,
		'preservekeys' => true,
		'nopermissions' => true,
		'templated_hosts' => true
	));

	foreach ($dstHosts as $dstHost) {
		$interfaceids = array();
		foreach ($dstHost['interfaces'] as $interface) {
			if ($interface['main'] == 1) {
				$interfaceids[$interface['type']] = $interface['interfaceid'];
			}
		}
		foreach ($srcItems as &$srcItem) {
			if ($dstHost['status'] != HOST_STATUS_TEMPLATE) {
				$type = itemTypeInterface($srcItem['type']);

				if ($type == INTERFACE_TYPE_ANY) {
					foreach (array(INTERFACE_TYPE_AGENT, INTERFACE_TYPE_SNMP, INTERFACE_TYPE_JMX, INTERFACE_TYPE_IPMI) as $itype) {
						if (isset($interfaceids[$itype])) {
							$srcItem['interfaceid'] = $interfaceids[$itype];
							break;
						}
					}
				}
				elseif ($type !== false) {
					if (!isset($interfaceids[$type])) {
						error(_s('Cannot find host interface on "%1$s" for item key "%2$s".', $dstHost['host'], $srcItem['key_']));
						return false;
					}
					$srcItem['interfaceid'] = $interfaceids[$type];
				}
			}
			unset($srcItem['itemid']);
			$srcItem['hostid'] = $dstHost['hostid'];
			$srcItem['applications'] = get_same_applications_for_host(zbx_objectValues($srcItem['applications'], 'applicationid'), $dstHost['hostid']);
		}
		if (!API::Item()->create($srcItems)) {
			return false;
		}
	}
	return true;
}

function copyItems($srcHostId, $dstHostId) {
	$srcItems = API::Item()->get(array(
		'hostids' => $srcHostId,
		'output' => array(
			'type', 'snmp_community', 'snmp_oid', 'name', 'key_', 'delay', 'history', 'trends', 'status', 'value_type',
			'trapper_hosts', 'units', 'multiplier', 'delta', 'snmpv3_securityname', 'snmpv3_securitylevel', 'snmpv3_authpassphrase',
			'snmpv3_privpassphrase', 'formula', 'logtimefmt', 'valuemapid', 'delay_flex', 'params', 'ipmi_sensor', 'data_type',
			'authtype', 'username', 'password', 'publickey', 'privatekey', 'flags', 'filter', 'port', 'description', 'inventory_link'
		),
		'inherited' => false,
		'filter' => array('flags' => ZBX_FLAG_DISCOVERY_NORMAL),
		'selectApplications' => API_OUTPUT_REFER
	));

	foreach ($srcItems as &$srcItem) {
		if ($srcItem['status'] == ITEM_STATUS_NOTSUPPORTED) {
			$srcItem['status'] = ITEM_STATUS_ACTIVE;
		}
	}

	$dstHosts = API::Host()->get(array(
		'output' => array('hostid', 'host', 'status'),
		'selectInterfaces' => array('interfaceid', 'type', 'main'),
		'hostids' => $dstHostId,
		'preservekeys' => true,
		'nopermissions' => true,
		'templated_hosts' => true
	));
	$dstHost = reset($dstHosts);

	foreach ($srcItems as &$srcItem) {
		if ($dstHost['status'] != HOST_STATUS_TEMPLATE) {
			// find a matching interface
			$interface = CItem::findInterfaceForItem($srcItem, $dstHost['interfaces']);
			if ($interface) {
				$srcItem['interfaceid'] = $interface['interfaceid'];
			}
			// no matching interface found, throw an error
			elseif ($interface !== false) {
				error(_s('Cannot find host interface on "%1$s" for item key "%2$s".', $dstHost['host'], $srcItem['key_']));
			}
		}
		unset($srcItem['itemid']);
		$srcItem['hostid'] = $dstHostId;
		$srcItem['applications'] = get_same_applications_for_host(zbx_objectValues($srcItem['applications'], 'applicationid'), $dstHostId);
	}
	return API::Item()->create($srcItems);
}

function copyApplications($srcHostId, $dstHostId) {
	$apps_to_clone = API::Application()->get(array(
		'hostids' => $srcHostId,
		'output' => API_OUTPUT_EXTEND,
		'inherited' => false
	));
	foreach ($apps_to_clone as &$app) {
		$app['hostid'] = $dstHostId;
		unset($app['applicationid'], $app['templateid']);
	}
	return API::Application()->create($apps_to_clone);
}

function activate_item($itemids) {
	zbx_value2array($itemids);

	// first update status for child items
	$child_items = array();
	$db_items = DBselect('SELECT i.itemid,i.hostid FROM items i WHERE '.DBcondition('i.templateid', $itemids));
	while ($item = DBfetch($db_items)) {
		$child_items[$item['itemid']] = $item['itemid'];
	}
	if (!empty($child_items)) {
		activate_item($child_items); // Recursion !!!
	}
	return update_item_status($itemids, ITEM_STATUS_ACTIVE);
}

function disable_item($itemids) {
	zbx_value2array($itemids);

	// first update status for child items
	$chd_items = array();
	$db_tmp_items = DBselect('SELECT i.itemid,i.hostid FROM items i WHERE '.DBcondition('i.templateid', $itemids));
	while ($db_tmp_item = DBfetch($db_tmp_items)) {
		$chd_items[$db_tmp_item['itemid']] = $db_tmp_item['itemid'];
	}
	if (!empty($chd_items)) {
		disable_item($chd_items); // Recursion !!!
	}
	return update_item_status($itemids, ITEM_STATUS_DISABLED);
}

function get_items_by_hostid($hostids) {
	zbx_value2array($hostids);
	return DBselect('SELECT i.* FROM items i WHERE '.DBcondition('i.hostid', $hostids));
}

function get_item_by_key($key, $host = '') {
	$item = false;
	$sql_from = '';
	$sql_where = '';
	if (!empty($host)) {
		$sql_from = ',hosts h ';
		$sql_where = ' AND h.host='.zbx_dbstr($host).' AND i.hostid=h.hostid ';
	}
	$sql = 'SELECT DISTINCT i.*'.
			' FROM items i '.$sql_from.
			' WHERE i.key_='.zbx_dbstr($key).
				$sql_where;
	if ($item = DBfetch(DBselect($sql))) {
		$item = $item;
	}
	return $item;
}

function get_item_by_itemid($itemid) {
	$db_items = DBfetch(DBselect('SELECT i.* FROM items i WHERE i.itemid='.$itemid));
	if ($db_items) {
		return $db_items;
	}
	error(_s('No item with itemid="%1$s".', $itemid));
	return false;
}

function get_item_by_itemid_limited($itemid) {
	$row = DBfetch(DBselect(
		'SELECT i.itemid,i.interfaceid,i.name,i.key_,i.hostid,i.delay,i.history,i.status,i.type,i.lifetime,'.
			'i.snmp_community,i.snmp_oid,i.value_type,i.data_type,i.trapper_hosts,i.port,i.units,i.multiplier,i.delta,'.
			'i.snmpv3_securityname,i.snmpv3_securitylevel,i.snmpv3_authpassphrase,i.snmpv3_privpassphrase,'.
			'i.formula,i.trends,i.logtimefmt,i.valuemapid,i.delay_flex,i.params,i.ipmi_sensor,i.templateid,'.
			'i.authtype,i.username,i.password,i.publickey,i.privatekey,i.flags,i.filter,i.description,i.inventory_link'.
		' FROM items i'.
		' WHERE i.itemid='.$itemid));
	if ($row) {
		return $row;
	}
	error(_s('No item with itemid "%1$s".', $itemid));
	return false;
}

/*
 * Description:
 * Replace items for specified host
 *
 * Comments:
 * $error= true : rise Error if item doesn't exist (error generated), false: special processing (NO error generated)
 */
function get_same_item_for_host($item, $dest_hostids) {
	$return_array = is_array($dest_hostids);
	zbx_value2array($dest_hostids);

	if (!is_array($item)) {
		$itemid = $item;
	}
	elseif (isset($item['itemid'])) {
		$itemid = $item['itemid'];
	}

	$same_item = null;
	$same_items = array();

	if (isset($itemid)) {
		$db_items = DBselect(
			'SELECT src.*'.
			' FROM items src,items dest'.
			' WHERE dest.itemid='.$itemid.
				' AND src.key_=dest.key_'.
				' AND '.DBcondition('src.hostid', $dest_hostids)
		);
		while ($db_item = DBfetch($db_items)) {
			if (is_array($item)) {
				$same_item = $db_item;
				$same_items[$db_item['itemid']] = $db_item;
			}
			else {
				$same_item = $db_item['itemid'];
				$same_items[$db_item['itemid']] = $db_item['itemid'];
			}
		}
		if ($return_array) {
			return $same_items;
		}
		else {
			return $same_item;
		}
	}
	return false;
}

/**
 * Resolve macros in item key.
 * Resolve {HOSTNAME}, {IPADDRESS}, {HOST.IP}, {HOST.DNS}, {HOST.CONN}, {HOST.HOST}, {HOST.NAME} and user macros.
 * Macros related to interface resolved only for host items.
 *
 * @param array $item
 *
 * @return string
 */
function resolveItemKeyMacros(array $item) {
	$key =& $item['key_'];
	$macStack = array();
	$macros = array('{HOSTNAME}', '{IPADDRESS}', '{HOST.IP}', '{HOST.DNS}', '{HOST.CONN}', '{HOST.HOST}', '{HOST.NAME}');

	foreach ($macros as $macro) {
		if (zbx_strpos($key, $macro) !== false) {
			$macStack[] = $macro;
		}
	}

	if (!empty($macStack)) {
		$dbItem = API::Item()->get(array(
			'itemids' => $item['itemid'],
			'selectInterfaces' => array('ip', 'dns', 'useip'),
			'selectHosts' => array('host', 'name'),
			'output' => API_OUTPUT_REFER
		));
		$dbItem = reset($dbItem);

		$host = reset($dbItem['hosts']);
		$interface = reset($dbItem['interfaces']);

		// if item without interface or template item, resolve interface related macros to *UNKNOWN*
		if (!$interface) {
			$interface = array(
				'ip' => _('*UNKNOWN*'),
				'dns' => _('*UNKNOWN*'),
				'useip' => false,
			);
		}

		foreach ($macStack as $macro) {
			switch ($macro) {
				case '{HOST.NAME}':
					$key = str_replace('{HOST.NAME}', $host['name'], $key);
					break;
				case '{HOSTNAME}': // deprecated
					$key = str_replace('{HOSTNAME}', $host['host'], $key);
					break;
				case '{HOST.HOST}':
					$key = str_replace('{HOST.HOST}', $host['host'], $key);
					break;
				case '{HOST.IP}':
					$key = str_replace('{HOST.IP}', $interface['ip'], $key);
					break;
				case '{IPADDRESS}': // deprecated
					$key = str_replace('{IPADDRESS}', $interface['ip'], $key);
					break;
				case '{HOST.DNS}':
					$key = str_replace('{HOST.DNS}', $interface['dns'], $key);
					break;
				case '{HOST.CONN}':
					$key = str_replace('{HOST.CONN}', $interface['useip'] ? $interface['ip'] : $interface['dns'], $key);
					break;
			}
		}
	}

	if (preg_match('/'.ZBX_PREG_EXPRESSION_USER_MACROS.'/', $key)) {
		$item = API::UserMacro()->resolveItem($item);
	}

	return $item['key_'];
}

/**
 * Expand macros inside key name and return it
 * Example:
 *	key: 'test.key[a, b, "{HOSTNAME}"]'
 *	name: 'Test item $1, $2, $3'
 *	result: 'Test item a, b, Zabbix-server'
 *
 * @param array $item
 * @return string
 */
function itemName($item) {
	$name = $item['name'];

	// if item name contains $1..$9 macros, we need to expand them
	if (preg_match('/\$[1-9]/', $name)) {
		$key = resolveItemKeyMacros($item);

		// parsing key to get the parameters out of it
		$ItemKey = new CItemKey($key);
		if ($ItemKey->isValid()) {
			$keyParameters = $ItemKey->getParameters();
			$searchOffset = 0;
			while (preg_match('/\$[1-9]/', $name, $matches, PREG_OFFSET_CAPTURE, $searchOffset)) {
				// matches[0][0] - matched param, [1] - second character of it
				$paramNumber = $matches[0][0][1] - 1;
				$replaceString = isset($keyParameters[$paramNumber]) ? $keyParameters[$paramNumber] : '';

				$name = substr_replace($name, $replaceString, $matches[0][1], 2);
				$searchOffset = $matches[0][1] + strlen($replaceString);
			}
		}
	}
	if (preg_match_all('/'.ZBX_PREG_EXPRESSION_USER_MACROS.'/', $name, $arr)) {
		$macros = API::UserMacro()->getMacros(array(
			'macros' => $arr[1],
			'itemid' => $item['itemid']
		));
		$name = str_replace(array_keys($macros), array_values($macros), $name);
	}
	return $name;
}

function get_realhost_by_itemid($itemid) {
	$item = get_item_by_itemid($itemid);
	if ($item['templateid'] <> 0) {
		return get_realhost_by_itemid($item['templateid']); // attention recursion!
	}
	return get_host_by_itemid($itemid);
}

function fillItemsWithChildTemplates(&$items) {
	$processSecondLevel = false;
	$dbItems = DBselect('SELECT i.itemid,i.templateid FROM items i WHERE '.DBcondition('i.itemid', zbx_objectValues($items, 'templateid')));
	while ($dbItem = DBfetch($dbItems)) {
		foreach ($items as $itemid => $item) {
			if ($item['templateid'] == $dbItem['itemid'] && !empty($dbItem['templateid'])) {
				$items[$itemid]['templateid'] = $dbItem['templateid'];
				$processSecondLevel = true;
			}
		}
	}
	if ($processSecondLevel) {
		fillItemsWithChildTemplates($items); // attention recursion!
	}
}

function get_realrule_by_itemid_and_hostid($itemid, $hostid) {
	$item = get_item_by_itemid($itemid);
	if (bccomp($hostid,$item['hostid']) == 0) {
		return $item['itemid'];
	}
	if ($item['templateid'] <> 0) {
		return get_realrule_by_itemid_and_hostid($item['templateid'], $hostid);
	}
	return $item['itemid'];
}

/**
 * Retrieve overview table object for items.
 * @param $hostids
 * @param null $view_style
 * @return CTableInfo
 */
function get_items_data_overview($hostids, $view_style) {
	global $USER_DETAILS;

	$table = new CTableInfo(_('No items defined.'));

	$db_items = DBselect(
		'SELECT DISTINCT h.hostid,h.name AS hostname,i.itemid,i.key_,i.value_type,i.lastvalue,i.units,i.lastclock,'.
			'i.name,t.priority,i.valuemapid,t.value AS tr_value,t.triggerid'.
		' FROM hosts h,items i'.
			' LEFT JOIN functions f ON f.itemid=i.itemid'.
			' LEFT JOIN triggers t ON t.triggerid=f.triggerid AND t.status='.TRIGGER_STATUS_ENABLED.
		' WHERE '.DBcondition('h.hostid', $hostids).
			' AND h.status='.HOST_STATUS_MONITORED.
			' AND h.hostid=i.hostid'.
			' AND i.status='.ITEM_STATUS_ACTIVE.
			' AND '.DBcondition('i.flags', array(ZBX_FLAG_DISCOVERY_NORMAL, ZBX_FLAG_DISCOVERY_CREATED)).
		' ORDER BY i.name,i.itemid'
	);

	// fetch data for the host JS menu
	$hosts = API::Host()->get(array(
		'output' => array('name', 'hostid'),
		'monitored_hosts' => true,
		'selectScreens' => API_OUTPUT_COUNT,
		'selectInventory' => true,
		'preservekeys' => true
	));
	$hostScripts = API::Script()->getScriptsByHosts(zbx_objectValues($hosts, 'hostid'));
	foreach ($hostScripts as $hostid => $scripts) {
		$hosts[$hostid]['scripts'] = $scripts;
	}

	$items = array();
	while ($row = DBfetch($db_items)) {
		$descr = itemName($row);
		$row['hostname'] = get_node_name_by_elid($row['hostid'], null, ': ').$row['hostname'];
		$hostNames[$row['hostid']] = $row['hostname'];

		// a little tricky check for attempt to overwrite active trigger (value=1) with
		// inactive or active trigger with lower priority.
		if (!isset($items[$descr][$row['hostname']])
				|| (($items[$descr][$row['hostname']]['tr_value'] == TRIGGER_VALUE_FALSE && $row['tr_value'] == TRIGGER_VALUE_TRUE)
					|| (($items[$descr][$row['hostname']]['tr_value'] == TRIGGER_VALUE_FALSE || $row['tr_value'] == TRIGGER_VALUE_TRUE)
						&& $row['priority'] > $items[$descr][$row['hostname']]['severity']))) {
			$items[$descr][$row['hostname']] = array(
				'itemid'	=> $row['itemid'],
				'value_type'=> $row['value_type'],
				'lastvalue'	=> $row['lastvalue'],
				'lastclock'	=> $row['lastclock'],
				'units'		=> $row['units'],
				'name'		=> $row['name'],
				'valuemapid' => $row['valuemapid'],
				'severity'	=> $row['priority'],
				'tr_value'	=> $row['tr_value'],
				'triggerid'	=> $row['triggerid']
			);
		}
	}

	if (!isset($hostNames)) {
		return $table;
	}

	order_result($hostNames);

	$css = getUserTheme($USER_DETAILS);
	if ($view_style == STYLE_TOP) {
		$header = array(new CCol(_('Items'), 'center'));
		foreach ($hostNames as $hostname) {
			$header = array_merge($header, array(new CImg('vtext.php?text='.urlencode($hostname).'&theme='.$css)));
		}
		$table->setHeader($header, 'vertical_header');

		foreach ($items as $descr => $ithosts) {
			$table_row = array(nbsp($descr));
			foreach ($hostNames as $hostname) {
				$table_row = get_item_data_overview_cells($table_row, $ithosts, $hostname);
			}
			$table->addRow($table_row);
		}
	}
	else {
		$header = array(new CCol(_('Hosts'), 'center'));
		foreach ($items as $descr => $ithosts) {
			$header = array_merge($header, array(new CImg('vtext.php?text='.urlencode($descr).'&theme='.$css)));
		}
		$table->setHeader($header, 'vertical_header');

		foreach ($hostNames as $hostid => $hostname) {
			$host = $hosts[$hostid];

			// host JS menu link
			$hostSpan = new CSpan(nbsp($host['name']), 'link_menu menu-host');
			$scripts = ($hostScripts[$host['hostid']]) ? $hostScripts[$host['hostid']] : array();
			$hostSpan->setAttribute('data-menu', hostMenuData($host, $scripts));

			$table_row = array(new CCol($hostSpan));
			foreach ($items as $ithosts) {
				$table_row = get_item_data_overview_cells($table_row, $ithosts, $hostname);
			}
			$table->addRow($table_row);
		}
	}
	return $table;
}

function get_item_data_overview_cells(&$table_row, &$ithosts, $hostname) {
	$css_class = '';
	unset($it_ov_menu);

	$value = '-';
	$ack = null;
	if (isset($ithosts[$hostname])) {
		if ($ithosts[$hostname]['tr_value'] == TRIGGER_VALUE_TRUE) {
			$css_class = getSeverityStyle($ithosts[$hostname]['severity']);
			$ack = get_last_event_by_triggerid($ithosts[$hostname]['triggerid']);
			if ($ack['acknowledged'] == 1) {
				$ack = array(SPACE, new CImg('images/general/tick.png', 'ack'));
			}
			else {
				$ack = null;
			}
		}
		$value = format_lastvalue($ithosts[$hostname]);

		$it_ov_menu = array(
			array(_('Values'), null, null, array('outer' => array('pum_oheader'), 'inner' => array('pum_iheader'))),
			array(_('500 latest values'), 'history.php?action=showlatest&itemid='.$ithosts[$hostname]['itemid'], array('tw' => '_blank'))
		);

		switch ($ithosts[$hostname]['value_type']) {
			case ITEM_VALUE_TYPE_UINT64:
			case ITEM_VALUE_TYPE_FLOAT:
				$it_ov_menu = array_merge(array(
					// name, url, (target [tw], statusbar [sb]), css, submenu
					array(_('Graphs'), null, null,
						array('outer' => array('pum_oheader'), 'inner' => array('pum_iheader'))
					),
					array(_('Last hour graph'), 'history.php?period=3600&action=showgraph&itemid='.$ithosts[$hostname]['itemid'], array('tw' => '_blank')),
					array(_('Last week graph'), 'history.php?period=604800&action=showgraph&itemid='.$ithosts[$hostname]['itemid'], array('tw' => '_blank')),
					array(_('Last month graph'), 'history.php?period=2678400&action=showgraph&itemid='.$ithosts[$hostname]['itemid'], array('tw' => '_blank'))
				), $it_ov_menu);
				break;
			default:
				break;
		}
	}

	if ($value != '-') {
		$value = new CSpan($value, 'link');
	}
	$value_col = new CCol(array($value, $ack), $css_class);

	if (isset($it_ov_menu)) {
		$it_ov_menu  = new CPUMenu($it_ov_menu, 170);
		$value_col->onClick($it_ov_menu->getOnActionJS());
		unset($it_ov_menu);
	}
	array_push($table_row,$value_col);
	return $table_row;
}

/******************************************************************************
 *                                                                            *
 * Comments: !!! Don't forget sync code with C !!!                            *
 *                                                                            *
 ******************************************************************************/
function get_same_applications_for_host($applications, $hostid) {
	$child_applications = array();
	$db_apps = DBselect(
		'SELECT a1.applicationid'.
				' FROM applications a1,applications a2'.
				' WHERE a1.name=a2.name'.
				' AND a1.hostid='.$hostid.
				' AND '.DBcondition('a2.applicationid', $applications)
	);
	while ($app = DBfetch($db_apps)) {
		$child_applications[] = $app['applicationid'];
	}

	return $child_applications;
}

/******************************************************************************
 *                                                                            *
 * Comments: !!! Don't forget sync code with C !!!                            *
 *                                                                            *
 ******************************************************************************/
function get_applications_by_itemid($itemids, $field = 'applicationid') {
	zbx_value2array($itemids);
	$result = array();
	$db_applications = DBselect(
		'SELECT DISTINCT app.'.$field.' AS result'.
		' FROM applications app,items_applications ia'.
		' WHERE app.applicationid=ia.applicationid'.
			' AND '.DBcondition('ia.itemid', $itemids)
	);
	while ($db_application = DBfetch($db_applications)) {
		array_push($result, $db_application['result']);
	}
	return $result;
}

/**
 * Clear items history and trends.
 *
 * @param $itemIds
 *
 * @return bool
 */
function delete_history_by_itemid($itemIds) {
	zbx_value2array($itemIds);
	$result = delete_trends_by_itemid($itemIds);
	if (!$result) {
		return $result;
	}
	DBexecute('DELETE FROM history_text WHERE '.DBcondition('itemid', $itemIds));
	DBexecute('DELETE FROM history_log WHERE '.DBcondition('itemid', $itemIds));
	DBexecute('DELETE FROM history_uint WHERE '.DBcondition('itemid', $itemIds));
	DBexecute('DELETE FROM history_str WHERE '.DBcondition('itemid', $itemIds));
	DBexecute('DELETE FROM history WHERE '.DBcondition('itemid', $itemIds));

	return true;
}

/**
 * Clear trends history for provided item ids.
 *
 * @param mixed $itemIds IDs of items for which history should be cleared
 *
 * @return bool
 */
function delete_trends_by_itemid($itemIds) {
	zbx_value2array($itemIds);
	$r1 = DBexecute('DELETE FROM trends WHERE '.DBcondition('itemid', $itemIds));
	$r2 = DBexecute('DELETE FROM trends_uint WHERE '.DBcondition('itemid', $itemIds));

	return $r1 && $r2;
}

function format_lastvalue($db_item) {
	if (!isset($db_item['lastvalue']) || $db_item['lastclock'] == 0) {
		return '-';
	}
	if ($db_item['value_type'] == ITEM_VALUE_TYPE_FLOAT || $db_item['value_type'] == ITEM_VALUE_TYPE_UINT64) {
		$lastvalue=convert_units($db_item['lastvalue'], $db_item['units']);
	}
	elseif ($db_item['value_type'] == ITEM_VALUE_TYPE_STR || $db_item['value_type'] == ITEM_VALUE_TYPE_TEXT || $db_item['value_type'] == ITEM_VALUE_TYPE_LOG) {
		$lastvalue = $db_item['lastvalue'];
		if (zbx_strlen($lastvalue) > 20) {
			$lastvalue = zbx_substr($lastvalue, 0, 20).' ...';
		}
		$lastvalue = nbsp(htmlspecialchars($lastvalue));
	}
	else {
		$lastvalue = _('Unknown value type');
	}
	if ($db_item['valuemapid'] > 0) {
		$lastvalue = applyValueMap($lastvalue, $db_item['valuemapid']);
	}
	return $lastvalue;
}

/*
 * Parameters:
 *     itemid - item ID
 *     last  - 0 - last value (clock is used), 1 - last value
 */
function item_get_history($db_item, $last = 1, $clock = 0, $ns = 0) {
	$value = null;

	switch ($db_item['value_type']) {
		case ITEM_VALUE_TYPE_FLOAT:
			$table = 'history';
			break;
		case ITEM_VALUE_TYPE_UINT64:
			$table = 'history_uint';
			break;
		case ITEM_VALUE_TYPE_TEXT:
			$table = 'history_text';
			break;
		case ITEM_VALUE_TYPE_STR:
			$table = 'history_str';
			break;
		case ITEM_VALUE_TYPE_LOG:
		default:
			$table = 'history_log';
			break;
	}

	$config = select_config();

	if ($last == 0) {
		$sql = 'SELECT value'.
				' FROM '.$table.
				' WHERE itemid='.$db_item['itemid'].
					' AND clock='.$clock.
					' AND ns='.$ns;
		if (null != ($row = DBfetch(DBselect($sql, 1)))) {
			$value = $row['value'];
		}
		if ($value != null) {
			return $value;
		}

		$max_clock = 0;

		$sql = 'SELECT DISTINCT clock'.
				' FROM '.$table.
				' WHERE itemid='.$db_item['itemid'].
					' AND clock='.$clock.
					' AND ns<'.$ns;
		if (null != ($row = DBfetch(DBselect($sql)))) {
			$max_clock = $row['clock'];
		}
		if ($max_clock == 0) {
			$sql = 'SELECT MAX(clock) AS clock'.
					' FROM '.$table.
					' WHERE itemid='.$db_item['itemid'].
						' AND clock<'.$clock;
			if (null != ($row = DBfetch(DBselect($sql)))) {
				$max_clock = $row['clock'];
			}
		}
		if ($max_clock == 0) {
			return $value;
		}

		if ($clock == $max_clock) {
			$sql = 'SELECT value'.
					' FROM '.$table.
					' WHERE itemid='.$db_item['itemid'].
						' AND clock='.$clock.
						' AND ns<'.$ns;
		}
		else {
			$sql = 'SELECT value'.
					' FROM '.$table.
					' WHERE itemid='.$db_item['itemid'].
						' AND clock='.$max_clock.
					' ORDER BY itemid,clock desc,ns desc';
		}

		if (null != ($row = DBfetch(DBselect($sql, 1)))) {
			$value = $row['value'];
		}
	}
	else {
		$row = DBfetch(DBselect('SELECT MAX(clock) AS clock FROM '.$table.' WHERE itemid='.$db_item['itemid']));
		if (!empty($row['clock'])) {
			$row = DBfetch(DBselect('SELECT value FROM '.$table.' WHERE itemid='.$db_item['itemid'].' AND clock='.$row['clock'].' ORDER BY ns DESC', 1));
			if (!empty($row['value'])) {
				$value = $row['value'];
			}
		}
	}
	return $value;
}

/*
 * Purpose: check if current time is within given period
 *
 * Parameters: period - [IN] time period in format [wd[-wd2],hh:mm-hh:mm]
 *             now    - [IN] timestamp for comparison
 *
 * Return value: 0 - out of period, 1 - within the period
 *
 * Comments:
 *        !!! Don't forget sync code with C !!!
 */
function check_time_period($period, $now) {
	$tm = localtime($now, true);
	$day = (0 == $tm['tm_wday']) ? 7 : $tm['tm_wday'];
	$sec = SEC_PER_HOUR * $tm['tm_hour'] + SEC_PER_MIN * $tm['tm_min'] + $tm['tm_sec'];
	$flag = (6 == sscanf($period, '%d-%d,%d:%d-%d:%d', $d1, $d2, $h1, $m1, $h2, $m2));
	if (!$flag) {
		$flag = (5 == sscanf($period, '%d,%d:%d-%d:%d', $d1, $h1, $m1, $h2, $m2));
		$d2 = $d1;
	}
	if (!$flag) {
		// Delay period format is wrong - skip
	}
	else {
		if ($day >= $d1 && $day <= $d2
			&& $sec >= (SEC_PER_HOUR * $h1 + SEC_PER_MIN * $m1)
			&& $sec <= (SEC_PER_HOUR * $h2 + SEC_PER_MIN * $m2)) {
			return true;
		}
	}
	return false;
}

function getItemDelay($delay, $flexIntervals) {
	if (!empty($delay) || zbx_empty($flexIntervals)) {
		return $delay;
	}
	$minDelay = SEC_PER_YEAR;
	$flexIntervals = explode(';', $flexIntervals);
	foreach ($flexIntervals as $flexInterval) {
		if (sscanf($flexInterval, "%d/%29s", $flexDelay, $flexPeriod) != 2) {
			continue;
		}
		$minDelay = min($minDelay, $flexDelay);
	}
	return $minDelay;
}
/*
 * Function: get_current_delay
 *
 * Purpose: return delay value that is currently applicable
 *
 * Parameters: delay          - [IN] default delay
 *             flex_intervals - [IN] separated flexible intervals
 *
 *                                   +------------[;]<----------+
 *                                   |                          |
 *                                 ->+-[d/wd[-wd2],hh:mm-hh:mm]-+
 *
 *                                 d       - delay (0-n)
 *                                 wd, wd2 - day of week (1-7)
 *                                 hh      - hours (0-24)
 *                                 mm      - minutes (0-59)
 *
 *             now            - [IN] current time
 *
 * Return value: delay value - either default or minimum delay value
 *                             out of all applicable intervals
 *
 * Author: Alexander Vladishev
 */
function get_current_delay($delay, $flex_intervals, $now) {
	if (zbx_empty($flex_intervals)) {
		return $delay;
	}
	$current_delay = SEC_PER_YEAR;
	$arr_of_flex_intervals = explode(';', $flex_intervals);
	foreach ($arr_of_flex_intervals as $flex_interval) {
		if (sscanf($flex_interval, "%d/%29s", $flex_delay, $flex_period) != 2) {
			continue;
		}
		if ($flex_delay < $current_delay && check_time_period($flex_period, $now)) {
			$current_delay = $flex_delay;
		}
	}
	if ($current_delay == SEC_PER_YEAR) {
		return $delay;
	}
	return $current_delay == 0 ? SEC_PER_YEAR : $current_delay;
}

/*
 * Function: get_next_delay_interval
 *
 * Purpose: return time of next flexible interval
 *
 * Parameters: flex_intervals - [IN] separated flexible intervals
	 *
 *                                   +------------[;]<----------+
 *                                   |                          |
 *                                 ->+-[d/wd[-wd2],hh:mm-hh:mm]-+
	 *
 *                                 d       - delay (0-n)
 *                                 wd, wd2 - day of week (1-7)
 *                                 hh      - hours (0-24)
 *                                 mm      - minutes (0-59)
	 *
 *             now            - [IN] current time
 *
 * Return value: start of next interval
 *
 * Author: Alexei Vladishev, Alexander Vladishev
 */
function get_next_delay_interval($flex_intervals, $now, &$next_interval) {
	if (zbx_empty($flex_intervals)) {
		return false;
	}
	$next = 0;
	$tm = localtime($now, true);
	$day = (0 == $tm['tm_wday']) ? 7 : $tm['tm_wday'];
	$sec = SEC_PER_HOUR * $tm['tm_hour'] + SEC_PER_MIN * $tm['tm_min'] + $tm['tm_sec'];
	$arr_of_flex_intervals = explode(';', $flex_intervals);
	foreach ($arr_of_flex_intervals as $flex_interval) {
		if (7 != sscanf($flex_interval, "%d/%d-%d,%d:%d-%d:%d", $delay, $d1, $d2, $h1, $m1, $h2, $m2)) {
			if (6 != sscanf($flex_interval, "%d/%d,%d:%d-%d:%d", $delay, $d1, $h1, $m1, $h2, $m2)) {
				continue;
			}
			$d2 = $d1;
		}
		$sec1 = SEC_PER_HOUR * $h1 + SEC_PER_MIN * $m1;
		$sec2 = SEC_PER_HOUR * $h2 + SEC_PER_MIN * $m2;

		if ($day >= $d1 && $day <= $d2 && $sec >= $sec1 && $sec <= $sec2) {
			// current period
			if ($next == 0 || ($next > ($now - $sec + $sec2))) {
				$next = $now - $sec + $sec2;
			}
		}
		elseif ($day >= $d1 && $day <= $d2 && $sec < $sec1) {
			// will be active today
			if ($next == 0 || ($next > ($now - $sec + $sec1))) {
				$next = $now - $sec + $sec1;
			}
		}
		else {
			$next_day = ($day + 1 <= 7) ? $day + 1 : 1;

			if ($next_day >= $d1 && $next_day <= $d2) {
				// will be active tomorrow
				if ($next == 0 || ($next > ($now - $sec + SEC_PER_DAY + $sec1))) {
					$next = $now - $sec + SEC_PER_DAY + $sec1;
				}
			}
			else {
				if ($day < $d1) {
					$day_diff = $d1 - $day;
				}
				if ($day >= $d2) {
					$day_diff = ($d1 + 7) - $day;
				}
				if ($day >= $d1 && $day < $d2) {
					// should never happen, could not deduce day difference
					$day_diff = -1;
				}
				if ($day_diff != -1) {
					if ($next == 0 || ($next > ($now - $sec + SEC_PER_DAY * $day_diff + $sec1))) {
						$next = $now - $sec + SEC_PER_DAY * $day_diff + $sec1;
					}
				}
			}
		}
	}
	if ($next != 0) {
		$next_interval = $next;
	}
	return $next;
}

/*
 * Function: calculate_item_nextcheck
 *
 * Description:
 *     calculate nextcheck timestamp for item
 *
 * Parameters:
 *     itemid - item ID
 *     item_type - item type
 *     delay - item's refresh rate in sec
 *     flex_intervals - item's flexible refresh rate
 *     now - current timestamp
 *
 * Author:
 *     Alexander Vladishev
 *
 * Comments:
 *     !!! Don't forget sync code with C !!!
 */
function calculate_item_nextcheck($interfaceid, $itemid, $item_type, $delay, $flex_intervals, $now) {
	if ($delay == 0) {
		$delay = SEC_PER_YEAR;
	}

	// special processing of active items to see better view in queue
	if ($item_type == ITEM_TYPE_ZABBIX_ACTIVE) {
		$nextcheck = $now + $delay;
	}
	else {
		$current_delay = get_current_delay($delay, $flex_intervals, $now);

		if (get_next_delay_interval($flex_intervals, $now, $next_interval) && ($now + $current_delay) > $next_interval) {
			// next check falls out of the current interval
			do {
				$current_delay = get_current_delay($delay, $flex_intervals, $next_interval + 1);

				// as soon as item check in the interval is not forbidden with delay=0, use it
				if (SEC_PER_YEAR != $current_delay) {
					break;
				}
				get_next_delay_interval($flex_intervals, $next_interval + 1, $next_interval);
			}
			while ($next_interval - $now < SEC_PER_WEEK);
			// checking the nearest week for delay!=0

			$now = $next_interval;
		}
		$delay = $current_delay;
		$shift = $item_type == ITEM_TYPE_JMX ? $interfaceid : $itemid;
		$nextcheck = $delay * floor($now / $delay) + ($shift % $delay);

		while ($nextcheck <= $now) {
			$nextcheck += $delay;
		}
	}
	return array('nextcheck' => $nextcheck, 'delay' => $delay);
}

/**
 * Check if given character is a valid key id char
 * this function is a copy of is_key_char() from /src/libs/zbxcommon/misc.c
 * don't forget to take look in there before changing anything
 *
 * @author Konstantin Buravcov
 * @param string $char
 * @return bool
 */
function isKeyIdChar($char) {
	return (
		($char >= 'a' && $char <= 'z')
		|| $char == '.' || $char == '_' || $char == '-'
		|| ($char >= 'A' && $char <= 'Z')
		|| ($char >= '0' && $char <= '9')
	);
}

/*
 * Description:
 *	Function returns true if http items exists in the $items array.
 *	The array should contain a field 'type'
 */
function httpItemExists($items) {
	foreach ($items as $item) {
		if ($item['type'] == ITEM_TYPE_HTTPTEST) {
			return true;
		}
	}
	return false;
}

function getParamFieldNameByType($itemType) {
	switch ($itemType) {
		case ITEM_TYPE_SSH:
		case ITEM_TYPE_TELNET:
		case ITEM_TYPE_JMX:
			return 'params_es';
		case ITEM_TYPE_DB_MONITOR:
			return 'params_ap';
		case ITEM_TYPE_CALCULATED:
			return 'params_f';
		default:
			return 'params';
	}
}

function getParamFieldLabelByType($itemType) {
	switch ($itemType) {
		case ITEM_TYPE_SSH:
		case ITEM_TYPE_TELNET:
		case ITEM_TYPE_JMX:
			return _('Executed script');
		case ITEM_TYPE_DB_MONITOR:
			return _('Additional parameters');
		case ITEM_TYPE_CALCULATED:
			return _('Formula');
		default:
			return 'params';
	}
}
?>
