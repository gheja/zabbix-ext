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


/**
 * @package API
 */
class CHost extends CZBXAPI {

	protected $tableName = 'hosts';

	protected $tableAlias = 'h';

	/**
	 * Get Host data
	 *
	 * @param array         $options
	 * @param array         $options['nodeids']                  Node IDs
	 * @param array         $options['groupids']                 HostGroup IDs
	 * @param array         $options['hostids']                  Host IDs
	 * @param boolean       $options['monitored_hosts']          only monitored Hosts
	 * @param boolean       $options['templated_hosts']          include templates in result
	 * @param boolean       $options['with_items']               only with items
	 * @param boolean       $options['with_monitored_items']     only with monitored items
	 * @param boolean       $options['with_historical_items']    only with historical items
	 * @param boolean       $options['with_triggers']            only with triggers
	 * @param boolean       $options['with_monitored_triggers']  only with monitored triggers
	 * @param boolean       $options['with_httptests']           only with http tests
	 * @param boolean       $options['with_monitored_httptests'] only with monitored http tests
	 * @param boolean       $options['with_graphs']              only with graphs
	 * @param boolean       $options['editable']                 only with read-write permission. Ignored for SuperAdmins
	 * @param boolean       $options['selectGroups']             select HostGroups
	 * @param boolean       $options['selectTemplates']          select Templates
	 * @param boolean       $options['selectItems']              select Items
	 * @param boolean       $options['selectTriggers']           select Triggers
	 * @param boolean       $options['selectGraphs']             select Graphs
	 * @param boolean       $options['selectApplications']       select Applications
	 * @param boolean       $options['selectMacros']             select Macros
	 * @param boolean|array $options['selectInventory']          select Inventory
	 * @param boolean       $options['withInventory']            select only hosts with inventory
	 * @param int           $options['count']                    count Hosts, returned column name is rowscount
	 * @param string        $options['pattern']                  search hosts by pattern in Host name
	 * @param string        $options['extendPattern']            search hosts by pattern in Host name, ip and DNS
	 * @param int           $options['limit']                    limit selection
	 * @param string        $options['sortfield']                field to sort by
	 * @param string        $options['sortorder']                sort order
	 *
	 * @return array|boolean Host data as array or false if error
	 */
	public function get($options = array()) {
		$result = array();
		$nodeCheck = false;
		$userType = self::$userData['type'];
		$userid = self::$userData['userid'];

		// allowed columns for sorting
		$sortColumns = array('hostid', 'host', 'name', 'status');

		// allowed output options for [ select_* ] params
		$subselectsAllowedOutputs = array(API_OUTPUT_REFER, API_OUTPUT_EXTEND, API_OUTPUT_CUSTOM);

		$sqlParts = array(
			'select'	=> array('hosts' => 'h.hostid'),
			'from'		=> array('hosts' => 'hosts h'),
			'where'		=> array(),
			'group'		=> array(),
			'order'		=> array(),
			'limit'		=> null
		);

		$defOptions = array(
			'nodeids'					=> null,
			'groupids'					=> null,
			'hostids'					=> null,
			'proxyids'					=> null,
			'templateids'				=> null,
			'interfaceids'				=> null,
			'itemids'					=> null,
			'triggerids'				=> null,
			'maintenanceids'			=> null,
			'graphids'					=> null,
			'applicationids'			=> null,
			'dhostids'					=> null,
			'dserviceids'				=> null,
			'httptestids'				=> null,
			'monitored_hosts'			=> null,
			'templated_hosts'			=> null,
			'proxy_hosts'				=> null,
			'with_items'				=> null,
			'with_monitored_items'		=> null,
			'with_historical_items'		=> null,
			'with_triggers'				=> null,
			'with_monitored_triggers'	=> null,
			'with_httptests'			=> null,
			'with_monitored_httptests'	=> null,
			'with_graphs'				=> null,
			'withInventory'				=> null,
			'editable'					=> null,
			'nopermissions'				=> null,
			// filter
			'filter'					=> null,
			'search'					=> null,
			'searchByAny'				=> null,
			'startSearch'				=> null,
			'excludeSearch'				=> null,
			'searchWildcardsEnabled'	=> null,
			// output
			'output'					=> API_OUTPUT_REFER,
			'selectGroups'				=> null,
			'selectParentTemplates'		=> null,
			'selectItems'				=> null,
			'selectDiscoveries'			=> null,
			'selectTriggers'			=> null,
			'selectGraphs'				=> null,
			'selectDHosts'				=> null,
			'selectDServices'			=> null,
			'selectApplications'		=> null,
			'selectMacros'				=> null,
			'selectScreens'				=> null,
			'selectInterfaces'			=> null,
			'selectInventory'			=> null,
			'countOutput'				=> null,
			'groupCount'				=> null,
			'preservekeys'				=> null,

			'sortfield'					=> '',
			'sortorder'					=> '',
			'limit'						=> null,
			'limitSelects'				=> null
		);
		$options = zbx_array_merge($defOptions, $options);

		if (is_array($options['output'])) {
			unset($sqlParts['select']['hosts']);

			$dbTable = DB::getSchema('hosts');
			$sqlParts['select']['hostid'] = 'h.hostid';
			foreach ($options['output'] as $field) {
				if (isset($dbTable['fields'][$field])) {
					$sqlParts['select'][$field] = 'h.'.$field;
				}
			}
			$options['output'] = API_OUTPUT_CUSTOM;
		}

// editable + PERMISSION CHECK
		if ((USER_TYPE_SUPER_ADMIN == $userType) || $options['nopermissions']) {
		}
		else {
			$permission = $options['editable'] ? PERM_READ_WRITE : PERM_READ_ONLY;

			$sqlParts['where'][] = 'EXISTS ('.
							' SELECT hh.hostid'.
							' FROM hosts hh, hosts_groups hgg, rights r, users_groups ug'.
							' WHERE hh.hostid=h.hostid'.
								' AND hh.hostid=hgg.hostid'.
								' AND r.id=hgg.groupid'.
								' AND r.groupid=ug.usrgrpid'.
								' AND ug.userid='.$userid.
								' AND r.permission>='.$permission.
								' AND NOT EXISTS('.
									' SELECT hggg.groupid'.
									' FROM hosts_groups hggg, rights rr, users_groups gg'.
									' WHERE hggg.hostid=hgg.hostid'.
										' AND rr.id=hggg.groupid'.
										' AND rr.groupid=gg.usrgrpid'.
										' AND gg.userid='.$userid.
										' AND rr.permission<'.$permission.
								'))';
		}

// nodeids
		$nodeids = !is_null($options['nodeids']) ? $options['nodeids'] : get_current_nodeid();

// hostids
		if (!is_null($options['hostids'])) {
			zbx_value2array($options['hostids']);
			$sqlParts['where']['hostid'] = DBcondition('h.hostid', $options['hostids']);

			if (!$nodeCheck) {
				$nodeCheck = true;
				$sqlParts['where'][] = DBin_node('h.hostid', $nodeids);
			}
		}

// groupids
		if (!is_null($options['groupids'])) {
			zbx_value2array($options['groupids']);
			if ($options['output'] != API_OUTPUT_SHORTEN) {
				$sqlParts['select']['groupid'] = 'hg.groupid';
			}

			$sqlParts['from']['hosts_groups'] = 'hosts_groups hg';
			$sqlParts['where'][] = DBcondition('hg.groupid', $options['groupids']);
			$sqlParts['where']['hgh'] = 'hg.hostid=h.hostid';

			if (!is_null($options['groupCount'])) {
				$sqlParts['group']['groupid'] = 'hg.groupid';
			}

			if (!$nodeCheck) {
				$nodeCheck = true;
				$sqlParts['where'][] = DBin_node('hg.groupid', $nodeids);
			}
		}


// proxyids
		if (!is_null($options['proxyids'])) {
			zbx_value2array($options['proxyids']);
			if ($options['output'] != API_OUTPUT_SHORTEN) {
				$sqlParts['select']['proxy_hostid'] = 'h.proxy_hostid';
			}
			$sqlParts['where'][] = DBcondition('h.proxy_hostid', $options['proxyids']);
		}

// templateids
		if (!is_null($options['templateids'])) {
			zbx_value2array($options['templateids']);
			if ($options['output'] != API_OUTPUT_SHORTEN) {
				$sqlParts['select']['templateid'] = 'ht.templateid';
			}

			$sqlParts['from']['hosts_templates'] = 'hosts_templates ht';
			$sqlParts['where'][] = DBcondition('ht.templateid', $options['templateids']);
			$sqlParts['where']['hht'] = 'h.hostid=ht.hostid';

			if (!is_null($options['groupCount'])) {
				$sqlParts['group']['templateid'] = 'ht.templateid';
			}

			if (!$nodeCheck) {
				$nodeCheck = true;
				$sqlParts['where'][] = DBin_node('ht.templateid', $nodeids);
			}
		}

// interfaceids
		if (!is_null($options['interfaceids'])) {
			zbx_value2array($options['interfaceids']);
			if ($options['output'] != API_OUTPUT_SHORTEN) {
				$sqlParts['select']['interfaceid'] = 'hi.interfaceid';
			}

			$sqlParts['from']['interface'] = 'interface hi';
			$sqlParts['where'][] = DBcondition('hi.interfaceid', $options['interfaceids']);
			$sqlParts['where']['hi'] = 'h.hostid=hi.hostid';

			if (!$nodeCheck) {
				$nodeCheck = true;
				$sqlParts['where'][] = DBin_node('hi.interfaceid', $nodeids);
			}
		}

// itemids
		if (!is_null($options['itemids'])) {
			zbx_value2array($options['itemids']);
			if ($options['output'] != API_OUTPUT_SHORTEN) {
				$sqlParts['select']['itemid'] = 'i.itemid';
			}

			$sqlParts['from']['items'] = 'items i';
			$sqlParts['where'][] = DBcondition('i.itemid', $options['itemids']);
			$sqlParts['where']['hi'] = 'h.hostid=i.hostid';

			if (!$nodeCheck) {
				$nodeCheck = true;
				$sqlParts['where'][] = DBin_node('i.itemid', $nodeids);
			}
		}

// triggerids
		if (!is_null($options['triggerids'])) {
			zbx_value2array($options['triggerids']);
			if ($options['output'] != API_OUTPUT_SHORTEN) {
				$sqlParts['select']['triggerid'] = 'f.triggerid';
			}

			$sqlParts['from']['functions'] = 'functions f';
			$sqlParts['from']['items'] = 'items i';
			$sqlParts['where'][] = DBcondition('f.triggerid', $options['triggerids']);
			$sqlParts['where']['hi'] = 'h.hostid=i.hostid';
			$sqlParts['where']['fi'] = 'f.itemid=i.itemid';

			if (!$nodeCheck) {
				$nodeCheck = true;
				$sqlParts['where'][] = DBin_node('f.triggerid', $nodeids);
			}
		}

// httptestids
		if (!is_null($options['httptestids'])) {
			zbx_value2array($options['httptestids']);
			if ($options['output'] != API_OUTPUT_SHORTEN) {
				$sqlParts['select']['httptestid'] = 'ht.httptestid';
			}

			$sqlParts['from']['applications'] = 'applications a';
			$sqlParts['from']['httptest'] = 'httptest ht';
			$sqlParts['where'][] = DBcondition('ht.httptestid', $options['httptestids']);
			$sqlParts['where']['aht'] = 'a.applicationid=ht.applicationid';
			$sqlParts['where']['ah'] = 'a.hostid=h.hostid';

			if (!$nodeCheck) {
				$nodeCheck = true;
				$sqlParts['where'][] = DBin_node('ht.httptestid', $nodeids);
			}
		}

// graphids
		if (!is_null($options['graphids'])) {
			zbx_value2array($options['graphids']);
			if ($options['output'] != API_OUTPUT_SHORTEN) {
				$sqlParts['select']['graphid'] = 'gi.graphid';
			}

			$sqlParts['from']['graphs_items'] = 'graphs_items gi';
			$sqlParts['from']['items'] = 'items i';
			$sqlParts['where'][] = DBcondition('gi.graphid', $options['graphids']);
			$sqlParts['where']['igi'] = 'i.itemid=gi.itemid';
			$sqlParts['where']['hi'] = 'h.hostid=i.hostid';

			if (!$nodeCheck) {
				$nodeCheck = true;
				$sqlParts['where'][] = DBin_node('gi.graphid', $nodeids);
			}
		}

// applicationids
		if (!is_null($options['applicationids'])) {
			zbx_value2array($options['applicationids']);
			if ($options['output'] != API_OUTPUT_SHORTEN) {
				$sqlParts['select']['applicationid'] = 'a.applicationid';
			}

			$sqlParts['from']['applications'] = 'applications a';
			$sqlParts['where'][] = DBcondition('a.applicationid', $options['applicationids']);
			$sqlParts['where']['ah'] = 'a.hostid=h.hostid';

			if (!$nodeCheck) {
				$nodeCheck = true;
				$sqlParts['where'][] = DBin_node('a.applicationid', $nodeids);
			}
		}

// dhostids
		if (!is_null($options['dhostids'])) {
			zbx_value2array($options['dhostids']);
			if ($options['output'] != API_OUTPUT_SHORTEN) {
				$sqlParts['select']['dhostid'] = 'ds.dhostid';
			}

			$sqlParts['from']['dservices'] = 'dservices ds';
			$sqlParts['where'][] = DBcondition('ds.dhostid', $options['dhostids']);
			$sqlParts['where']['dsh'] = 'ds.ip=h.ip';

			if (!is_null($options['groupCount'])) {
				$sqlParts['group']['dhostid'] = 'ds.dhostid';
			}
		}

// dserviceids
		if (!is_null($options['dserviceids'])) {
			zbx_value2array($options['dserviceids']);
			if ($options['output'] != API_OUTPUT_SHORTEN) {
				$sqlParts['select']['dserviceid'] = 'ds.dserviceid';
			}

			$sqlParts['from']['dservices'] = 'dservices ds';
			$sqlParts['from']['interface'] = 'interface i';
			$sqlParts['where'][] = DBcondition('ds.dserviceid', $options['dserviceids']);
			$sqlParts['where']['dsh'] = 'ds.ip=i.ip';
			$sqlParts['where']['hi'] = 'h.hostid=i.hostid';

			if (!is_null($options['groupCount'])) {
				$sqlParts['group']['dserviceid'] = 'ds.dserviceid';
			}
		}

// maintenanceids
		if (!is_null($options['maintenanceids'])) {
			zbx_value2array($options['maintenanceids']);
			if ($options['output'] != API_OUTPUT_SHORTEN) {
				$sqlParts['select']['maintenanceid'] = 'mh.maintenanceid';
			}

			$sqlParts['from']['maintenances_hosts'] = 'maintenances_hosts mh';
			$sqlParts['where'][] = DBcondition('mh.maintenanceid', $options['maintenanceids']);
			$sqlParts['where']['hmh'] = 'h.hostid=mh.hostid';

			if (!is_null($options['groupCount'])) {
				$sqlParts['group']['maintenanceid'] = 'mh.maintenanceid';
			}
		}

// node check !!!!!
// should be last, after all ****IDS checks
		if (!$nodeCheck) {
			$sqlParts['where'][] = DBin_node('h.hostid', $nodeids);
		}

// monitored_hosts, templated_hosts
		if (!is_null($options['monitored_hosts'])) {
			$sqlParts['where']['status'] = 'h.status='.HOST_STATUS_MONITORED;
		}
		elseif (!is_null($options['templated_hosts'])) {
			$sqlParts['where']['status'] = 'h.status IN ('.HOST_STATUS_MONITORED.','.HOST_STATUS_NOT_MONITORED.','.HOST_STATUS_TEMPLATE.')';
		}
		elseif (!is_null($options['proxy_hosts'])) {
			$sqlParts['where']['status'] = 'h.status IN ('.HOST_STATUS_PROXY_ACTIVE.','.HOST_STATUS_PROXY_PASSIVE.')';
		}
		else {
			$sqlParts['where']['status'] = 'h.status IN ('.HOST_STATUS_MONITORED.','.HOST_STATUS_NOT_MONITORED.')';
		}

// with_items, with_monitored_items, with_historical_items
		if (!is_null($options['with_items'])) {
			$sqlParts['where'][] = 'EXISTS (SELECT i.hostid FROM items i WHERE h.hostid=i.hostid )';
		}
		elseif (!is_null($options['with_monitored_items'])) {
			$sqlParts['where'][] = 'EXISTS (SELECT i.hostid FROM items i WHERE h.hostid=i.hostid AND i.status='.ITEM_STATUS_ACTIVE.')';
		}
		elseif (!is_null($options['with_historical_items'])) {
			$sqlParts['where'][] = 'EXISTS (SELECT i.hostid FROM items i WHERE h.hostid=i.hostid AND (i.status='.ITEM_STATUS_ACTIVE.' OR i.status='.ITEM_STATUS_NOTSUPPORTED.') AND i.lastvalue IS NOT NULL)';
		}

// with_triggers, with_monitored_triggers
		if (!is_null($options['with_triggers'])) {
			$sqlParts['where'][] = 'EXISTS('.
					' SELECT i.itemid'.
					' FROM items i, functions f, triggers t'.
					' WHERE i.hostid=h.hostid'.
						' AND i.itemid=f.itemid'.
						' AND f.triggerid=t.triggerid)';
		}
		elseif (!is_null($options['with_monitored_triggers'])) {
			$sqlParts['where'][] = 'EXISTS('.
					' SELECT i.itemid'.
					' FROM items i, functions f, triggers t'.
					' WHERE i.hostid=h.hostid'.
						' AND i.status='.ITEM_STATUS_ACTIVE.
						' AND i.itemid=f.itemid'.
						' AND f.triggerid=t.triggerid'.
						' AND t.status='.TRIGGER_STATUS_ENABLED.')';
		}

// with_httptests, with_monitored_httptests
		if (!is_null($options['with_httptests'])) {
			$sqlParts['where'][] = 'EXISTS('.
					' SELECT a.applicationid'.
					' FROM applications a, httptest ht'.
					' WHERE a.hostid=h.hostid'.
						' AND ht.applicationid=a.applicationid)';
		}
		elseif (!is_null($options['with_monitored_httptests'])) {
			$sqlParts['where'][] = 'EXISTS('.
					' SELECT a.applicationid'.
					' FROM applications a, httptest ht'.
					' WHERE a.hostid=h.hostid'.
						' AND ht.applicationid=a.applicationid'.
						' AND ht.status='.HTTPTEST_STATUS_ACTIVE.')';
		}

// with_graphs
		if (!is_null($options['with_graphs'])) {
			$sqlParts['where'][] = 'EXISTS('.
					' SELECT DISTINCT i.itemid'.
					' FROM items i, graphs_items gi'.
					' WHERE i.hostid=h.hostid'.
						' AND i.itemid=gi.itemid)';
		}

// withInventory
		if (!is_null($options['withInventory']) && $options['withInventory']) {
			$sqlParts['where'][] = ' h.hostid IN ('.
					' SELECT hin.hostid'.
					' FROM host_inventory hin)';
		}

// search
		if (is_array($options['search'])) {
			zbx_db_search('hosts h', $options, $sqlParts);

			if (zbx_db_search('interface hi', $options, $sqlParts)) {
				$sqlParts['from']['interface'] = 'interface hi';
				$sqlParts['where']['hi'] = 'h.hostid=hi.hostid';
			}
		}

// filter
		if (is_array($options['filter'])) {
			zbx_db_filter('hosts h', $options, $sqlParts);

			if (zbx_db_filter('interface hi', $options, $sqlParts)) {
				$sqlParts['from']['interface'] = 'interface hi';
				$sqlParts['where']['hi'] = 'h.hostid=hi.hostid';
			}
		}

// output
		if ($options['output'] == API_OUTPUT_EXTEND) {
			$sqlParts['select']['hosts'] = 'h.*';
		}

// countOutput
		if (!is_null($options['countOutput'])) {
			$options['sortfield'] = '';
			$sqlParts['select'] = array('count(DISTINCT h.hostid) as rowscount');

// groupCount
			if (!is_null($options['groupCount'])) {
				foreach ($sqlParts['group'] as $key => $fields) {
					$sqlParts['select'][$key] = $fields;
				}
			}
		}

		// sorting
		zbx_db_sorting($sqlParts, $options, $sortColumns, 'h');

// limit
		if (zbx_ctype_digit($options['limit']) && $options['limit']) {
			$sqlParts['limit'] = $options['limit'];
		}
//-------


		$hostids = array();

		$sqlParts['select'] = array_unique($sqlParts['select']);
		$sqlParts['from'] = array_unique($sqlParts['from']);
		$sqlParts['where'] = array_unique($sqlParts['where']);
		$sqlParts['group'] = array_unique($sqlParts['group']);
		$sqlParts['order'] = array_unique($sqlParts['order']);

		$sqlSelect = '';
		$sqlFrom = '';
		$sqlWhere = '';
		$sqlGroup = '';
		$sqlOrder = '';
		if (!empty($sqlParts['select'])) {
			$sqlSelect .= implode(',', $sqlParts['select']);
		}
		if (!empty($sqlParts['from'])) {
			$sqlFrom .= implode(',', $sqlParts['from']);
		}
		if (!empty($sqlParts['where']))	{
			$sqlWhere .= implode(' AND ', $sqlParts['where']);
		}
		if (!empty($sqlParts['group']))	{
			$sqlGroup .= ' GROUP BY '.implode(',', $sqlParts['group']);
		}
		if (!empty($sqlParts['order']))	{
			$sqlOrder .= ' ORDER BY '.implode(',', $sqlParts['order']);
		}
		$sqlLimit = $sqlParts['limit'];

		$sql = 'SELECT '.zbx_db_distinct($sqlParts).' '.$sqlSelect.
				' FROM '.$sqlFrom.
				' WHERE '.$sqlWhere.
				$sqlGroup.
				$sqlOrder;

		$res = DBselect($sql, $sqlLimit);
		while ($host = DBfetch($res)) {
			if (!is_null($options['countOutput'])) {
				if (!is_null($options['groupCount'])) {
					$result[] = $host;
				}
				else {
					$result = $host['rowscount'];
				}
			}
			else {
				$hostids[$host['hostid']] = $host['hostid'];

				if ($options['output'] == API_OUTPUT_SHORTEN) {
					$result[$host['hostid']] = array('hostid' => $host['hostid']);
				}
				else {
					if (!isset($result[$host['hostid']])) $result[$host['hostid']] = array();

					if (!is_null($options['selectGroups']) && !isset($result[$host['hostid']]['groups'])) {
						$result[$host['hostid']]['groups'] = array();
					}
					if (!is_null($options['selectParentTemplates']) && !isset($result[$host['hostid']]['parentTemplates'])) {
						$result[$host['hostid']]['parentTemplates'] = array();
					}
					if (!is_null($options['selectItems']) && !isset($result[$host['hostid']]['items'])) {
						$result[$host['hostid']]['items'] = array();
					}
					if (!is_null($options['selectDiscoveries']) && !isset($result[$host['hostid']]['discoveries'])) {
						$result[$host['hostid']]['discoveries'] = array();
					}
					if (!is_null($options['selectInventory']) && !isset($result[$host['hostid']]['inventory'])) {
						$result[$host['hostid']]['inventory'] = array();
					}
					if (!is_null($options['selectTriggers']) && !isset($result[$host['hostid']]['triggers'])) {
						$result[$host['hostid']]['triggers'] = array();
					}
					if (!is_null($options['selectGraphs']) && !isset($result[$host['hostid']]['graphs'])) {
						$result[$host['hostid']]['graphs'] = array();
					}
					if (!is_null($options['selectDHosts']) && !isset($result[$host['hostid']]['dhosts'])) {
						$result[$host['hostid']]['dhosts'] = array();
					}
					if (!is_null($options['selectDServices']) && !isset($result[$host['hostid']]['dservices'])) {
						$result[$host['hostid']]['dservices'] = array();
					}
					if (!is_null($options['selectApplications']) && !isset($result[$host['hostid']]['applications'])) {
						$result[$host['hostid']]['applications'] = array();
					}
					if (!is_null($options['selectMacros']) && !isset($result[$host['hostid']]['macros'])) {
						$result[$host['hostid']]['macros'] = array();
					}

					if (!is_null($options['selectScreens']) && !isset($result[$host['hostid']]['screens'])) {
						$result[$host['hostid']]['screens'] = array();
					}

					if (!is_null($options['selectInterfaces']) && !isset($result[$host['hostid']]['interfaces'])) {
						$result[$host['hostid']]['interfaces'] = array();
					}

// groupids
					if (isset($host['groupid']) && is_null($options['selectGroups'])) {
						if (!isset($result[$host['hostid']]['groups']))
							$result[$host['hostid']]['groups'] = array();

						$result[$host['hostid']]['groups'][] = array('groupid' => $host['groupid']);
						unset($host['groupid']);
					}

// templateids
					if (isset($host['templateid'])) {
						if (!isset($result[$host['hostid']]['templates']))
							$result[$host['hostid']]['templates'] = array();

						$result[$host['hostid']]['templates'][] = array(
							'templateid' => $host['templateid'],
							'hostid' => $host['templateid']
						);
						unset($host['templateid']);
					}

// triggerids
					if (isset($host['triggerid']) && is_null($options['selectTriggers'])) {
						if (!isset($result[$host['hostid']]['triggers']))
							$result[$host['hostid']]['triggers'] = array();

						$result[$host['hostid']]['triggers'][] = array('triggerid' => $host['triggerid']);
						unset($host['triggerid']);
					}

// interfaceids
					if (isset($host['interfaceid']) && is_null($options['selectInterfaces'])) {
						if (!isset($result[$host['hostid']]['interfaces']))
							$result[$host['hostid']]['interfaces'] = array();

						$result[$host['hostid']]['interfaces'][] = array('interfaceid' => $host['interfaceid']);
						unset($host['interfaceid']);
					}

// itemids
					if (isset($host['itemid']) && is_null($options['selectItems'])) {
						if (!isset($result[$host['hostid']]['items']))
							$result[$host['hostid']]['items'] = array();

						$result[$host['hostid']]['items'][] = array('itemid' => $host['itemid']);
						unset($host['itemid']);
					}

// graphids
					if (isset($host['graphid']) && is_null($options['selectGraphs'])) {
						if (!isset($result[$host['hostid']]['graphs']))
							$result[$host['hostid']]['graphs'] = array();

						$result[$host['hostid']]['graphs'][] = array('graphid' => $host['graphid']);
						unset($host['graphid']);
					}
// graphids
					if (isset($host['applicationid'])) {
						if (!isset($result[$host['hostid']]['applications']))
							$result[$host['hostid']]['applications'] = array();

						$result[$host['hostid']]['applications'][] = array('applicationid' => $host['applicationid']);
						unset($host['applicationid']);
					}
// httptestids
					if (isset($host['httptestid'])) {
						if (!isset($result[$host['hostid']]['httptests']))
							$result[$host['hostid']]['httptests'] = array();

						$result[$host['hostid']]['httptests'][] = array('httptestid' => $host['httptestid']);
						unset($host['httptestid']);
					}

// dhostids
					if (isset($host['dhostid']) && is_null($options['selectDHosts'])) {
						if (!isset($result[$host['hostid']]['dhosts']))
							$result[$host['hostid']]['dhosts'] = array();

						$result[$host['hostid']]['dhosts'][] = array('dhostid' => $host['dhostid']);
						unset($host['dhostid']);
					}

// dserviceids
					if (isset($host['dserviceid']) && is_null($options['selectDServices'])) {
						if (!isset($result[$host['hostid']]['dservices']))
							$result[$host['hostid']]['dservices'] = array();

						$result[$host['hostid']]['dservices'][] = array('dserviceid' => $host['dserviceid']);
						unset($host['dserviceid']);
					}
// maintenanceids
					if (isset($host['maintenanceid'])) {
						if (!isset($result[$host['hostid']]['maintenances']))
							$result[$host['hostid']]['maintenances'] = array();

						if ($host['maintenanceid'] > 0)
							$result[$host['hostid']]['maintenances'][] = array('maintenanceid' => $host['maintenanceid']);
					}

					$result[$host['hostid']] += $host;
				}
			}
		}

		if (!is_null($options['countOutput'])) {
			return $result;
		}

// Adding Objects
// Adding Groups
		if (!is_null($options['selectGroups']) && str_in_array($options['selectGroups'], $subselectsAllowedOutputs)) {
			$objParams = array(
					'nodeids' => $nodeids,
					'output' => $options['selectGroups'],
					'hostids' => $hostids,
					'preservekeys' => 1
				);
			$groups = API::HostGroup()->get($objParams);

			foreach ($groups as $group) {
				$ghosts = $group['hosts'];
				unset($group['hosts']);
				foreach ($ghosts as $host) {
					$result[$host['hostid']]['groups'][] = $group;
				}
			}
		}

// Adding Inventories
		if (!is_null($options['selectInventory']) && $options['selectInventory'] !== false) {
			if (is_array($options['selectInventory'])) {
				// if we are given a list of fields that needs to be fetched
				$dbTable = DB::getSchema('host_inventory');
				$selectHIn = array('hin.hostid');
				foreach ($options['selectInventory'] as $field) {
					if (isset($dbTable['fields'][$field]))
						$selectHIn[] = 'hin.'.$field;
				}
			}
			else {
				// all fields are needed
				$selectHIn = array('hin.*');
			}

			$sql = 'SELECT '.implode(', ', $selectHIn).
				' FROM host_inventory hin'.
				' WHERE '.DBcondition('hin.hostid', $hostids);
			$dbInventory = DBselect($sql);
			while ($inventory = DBfetch($dbInventory))
				$result[$inventory['hostid']]['inventory'] = $inventory;
		}

// Adding Templates
		if (!is_null($options['selectParentTemplates'])) {
			$objParams = array(
				'nodeids' => $nodeids,
				'hostids' => $hostids,
				'preservekeys' => 1
			);

			if (is_array($options['selectParentTemplates']) || str_in_array($options['selectParentTemplates'], $subselectsAllowedOutputs)) {
				$objParams['output'] = $options['selectParentTemplates'];
				$templates = API::Template()->get($objParams);

				if (!is_null($options['limitSelects'])) {
					order_result($templates, 'host');
				}
				foreach ($templates as $templateid => $template) {
					unset($templates[$templateid]['hosts']);
					$count = array();
					foreach ($template['hosts'] as $host) {
						if (!is_null($options['limitSelects'])) {
							if (!isset($count[$host['hostid']])) {
								$count[$host['hostid']] = 0;
							}
							$count[$host['hostid']]++;

							if ($count[$host['hostid']] > $options['limitSelects']) {
								continue;
							}
						}

						$result[$host['hostid']]['parentTemplates'][] = &$templates[$templateid];
					}
				}
			}
			elseif (API_OUTPUT_COUNT == $options['selectParentTemplates']) {
				$objParams['countOutput'] = 1;
				$objParams['groupCount'] = 1;

				$templates = API::Template()->get($objParams);
				$templates = zbx_toHash($templates, 'hostid');
				foreach ($result as $hostid => $host) {
					if (isset($templates[$hostid]))
						$result[$hostid]['templates'] = $templates[$hostid]['rowscount'];
					else
						$result[$hostid]['templates'] = 0;
				}
			}
		}

// Adding HostInterfaces
		if (!is_null($options['selectInterfaces'])) {
			$objParams = array(
				'nodeids' => $nodeids,
				'hostids' => $hostids,
				'nopermissions' => true,
				'preservekeys' => true
			);
			if (is_array($options['selectInterfaces']) || str_in_array($options['selectInterfaces'], $subselectsAllowedOutputs)) {
				$objParams['output'] = $options['selectInterfaces'];
				$interfaces = API::HostInterface()->get($objParams);

// we need to order interfaces for proper linkage and viewing
//				if (!is_null($options['limitSelects']))
					order_result($interfaces, 'interfaceid', ZBX_SORT_UP);

				$count = array();
				foreach ($interfaces as $interfaceid => $interface) {
					if (!is_null($options['limitSelects'])) {
						if (!isset($count[$interface['hostid']])) {
							$count[$interface['hostid']] = 0;
						}
						$count[$interface['hostid']]++;

						if ($count[$interface['hostid']] > $options['limitSelects']) {
							continue;
						}
					}

					$result[$interface['hostid']]['interfaces'][$interfaceid] = &$interfaces[$interfaceid];
				}
			}
			elseif (API_OUTPUT_COUNT == $options['selectInterfaces']) {
				$objParams['countOutput'] = 1;
				$objParams['groupCount'] = 1;

				$interfaces = API::HostInterface()->get($objParams);
				$interfaces = zbx_toHash($interfaces, 'hostid');
				foreach ($result as $hostid => $host) {
					if (isset($interfaces[$hostid]))
						$result[$hostid]['interfaces'] = $interfaces[$hostid]['rowscount'];
					else
						$result[$hostid]['interfaces'] = 0;
				}
			}
		}

// Adding Items
		if (!is_null($options['selectItems'])) {
			$objParams = array(
				'nodeids' => $nodeids,
				'hostids' => $hostids,
				'filter' => array('flags' => array(ZBX_FLAG_DISCOVERY_NORMAL, ZBX_FLAG_DISCOVERY_CREATED)),
				'nopermissions' => 1,
				'preservekeys' => 1
			);

			if (is_array($options['selectItems']) || str_in_array($options['selectItems'], $subselectsAllowedOutputs)) {
				$objParams['output'] = $options['selectItems'];
				$items = API::Item()->get($objParams);

				if (!is_null($options['limitSelects'])) {
					order_result($items, 'name');
				}
				$count = array();
				foreach ($items as $itemid => $item) {
					if (!is_null($options['limitSelects'])) {
						if (!isset($count[$item['hostid']])) {
							$count[$item['hostid']] = 0;
						}
						$count[$item['hostid']]++;

						if ($count[$item['hostid']] > $options['limitSelects']) {
							continue;
						}
					}

					$result[$item['hostid']]['items'][] = &$items[$itemid];
				}
			}
			elseif (API_OUTPUT_COUNT == $options['selectItems']) {
				$objParams['countOutput'] = 1;
				$objParams['groupCount'] = 1;

				$items = API::Item()->get($objParams);
				$items = zbx_toHash($items, 'hostid');
				foreach ($result as $hostid => $host) {
					if (isset($items[$hostid]))
						$result[$hostid]['items'] = $items[$hostid]['rowscount'];
					else
						$result[$hostid]['items'] = 0;
				}
			}
		}

// Adding Discoveries
		if (!is_null($options['selectDiscoveries'])) {
			$objParams = array(
				'nodeids' => $nodeids,
				'hostids' => $hostids,
				'nopermissions' => 1,
				'preservekeys' => 1,
			);

			if (is_array($options['selectDiscoveries']) || str_in_array($options['selectDiscoveries'], $subselectsAllowedOutputs)) {
				$objParams['output'] = $options['selectDiscoveries'];
				$items = API::DiscoveryRule()->get($objParams);

				if (!is_null($options['limitSelects'])) order_result($items, 'name');

				$count = array();
				foreach ($items as $itemid => $item) {
					unset($items[$itemid]['hosts']);
					foreach ($item['hosts'] as $host) {
						if (!is_null($options['limitSelects'])) {
							if (!isset($count[$host['hostid']])) {
								$count[$host['hostid']] = 0;
							}
							$count[$host['hostid']]++;

							if ($count[$host['hostid']] > $options['limitSelects']) {
								continue;
							}
						}

						$result[$host['hostid']]['discoveries'][] = &$items[$itemid];
					}
				}
			}
			elseif (API_OUTPUT_COUNT == $options['selectDiscoveries']) {
				$objParams['countOutput'] = 1;
				$objParams['groupCount'] = 1;

				$items = API::DiscoveryRule()->get($objParams);
				$items = zbx_toHash($items, 'hostid');
				foreach ($result as $hostid => $host) {
					if (isset($items[$hostid]))
						$result[$hostid]['discoveries'] = $items[$hostid]['rowscount'];
					else
						$result[$hostid]['discoveries'] = 0;
				}
			}
		}

// Adding triggers
		if (!is_null($options['selectTriggers'])) {
			$objParams = array(
				'nodeids' => $nodeids,
				'hostids' => $hostids,
				'nopermissions' => 1,
				'preservekeys' => 1
			);

			if (is_array($options['selectTriggers']) || str_in_array($options['selectTriggers'], $subselectsAllowedOutputs)) {
				$objParams['output'] = $options['selectTriggers'];
				$triggers = API::Trigger()->get($objParams);

				if (!is_null($options['limitSelects'])) order_result($triggers, 'description');

				$count = array();
				foreach ($triggers as $triggerid => $trigger) {
					unset($triggers[$triggerid]['hosts']);

					foreach ($trigger['hosts'] as $host) {
						if (!is_null($options['limitSelects'])) {
							if (!isset($count[$host['hostid']])) {
								$count[$host['hostid']] = 0;
							}
							$count[$host['hostid']]++;

							if ($count[$host['hostid']] > $options['limitSelects']) {
								continue;
							}
						}

						$result[$host['hostid']]['triggers'][] = &$triggers[$triggerid];
					}
				}
			}
			elseif (API_OUTPUT_COUNT == $options['selectTriggers']) {
				$objParams['countOutput'] = 1;
				$objParams['groupCount'] = 1;

				$triggers = API::Trigger()->get($objParams);
				$triggers = zbx_toHash($triggers, 'hostid');
				foreach ($result as $hostid => $host) {
					if (isset($triggers[$hostid]))
						$result[$hostid]['triggers'] = $triggers[$hostid]['rowscount'];
					else
						$result[$hostid]['triggers'] = 0;
				}
			}
		}

// Adding graphs
		if (!is_null($options['selectGraphs'])) {
			$objParams = array(
				'nodeids' => $nodeids,
				'hostids' => $hostids,
				'nopermissions' => 1,
				'preservekeys' => 1
			);

			if (is_array($options['selectGraphs']) || str_in_array($options['selectGraphs'], $subselectsAllowedOutputs)) {
				$objParams['output'] = $options['selectGraphs'];
				$graphs = API::Graph()->get($objParams);

				if (!is_null($options['limitSelects'])) {
					order_result($graphs, 'name');
				}

				$count = array();
				foreach ($graphs as $graphid => $graph) {
					unset($graphs[$graphid]['hosts']);

					foreach ($graph['hosts'] as $host) {
						if (!is_null($options['limitSelects'])) {
							if (!isset($count[$host['hostid']])) {
								$count[$host['hostid']] = 0;
							}
							$count[$host['hostid']]++;

							if ($count[$host['hostid']] > $options['limitSelects']) {
								continue;
							}
						}

						$result[$host['hostid']]['graphs'][] = &$graphs[$graphid];
					}
				}
			}
			elseif (API_OUTPUT_COUNT == $options['selectGraphs']) {
				$objParams['countOutput'] = 1;
				$objParams['groupCount'] = 1;

				$graphs = API::Graph()->get($objParams);
				$graphs = zbx_toHash($graphs, 'hostid');
				foreach ($result as $hostid => $host) {
					if (isset($graphs[$hostid]))
						$result[$hostid]['graphs'] = $graphs[$hostid]['rowscount'];
					else
						$result[$hostid]['graphs'] = 0;
				}
			}
		}

// Adding discovery hosts
		if (!is_null($options['selectDHosts'])) {
			$objParams = array(
				'nodeids' => $nodeids,
				'hostids' => $hostids,
				'nopermissions' => 1,
				'preservekeys' => 1
			);

			if (is_array($options['selectDHosts']) || str_in_array($options['selectDHosts'], $subselectsAllowedOutputs)) {
				$objParams['output'] = $options['selectDHosts'];
				$dhosts = API::DHost()->get($objParams);

				if (!is_null($options['limitSelects'])) {
					order_result($dhosts, 'dhostid');
				}

				$count = array();
				foreach ($dhosts as $dhostid => $dhost) {
					unset($dhosts[$dhostid]['hosts']);

					foreach ($dhost['hosts'] as $host) {
						if (!is_null($options['limitSelects'])) {
							if (!isset($count[$host['hostid']])) {
								$count[$host['hostid']] = 0;
							}
							$count[$host['hostid']]++;

							if ($count[$host['hostid']] > $options['limitSelects']) {
								continue;
							}
						}

						$result[$host['hostid']]['dhosts'][] = &$dhosts[$dhostid];
					}
				}
			}
			elseif (API_OUTPUT_COUNT == $options['selectDHosts']) {
				$objParams['countOutput'] = 1;
				$objParams['groupCount'] = 1;

				$dhosts = API::DHost()->get($objParams);
				$dhosts = zbx_toHash($dhosts, 'hostid');
				foreach ($result as $hostid => $host) {
					if (isset($dhosts[$hostid]))
						$result[$hostid]['dhosts'] = $dhosts[$hostid]['rowscount'];
					else
						$result[$hostid]['dhosts'] = 0;
				}
			}
		}

// Adding applications
		if (!is_null($options['selectApplications'])) {
			$objParams = array(
				'nodeids' => $nodeids,
				'hostids' => $hostids,
				'nopermissions' => 1,
				'preservekeys' => 1
			);

			if (is_array($options['selectApplications']) || str_in_array($options['selectApplications'], $subselectsAllowedOutputs)) {
				$objParams['output'] = $options['selectApplications'];
				$applications = API::Application()->get($objParams);

				if (!is_null($options['limitSelects'])) {
					order_result($applications, 'name');
				}

				$count = array();
				foreach ($applications as $applicationid => $application) {
					unset($applications[$applicationid]['hosts']);

					foreach ($application['hosts'] as $host) {
						if (!is_null($options['limitSelects'])) {
							if (!isset($count[$host['hostid']])) {
								$count[$host['hostid']] = 0;
							}
							$count[$host['hostid']]++;

							if ($count[$host['hostid']] > $options['limitSelects']) {
								continue;
							}
						}

						$result[$host['hostid']]['applications'][] = &$applications[$applicationid];
					}
				}
			}
			elseif (API_OUTPUT_COUNT == $options['selectApplications']) {
				$objParams['countOutput'] = 1;
				$objParams['groupCount'] = 1;

				$applications = API::Application()->get($objParams);

				$applications = zbx_toHash($applications, 'hostid');
				foreach ($result as $hostid => $host) {
					if (isset($applications[$hostid]))
						$result[$hostid]['applications'] = $applications[$hostid]['rowscount'];
					else
						$result[$hostid]['applications'] = 0;
				}
			}
		}

// Adding macros
		if (!is_null($options['selectMacros']) && str_in_array($options['selectMacros'], $subselectsAllowedOutputs)) {
			$objParams = array(
				'nodeids' => $nodeids,
				'output' => $options['selectMacros'],
				'hostids' => $hostids,
				'preservekeys' => 1
			);
			$macros = API::UserMacro()->get($objParams);

			foreach ($macros as $macroid => $macro) {
				$mhosts = $macro['hosts'];
				unset($macro['hosts']);
				foreach ($mhosts as $host) {
					$result[$host['hostid']]['macros'][$macroid] = $macro;
				}
			}
		}

// Adding screens
		if (!is_null($options['selectScreens'])) {
			$objParams = array(
				'nodeids' => $nodeids,
				'hostids' => $hostids,
				'editable' => $options['editable'],
				'nopermissions' => 1,
				'preservekeys' => 1
			);

			if (is_array($options['selectScreens']) || str_in_array($options['selectScreens'], $subselectsAllowedOutputs)) {
				$objParams['output'] = $options['selectScreens'];

				$screens = API::TemplateScreen()->get($objParams);
				if (!is_null($options['limitSelects'])) order_result($screens, 'name');

				foreach ($screens as $snum => $screen) {
					if (!is_null($options['limitSelects'])) {
						if (count($result[$screen['hostid']]['screens']) >= $options['limitSelects']) continue;
					}

					unset($screens[$snum]['hosts']);
					$result[$screen['hostid']]['screens'][] = &$screens[$snum];
				}
			}
			elseif (API_OUTPUT_COUNT == $options['selectScreens']) {
				$objParams['countOutput'] = 1;
				$objParams['groupCount'] = 1;

				$screens = API::TemplateScreen()->get($objParams);
				$screens = zbx_toHash($screens, 'hostid');

				foreach ($result as $hostid => $host) {
					if (isset($screens[$hostid]))
						$result[$hostid]['screens'] = $screens[$hostid]['rowscount'];
					else
						$result[$hostid]['screens'] = 0;
				}
			}
		}


// removing keys (hash -> array)
		if (is_null($options['preservekeys'])) {
			$result = zbx_cleanHashes($result);
		}

		return $result;
	}

	/**
	 * Get Host ID by Host name
	 *
	 * @param array $host_data
	 * @param string $host_data['host']
	 *
	 * @return int|boolean
	 */
	public function getObjects($hostData) {
		$options = array(
			'filter' => $hostData,
			'output' => API_OUTPUT_EXTEND
		);

		if (isset($hostData['node'])) {
			$options['nodeids'] = getNodeIdByNodeName($hostData['node']);
		}
		elseif (isset($hostData['nodeids'])) {
			$options['nodeids'] = $hostData['nodeids'];
		}

		$result = $this->get($options);

		return $result;
	}

	public function exists($object) {
		$keyFields = array(
			array(
				'hostid',
				'host',
				'name'
			)
		);

		$options = array(
			'filter' => zbx_array_mintersect($keyFields, $object),
			'output' => API_OUTPUT_SHORTEN,
			'nopermissions' => 1,
			'limit' => 1
		);

		if (isset($object['node'])) {
			$options['nodeids'] = getNodeIdByNodeName($object['node']);
		}
		elseif (isset($object['nodeids'])) {
			$options['nodeids'] = $object['nodeids'];
		}

		$objs = $this->get($options);

		return !empty($objs);
	}

	protected function checkInput(&$hosts, $method) {
		$create = ($method == 'create');
		$update = ($method == 'update');
		$delete = ($method == 'delete');

// permissions
		$groupids = array();
		foreach ($hosts as $host) {
			if (!isset($host['groups'])) {
				continue;
			}
			$groupids = array_merge($groupids, zbx_objectValues($host['groups'], 'groupid'));
		}

		if ($update || $delete) {
			$hostDBfields = array('hostid' => null);
			$dbHosts = $this->get(array(
				'output' => array(
					'hostid',
					'host'
				),
				'hostids' => zbx_objectValues($hosts, 'hostid'),
				'editable' => 1,
				'preservekeys' => 1
			));
		}
		else {
			$hostDBfields = array('host' => null);
		}

		if (!empty($groupids)) {
			$dbGroups = API::HostGroup()->get(array(
				'output' => API_OUTPUT_EXTEND,
				'groupids' => $groupids,
				'editable' => 1,
				'preservekeys' => 1
			));

		}

		$inventoryFields = getHostInventories();
		$inventoryFields = zbx_objectValues($inventoryFields, 'db_field');

		$hostNames = array();
		foreach ($hosts as &$host) {
			if (!check_db_fields($hostDBfields, $host)) {
				self::exception(ZBX_API_ERROR_PARAMETERS,
					_s('Wrong fields for host "%s".', isset($host['host']) ? $host['host'] : ''));
			}

			if (isset($host['inventory']) && !empty($host['inventory'])) {
				$fields = array_keys($host['inventory']);
				foreach ($fields as $field) {
					if (!in_array($field, $inventoryFields)) {
						self::exception(ZBX_API_ERROR_PARAMETERS, _s('Incorrect inventory field "%s".', $field));
					}
				}
			}

			if ($update || $delete) {
				if (!isset($dbHosts[$host['hostid']])) {
					self::exception(ZBX_API_ERROR_PARAMETERS, _('No permissions to referred object or it does not exist!'));
				}
				if ($delete) {
					$host['host'] = $dbHosts[$host['hostid']]['host'];
				}
			}
			else {
				// if visible name is not given or empty it should be set to host name
				if (!isset($host['name']) || zbx_empty(trim($host['name']))) {
					$host['name'] = $host['host'];
				}

				if (!isset($host['groups'])) {
					self::exception(ZBX_API_ERROR_PARAMETERS, _s('No groups for host "%s".', $host['host']));
				}

				if (!isset($host['interfaces'])) {
					self::exception(ZBX_API_ERROR_PARAMETERS, _s('No interfaces for host "%s".', $host['host']));
				}
			}

			if ($delete) {
				continue;
			}

			if (isset($host['groups'])) {
				if (!is_array($host['groups']) || empty($host['groups'])) {
					self::exception(ZBX_API_ERROR_PARAMETERS, _s('No groups for host "%s".', $host['host']));
				}

				foreach ($host['groups'] as $group) {
					if (!isset($dbGroups[$group['groupid']])) {
						self::exception(ZBX_API_ERROR_PERMISSIONS, _('No permissions to referred object or it does not exist!'));
					}
				}
			}

			if (isset($host['interfaces'])) {
				if (!is_array($host['interfaces']) || empty($host['interfaces'])) {
					self::exception(ZBX_API_ERROR_PARAMETERS, _s('No interfaces for host "%s".', $host['host']));
				}
			}

			if (isset($host['host'])) {
				// Check if host name isn't longer than 64 chars
				if (zbx_strlen($host['host']) > 64) {
					self::exception(
						ZBX_API_ERROR_PARAMETERS,
						_n(
							'Maximum host name length is %2$d characters, "%3$s" is %1$d character.',
							'Maximum host name length is %2$d characters, "%3$s" is %1$d characters.',
							zbx_strlen($host['host']),
							64,
							$host['host']
						)
					);
				}

				if (!preg_match('/^'.ZBX_PREG_HOST_FORMAT.'$/', $host['host'])) {
					self::exception(ZBX_API_ERROR_PARAMETERS, _s('Incorrect characters used for host name "%s".', $host['host']));
				}

				if (isset($hostNames['host'][$host['host']])) {
					self::exception(ZBX_API_ERROR_PARAMETERS, _s('Duplicate host. Host with the same host name "%s" already exists in data.', $host['host']));
				}

				$hostNames['host'][$host['host']] = $update ? $host['hostid'] : 1;
			}

			if (isset($host['name'])) {
				if ($update) {
					// if visible name is empty replace it with host name
					if (zbx_empty(trim($host['name']))) {
						if (!isset($host['host'])) {
							self::exception(ZBX_API_ERROR_PARAMETERS, _s('Visible name cannot be empty if host name is missing.'));
						}
						$host['name'] = $host['host'];
					}
				}

				// Check if visible name isn't longer than 64 chars
				if (zbx_strlen($host['name']) > 64) {
					self::exception(
						ZBX_API_ERROR_PARAMETERS,
						_n(
							'Maximum visible host name length is %2$d characters, "%3$s" is %1$d character.',
							'Maximum visible host name length is %2$d characters, "%3$s" is %1$d characters.',
							zbx_strlen($host['name']),
							64,
							$host['name']
						)
					);
				}

				if (isset($hostNames['name'][$host['name']])) {
					self::exception(ZBX_API_ERROR_PARAMETERS, _s('Duplicate host. Host with the same visible name "%s" already exists in data.', $host['name']));
				}
				$hostNames['name'][$host['name']] = $update ? $host['hostid'] : 1;
			}
		}
		unset($host);

		if ($update || $create) {
			if (isset($hostNames['host']) || isset($hostNames['name'])) {
				$filter = array();
				if (isset($hostNames['host'])) {
					$filter['host'] = array_keys($hostNames['host']);
				}
				if (isset($hostNames['name'])) {
					$filter['name'] = array_keys($hostNames['name']);
				}

				$options = array(
					'output' => array(
						'hostid',
						'host',
						'name'
					),
					'filter' => $filter,
					'searchByAny' => true,
					'nopermissions' => true,
					'preservekeys' => true
				);

				$hostsExists = $this->get($options);

				foreach ($hostsExists as $hostExists) {
					if (isset($hostNames['host'][$hostExists['host']])) {
						if (!$update || bccomp($hostExists['hostid'], $hostNames['host'][$hostExists['host']]) != 0) {
							self::exception(ZBX_API_ERROR_PARAMETERS, _s('Host with the same name "%s" already exists.', $hostExists['host']));
						}
					}

					if (isset($hostNames['name'][$hostExists['name']])) {
						if (!$update || bccomp($hostExists['hostid'], $hostNames['name'][$hostExists['name']]) != 0) {
							self::exception(ZBX_API_ERROR_PARAMETERS, _s('Host with the same visible name "%s" already exists.', $hostExists['name']));
						}
					}
				}

				$templatesExists = API::Template()->get($options);

				foreach ($templatesExists as $templateExists) {
					if (isset($hostNames['host'][$templateExists['host']])) {
						if (!$update || bccomp($templateExists['templateid'], $hostNames['host'][$templateExists['host']]) != 0) {
							self::exception(ZBX_API_ERROR_PARAMETERS, _s('Template with the same name "%s" already exists.', $templateExists['host']));
						}
					}

					if (isset($hostNames['name'][$templateExists['name']])) {
						if (!$update || bccomp($templateExists['templateid'], $hostNames['name'][$templateExists['name']]) != 0) {
							self::exception(ZBX_API_ERROR_PARAMETERS, _s('Template with the same visible name "%s" already exists.', $templateExists['name']));
						}
					}
				}
			}
		}

		return $update ? $dbHosts : $hosts;
	}

	/**
	 * Add Host
	 *
	 * @param array  $hosts multidimensional array with Hosts data
	 * @param string $hosts ['host'] Host name.
	 * @param array  $hosts ['groups'] array of HostGroup objects with IDs add Host to.
	 * @param int    $hosts ['port'] Port. OPTIONAL
	 * @param int    $hosts ['status'] Host Status. OPTIONAL
	 * @param int    $hosts ['useip'] Use IP. OPTIONAL
	 * @param string $hosts ['dns'] DNS. OPTIONAL
	 * @param string $hosts ['ip'] IP. OPTIONAL
	 * @param int    $hosts ['proxy_hostid'] Proxy Host ID. OPTIONAL
	 * @param int    $hosts ['ipmi_authtype'] IPMI authentication type. OPTIONAL
	 * @param int    $hosts ['ipmi_privilege'] IPMI privilege. OPTIONAL
	 * @param string $hosts ['ipmi_username'] IPMI username. OPTIONAL
	 * @param string $hosts ['ipmi_password'] IPMI password. OPTIONAL
	 *
	 * @return boolean
	 */
	public function create($hosts) {
		$hosts = zbx_toArray($hosts);
		$hostids = array();

		$this->checkInput($hosts, __FUNCTION__);

		foreach ($hosts as $host) {
			$hostid = DB::insert('hosts', array($host));
			$hostids[] = $hostid = reset($hostid);

			$host['hostid'] = $hostid;

			// save groups
			// groups must be added before calling massAdd() for permission validation to work
			$groupsToAdd = array();
			foreach ($host['groups'] as $group) {
				$groupsToAdd[] = array(
					'hostid' => $hostid,
					'groupid' => $group['groupid']
				);
			}
			DB::insert('hosts_groups', $groupsToAdd);

			$options = array();
			$options['hosts'] = $host;

			if (isset($host['templates']) && !is_null($host['templates'])) {
				$options['templates'] = $host['templates'];
			}

			if (isset($host['macros']) && !is_null($host['macros'])) {
				$options['macros'] = $host['macros'];
			}

			if (isset($host['interfaces']) && !is_null($host['interfaces'])) {
				$options['interfaces'] = $host['interfaces'];
			}

			$result = API::Host()->massAdd($options);
			if (!$result) {
				self::exception();
			}

			if (isset($host['inventory']) && !empty($host['inventory'])) {
				$fields = array_keys($host['inventory']);
				$fields[] = 'inventory_mode';
				$fields = implode(', ', $fields);

				$values = array_map('zbx_dbstr', $host['inventory']);
				$values[] = isset($host['inventory_mode']) ? $host['inventory_mode'] : HOST_INVENTORY_MANUAL;
				$values = implode(', ', $values);

				DBexecute('INSERT INTO host_inventory (hostid, '.$fields.') VALUES ('.$hostid.', '.$values.')');
			}
		}

		return array('hostids' => $hostids);
	}

	/**
	 * Update Host.
	 *
	 * @param array  $hosts multidimensional array with Hosts data
	 * @param string $hosts ['host'] Host name.
	 * @param int    $hosts ['port'] Port. OPTIONAL
	 * @param int    $hosts ['status'] Host Status. OPTIONAL
	 * @param int    $hosts ['useip'] Use IP. OPTIONAL
	 * @param string $hosts ['dns'] DNS. OPTIONAL
	 * @param string $hosts ['ip'] IP. OPTIONAL
	 * @param int    $hosts ['proxy_hostid'] Proxy Host ID. OPTIONAL
	 * @param int    $hosts ['ipmi_authtype'] IPMI authentication type. OPTIONAL
	 * @param int    $hosts ['ipmi_privilege'] IPMI privilege. OPTIONAL
	 * @param string $hosts ['ipmi_username'] IPMI username. OPTIONAL
	 * @param string $hosts ['ipmi_password'] IPMI password. OPTIONAL
	 * @param string $hosts ['groups'] groups
	 *
	 * @return boolean
	 */
	public function update($hosts) {
		$hosts = zbx_toArray($hosts);
		$hostids = zbx_objectValues($hosts, 'hostid');

		$this->checkInput($hosts, __FUNCTION__);

		$macros = array();
		foreach ($hosts as $host) {
			API::HostInterface()->replaceHostInterfaces($host);
			unset($host['interfaces']);

			if (isset($host['macros'])) {
				$macros[$host['hostid']] = $host['macros'];
				unset($host['macros']);
			}

			$data = $host;
			$data['hosts'] = $host;
			$result = $this->massUpdate($data);

			if (!$result) {
				self::exception(ZBX_API_ERROR_INTERNAL, _('Host update failed.'));
			}
		}

		if ($macros) {
			API::UserMacro()->replaceMacros($macros);
		}

		return array('hostids' => $hostids);
	}

	/**
	 * Add Hosts to HostGroups. All Hosts are added to all HostGroups.
	 *
	 * @param array $data
	 * @param array $data['groups']
	 * @param array $data['templates']
	 * @param array $data['macros']
	 *
	 * @return array
	 */
	public function massAdd($data) {
		$data['hosts'] = zbx_toArray($data['hosts']);

		$options = array(
			'hostids' => zbx_objectValues($data['hosts'], 'hostid'),
			'editable' => 1,
			'preservekeys' => 1
		);
		$updHosts = $this->get($options);

		foreach ($data['hosts'] as $host) {
			if (!isset($updHosts[$host['hostid']])) {
				self::exception(ZBX_API_ERROR_PERMISSIONS, _('You do not have enough rights for operation.'));
			}
		}

		if (isset($data['interfaces']) && !empty($data['interfaces'])) {
			$data['interfaces'] = zbx_toArray($data['interfaces']);

			$options = array(
				'hosts' => &$data['hosts'],
				'interfaces' => &$data['interfaces']
			);

			$result = API::HostInterface()->massAdd($options);
			if (!$result) {
				self::exception();
			}
		}

		if (isset($data['groups']) && !empty($data['groups'])) {
			$data['groups'] = zbx_toArray($data['groups']);

			$options = array(
				'hosts' => &$data['hosts'],
				'groups' => &$data['groups']
			);
			$result = API::HostGroup()->massAdd($options);
			if (!$result) {
				self::exception();
			}
		}

		if (isset($data['templates']) && !empty($data['templates'])) {
			$data['templates'] = zbx_toArray($data['templates']);

			$options = array(
				'hosts' => &$data['hosts'],
				'templates' => &$data['templates']
			);
			$result = API::Template()->massAdd($options);
			if (!$result) {
				self::exception();
			}
		}

		if (isset($data['macros']) && !empty($data['macros'])) {
			$data['macros'] = zbx_toArray($data['macros']);

			// add the macros to all hosts
			$hostMacrosToAdd = array();
			foreach ($data['macros'] as $hostMacro) {
				foreach ($data['hosts'] as $host) {
					$hostMacro['hostid'] = $host['hostid'];
					$hostMacrosToAdd[] = $hostMacro;
				}
			}

			$result = API::UserMacro()->create($hostMacrosToAdd);
			if (!$result) {
				self::exception();
			}
		}

		return array('hostids' => zbx_objectValues($data['hosts'], 'hostid'));
	}

	/**
	 * Mass update hosts
	 *
	 * @param array $hosts multidimensional array with Hosts data
	 * @param array  $hosts ['hosts'] Array of Host objects to update
	 * @param string $hosts ['fields']['host'] Host name.
	 * @param array  $hosts ['fields']['groupids'] HostGroup IDs add Host to.
	 * @param int    $hosts ['fields']['port'] Port. OPTIONAL
	 * @param int    $hosts ['fields']['status'] Host Status. OPTIONAL
	 * @param int    $hosts ['fields']['useip'] Use IP. OPTIONAL
	 * @param string $hosts ['fields']['dns'] DNS. OPTIONAL
	 * @param string $hosts ['fields']['ip'] IP. OPTIONAL
	 * @param int    $hosts ['fields']['proxy_hostid'] Proxy Host ID. OPTIONAL
	 * @param int    $hosts ['fields']['ipmi_authtype'] IPMI authentication type. OPTIONAL
	 * @param int    $hosts ['fields']['ipmi_privilege'] IPMI privilege. OPTIONAL
	 * @param string $hosts ['fields']['ipmi_username'] IPMI username. OPTIONAL
	 * @param string $hosts ['fields']['ipmi_password'] IPMI password. OPTIONAL
	 *
	 * @return boolean
	 */
	public function massUpdate($data) {
		$hosts = zbx_toArray($data['hosts']);
		$hostids = zbx_objectValues($hosts, 'hostid');

		$options = array(
			'hostids' => $hostids,
			'editable' => true,
			'output' => API_OUTPUT_EXTEND,
			'preservekeys' => true,
		);
		$updHosts = $this->get($options);
		foreach ($hosts as $host) {
			if (!isset($updHosts[$host['hostid']])) {
				self::exception(ZBX_API_ERROR_PERMISSIONS, _('You do not have permission to perform this operation.'));
			}
		}

// CHECK IF HOSTS HAVE AT LEAST 1 GROUP {{{
		if (isset($data['groups']) && empty($data['groups'])) {
			self::exception(ZBX_API_ERROR_PARAMETERS, _('No groups for hosts.'));
		}
// }}} CHECK IF HOSTS HAVE AT LEAST 1 GROUP


// UPDATE HOSTS PROPERTIES {{{
		if (isset($data['name'])) {
			if (count($hosts) > 1) {
				self::exception(ZBX_API_ERROR_PARAMETERS, _('Cannot mass update visible host name.'));
			}
		}

		if (isset($data['host'])) {
			if (!preg_match('/^'.ZBX_PREG_HOST_FORMAT.'$/', $data['host'])) {
				self::exception(ZBX_API_ERROR_PARAMETERS, _s('Incorrect characters used for host name "%s".', $data['host']));
			}

			if (count($hosts) > 1) {
				self::exception(ZBX_API_ERROR_PARAMETERS, _('Cannot mass update host name.'));
			}

			$curHost = reset($hosts);

			$options = array(
				'filter' => array(
					'host' => $curHost['host']
				),
				'output' => API_OUTPUT_SHORTEN,
				'editable' => 1,
				'nopermissions' => 1
			);
			$hostExists = $this->get($options);
			$hostExist = reset($hostExists);
			if ($hostExist && (bccomp($hostExist['hostid'], $curHost['hostid']) != 0)) {
				self::exception(ZBX_API_ERROR_PARAMETERS, _s('Host "%1$s" already exists.', $data['host']));
			}

			// can't add host with the same name as existing template
			if (API::Template()->exists(array('host' => $curHost['host']))) {
				self::exception(ZBX_API_ERROR_PARAMETERS, _s('Template "%1$s" already exists.', $curHost['host']));
			}
		}


		if (isset($data['groups'])) {
			$updateGroups = $data['groups'];
			unset($data['groups']);
		}

		if (isset($data['interfaces'])) {
			$updateInterfaces = $data['interfaces'];
			unset($data['interfaces']);
		}

		if (isset($data['templates_clear'])) {
			$updateTemplatesClear = zbx_toArray($data['templates_clear']);
			unset($data['templates_clear']);
		}

		if (isset($data['templates'])) {
			$updateTemplates = $data['templates'];
			unset($data['templates']);
		}

		if (isset($data['macros'])) {
			$updateMacros = $data['macros'];
			unset($data['macros']);
		}

		if (!empty($data['inventory'])) {
			$updateInventory = $data['inventory'];
			$updateInventory['inventory_mode'] = $data['inventory_mode'];
			unset($data['inventory']);
			unset($data['inventory_mode']);
		}

		if (isset($data['status'])) {
			$updateStatus = $data['status'];
			unset($data['status']);
		}

		unset($data['hosts']);
		if (!zbx_empty($data)) {
			$update = array(
				'values' => $data,
				'where' => array('hostid' => $hostids)
			);
			DB::update('hosts', $update);
		}

		if (isset($updateStatus)) {
			updateHostStatus($hostids, $updateStatus);
		}

// }}} UPDATE HOSTS PROPERTIES

// UPDATE HOSTGROUPS LINKAGE {{{
		if (isset($updateGroups)) {
			$updateGroups = zbx_toArray($updateGroups);

			$hostGroups = API::HostGroup()->get(array('hostids' => $hostids));
			$hostGroupids = zbx_objectValues($hostGroups, 'groupid');
			$newGroupids = zbx_objectValues($updateGroups, 'groupid');

			$result = $this->massAdd(array(
				'hosts' => $hosts,
				'groups' => $updateGroups
			));
			if (!$result) {
				self::exception(ZBX_API_ERROR_PARAMETERS, _('Cannot create host group.'));
			}

			$groupidsToDel = array_diff($hostGroupids, $newGroupids);

			if (!empty($groupidsToDel)) {
				$result = $this->massRemove(array(
					'hostids' => $hostids,
					'groupids' => $groupidsToDel
				));
				if (!$result) {
					self::exception(ZBX_API_ERROR_PARAMETERS, _('Cannot delete host group.'));
				}
			}
		}
// }}} UPDATE HOSTGROUPS LINKAGE


// UPDATE INTERFACES {{{
		if (isset($updateInterfaces)) {
			$hostInterfaces = API::HostInterface()->get(array(
				'hostids' => $hostids,
				'output' => API_OUTPUT_EXTEND,
				'preservekeys' => true,
				'nopermissions' => 1
			));

			$this->massRemove(array(
				'hosts' => $hosts,
				'interfaces' => $hostInterfaces
			));
			$this->massAdd(array(
				'hosts' => $hosts,
				'interfaces' => $updateInterfaces
			));
		}
// }}} UPDATE INTERFACES


		if (isset($updateTemplatesClear)) {
			$templateidsClear = zbx_objectValues($updateTemplatesClear, 'templateid');
			if (!empty($updateTemplatesClear)) {
				$this->massRemove(
					array(
						'hostids' => $hostids,
						'templateids_clear' => $templateidsClear
					)
				);
			}
		}
		else {
			$templateidsClear = array();
		}


// UPDATE TEMPLATE LINKAGE {{{
		if (isset($updateTemplates)) {
			$opt = array(
				'hostids' => $hostids,
				'output' => API_OUTPUT_SHORTEN,
				'preservekeys' => true
			);
			$hostTemplates = API::Template()->get($opt);

			$hostTemplateids = array_keys($hostTemplates);
			$newTemplateids = zbx_objectValues($updateTemplates, 'templateid');

			$templatesToDel = array_diff($hostTemplateids, $newTemplateids);
			$templatesToDel = array_diff($templatesToDel, $templateidsClear);

			if (!empty($templatesToDel)) {
				$result = $this->massRemove(array(
					'hostids' => $hostids,
					'templateids' => $templatesToDel
				));
				if (!$result) {
					self::exception(ZBX_API_ERROR_PARAMETERS, _('Cannot unlink template'));
				}
			}

			$result = $this->massAdd(array(
				'hosts' => $hosts,
				'templates' => $updateTemplates
			));
			if (!$result) {
				self::exception(ZBX_API_ERROR_PARAMETERS, _('Cannot link template'));
			}
		}
// }}} UPDATE TEMPLATE LINKAGE


		//  macros
		if (isset($updateMacros)) {
			DB::delete('hostmacro', array('hostid' => $hostids));

			$this->massAdd(array(
				'hosts' => $hosts,
				'macros' => $updateMacros
			));
		}

		if (isset($updateInventory)) {
			if ($updateInventory['inventory_mode'] == HOST_INVENTORY_DISABLED) {
				$sql = 'DELETE FROM host_inventory WHERE '.DBcondition('hostid', $hostids);
				if (!DBexecute($sql)) {
					self::exception(ZBX_API_ERROR_PARAMETERS, _('Cannot delete inventory.'));
				}
			}

			else {
				$hostsWithInventories = array();
				$existingInventoriesDb = DBselect('SELECT hostid FROM host_inventory WHERE '.DBcondition('hostid', $hostids));
				while ($existingInventory = DBfetch($existingInventoriesDb)) {
					$hostsWithInventories[] = $existingInventory['hostid'];
				}

				// when hosts are being updated to use automatic mode for host inventories,
				// we must check if some items are set to populate inventory fields of every host.
				// if they do, mass update for those fields should be ignored
				if ($updateInventory['inventory_mode'] == HOST_INVENTORY_AUTOMATIC) {
					// getting all items on all affected hosts
					$options = array(
						'output' => array(
							'inventory_link',
							'hostid'
						),
						'filter' => array('hostid' => $hostids),
						'nopermissions' => true
					);
					$itemsToInventories = API::item()->get($options);

					// gathering links to array: 'hostid'=>array('inventory_name_1'=>true, 'inventory_name_2'=>true)
					$inventoryLinksOnHosts = array();
					$inventoryFields = getHostInventories();
					foreach ($itemsToInventories as $hinv) {
						if ($hinv['inventory_link'] != 0) { // 0 means 'no link'
							if (isset($inventoryLinksOnHosts[$hinv['hostid']])) {
								$inventoryLinksOnHosts[$hinv['hostid']][$inventoryFields[$hinv['inventory_link']]['db_field']] = true;
							}
							else {
								$inventoryLinksOnHosts[$hinv['hostid']] = array($inventoryFields[$hinv['inventory_link']]['db_field'] => true);
							}
						}
					}

					// now we have all info we need to determine, which inventory fields should be saved
					$inventoriesToSave = array();
					foreach ($hostids as $hostid) {
						$inventoriesToSave[$hostid] = $updateInventory;
						$inventoriesToSave[$hostid]['hostid'] = $hostid;
						foreach ($updateInventory as $inventoryName => $hinv) {
							if (isset($inventoryLinksOnHosts[$hostid][$inventoryName])) {
								unset($inventoriesToSave[$hostid][$inventoryName]);
							}
						}
					}
				}
				else {
					// if mode is not automatic, all fields can be saved
					$inventoriesToSave = array();
					foreach ($hostids as $hostid) {
						$inventoriesToSave[$hostid] = $updateInventory;
						$inventoriesToSave[$hostid]['hostid'] = $hostid;
					}
				}

				$hostsWithoutInventory = array_diff($hostids, $hostsWithInventories);

				// hosts that have no inventory yet, need it to be inserted
				foreach ($hostsWithoutInventory as $hostid) {
					DB::insert('host_inventory', array($inventoriesToSave[$hostid]), false);
				}

				// those hosts that already have an inventory, need it to be updated
				foreach ($hostsWithInventories as $hostid) {
					DB::update('host_inventory', array(
						'values' => $inventoriesToSave[$hostid],
						'where' => array('hostid' => $hostid)
					));
				}
			}
		}
// }}} INVENTORY
		return array('hostids' => $hostids);
	}

	/**
	 * remove Hosts from HostGroups. All Hosts are removed from all HostGroups.
	 *
	 * @param array $data
	 * @param array $data['hostids']
	 * @param array $data['groupids']
	 * @param array $data['templateids']
	 * @param array $data['macroids']
	 *
	 * @return array
	 */
	public function massRemove($data) {
		$hostids = zbx_toArray($data['hostids']);

		$options = array(
			'hostids' => $hostids,
			'editable' => 1,
			'preservekeys' => 1,
			'output' => API_OUTPUT_SHORTEN,
		);
		$updHosts = $this->get($options);
		foreach ($hostids as $hostid) {
			if (!isset($updHosts[$hostid])) {
				self::exception(ZBX_API_ERROR_PERMISSIONS, _('You do not have permission to perform this operation.'));
			}
		}

		if (isset($data['groupids'])) {
			$options = array(
				'hostids' => $hostids,
				'groupids' => zbx_toArray($data['groupids'])
			);
			$result = API::HostGroup()->massRemove($options);
			if (!$result) {
				self::exception();
			}
		}

		if (isset($data['templateids'])) {
			$options = array(
				'hostids' => $hostids,
				'templateids' => zbx_toArray($data['templateids'])
			);
			$result = API::Template()->massRemove($options);
			if (!$result) {
				self::exception();
			}
		}

		if (isset($data['templateids_clear'])) {
			$options = array(
				'templateids' => $hostids,
				'templateids_clear' => zbx_toArray($data['templateids_clear'])
			);
			$result = API::Template()->massRemove($options);
			if (!$result) {
				self::exception();
			}
		}

		if (isset($data['macros'])) {
			$result = API::UserMacro()->delete(zbx_toArray($data['macros']));
			if (!$result) {
				self::exception();
			}
		}

		if (isset($data['interfaces'])) {
			$options = array(
				'hostids' => $hostids,
				'interfaces' => zbx_toArray($data['interfaces'])
			);
			$result = API::HostInterface()->massRemove($options);
			if (!$result) {
				self::exception();
			}
		}

		return array('hostids' => $hostids);
	}

	/**
	 * Delete Host
	 *
	 * @param array $hosts
	 * @param array $hosts[0, ...]['hostid'] Host ID to delete
	 *
	 * @return array|boolean
	 */
	public function delete($hosts) {

		if (empty($hosts)) {
			self::exception(ZBX_API_ERROR_PARAMETERS, _('Empty input parameter.'));
		}

		$hosts = zbx_toArray($hosts);
		$hostids = zbx_objectValues($hosts, 'hostid');

		$this->checkInput($hosts, __FUNCTION__);

		// delete the discovery rules first
		$delRules = API::DiscoveryRule()->get(array(
			'hostids' => $hostids,
			'nopermissions' => true,
			'preservekeys' => true
		));
		if ($delRules) {
			API::DiscoveryRule()->delete(array_keys($delRules), true);
		}

		// delete the items
		$delItems = API::Item()->get(array(
			'templateids' => $hostids,
			'filter' => array(
				'flags' => array(
					ZBX_FLAG_DISCOVERY_NORMAL,
					ZBX_FLAG_DISCOVERY_CREATED
				)
			),
			'output' => API_OUTPUT_SHORTEN,
			'nopermissions' => true,
			'preservekeys' => true
		));
		if ($delItems) {
			API::Item()->delete(array_keys($delItems), true);
		}

// delete web tests
		$delHttptests = array();
		$dbHttptests = get_httptests_by_hostid($hostids);
		while ($dbHttptest = DBfetch($dbHttptests)) {
			$delHttptests[$dbHttptest['httptestid']] = $dbHttptest['httptestid'];
		}
		if (!empty($delHttptests)) {
			API::WebCheck()->delete($delHttptests);
		}


// delete screen items
		DB::delete('screens_items', array(
			'resourceid' => $hostids,
			'resourcetype' => SCREEN_RESOURCE_HOST_TRIGGERS
		));

// delete host from maps
		if (!empty($hostids)) {
			DB::delete('sysmaps_elements', array(
				'elementtype' => SYSMAP_ELEMENT_TYPE_HOST,
				'elementid' => $hostids
			));
		}

// disable actions
// actions from conditions
		$actionids = array();
		$sql = 'SELECT DISTINCT actionid'.
				' FROM conditions'.
				' WHERE conditiontype='.CONDITION_TYPE_HOST.
				' AND '.DBcondition('value', $hostids);
		$dbActions = DBselect($sql);
		while ($dbAction = DBfetch($dbActions)) {
			$actionids[$dbAction['actionid']] = $dbAction['actionid'];
		}

// actions from operations
		$sql = 'SELECT DISTINCT o.actionid'.
				' FROM operations o, opcommand_hst oh'.
				' WHERE o.operationid=oh.operationid'.
				' AND '.DBcondition('oh.hostid', $hostids);
		$dbActions = DBselect($sql);
		while ($dbAction = DBfetch($dbActions)) {
			$actionids[$dbAction['actionid']] = $dbAction['actionid'];
		}

		if (!empty($actionids)) {
			$update = array();
			$update[] = array(
				'values' => array('status' => ACTION_STATUS_DISABLED),
				'where' => array('actionid' => $actionids)
			);
			DB::update('actions', $update);
		}

// delete action conditions
		DB::delete('conditions', array(
			'conditiontype' => CONDITION_TYPE_HOST,
			'value' => $hostids
		));

// delete action operation commands
		$operationids = array();
		$sql = 'SELECT DISTINCT oh.operationid'.
				' FROM opcommand_hst oh'.
				' WHERE '.DBcondition('oh.hostid', $hostids);
		$dbOperations = DBselect($sql);
		while ($dbOperation = DBfetch($dbOperations)) {
			$operationids[$dbOperation['operationid']] = $dbOperation['operationid'];
		}

		DB::delete('opcommand_hst', array(
			'hostid' => $hostids,
		));

// delete empty operations
		$delOperationids = array();
		$sql = 'SELECT DISTINCT o.operationid'.
				' FROM operations o'.
				' WHERE '.DBcondition('o.operationid', $operationids).
				' AND NOT EXISTS(SELECT oh.opcommand_hstid FROM opcommand_hst oh WHERE oh.operationid=o.operationid)';
		$dbOperations = DBselect($sql);
		while ($dbOperation = DBfetch($dbOperations)) {
			$delOperationids[$dbOperation['operationid']] = $dbOperation['operationid'];
		}

		DB::delete('operations', array(
			'operationid' => $delOperationids,
		));

		$hosts = API::Host()->get(array(
			'output' => array(
				'hostid',
				'name'
			),
			'hostids' => $hostids,
			'nopermissions' => true
		));

// delete host inventory
		DB::delete('host_inventory', array('hostid' => $hostids));

// delete host applications
		DB::delete('applications', array('hostid' => $hostids));

// delete host
		DB::delete('hosts', array('hostid' => $hostids));

// TODO: remove info from API
		foreach ($hosts as $host) {
			info(_s('Deleted: Host "%1$s".', $host['name']));
			add_audit_ext(AUDIT_ACTION_DELETE, AUDIT_RESOURCE_HOST, $host['hostid'], $host['name'], 'hosts', NULL, NULL);
		}

		return array('hostids' => $hostids);
	}

	public function isReadable($ids) {
		if (!is_array($ids)) {
			return false;
		}
		if (empty($ids)) {
			return true;
		}

		$ids = array_unique($ids);

		$count = $this->get(array(
			'nodeids' => get_current_nodeid(true),
			'hostids' => $ids,
			'output' => API_OUTPUT_SHORTEN,
			'templated_hosts' => true,
			'countOutput' => true
		));

		return (count($ids) == $count);
	}

	public function isWritable($ids) {
		if (!is_array($ids)) {
			return false;
		}
		if (empty($ids)) {
			return true;
		}

		$ids = array_unique($ids);

		$count = $this->get(array(
			'nodeids' => get_current_nodeid(true),
			'hostids' => $ids,
			'output' => API_OUTPUT_SHORTEN,
			'editable' => true,
			'templated_hosts' => true,
			'countOutput' => true
		));

		return (count($ids) == $count);
	}
}
