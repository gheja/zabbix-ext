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
 * @package API
 */
class CTriggerPrototype extends CTriggerGeneral {

	protected $tableName = 'triggers';
	protected $tableAlias = 't';

	/**
	 * Get TriggerPrototypes data
	 *
	 * @param _array $options
	 * @param array $options['itemids']
	 * @param array $options['hostids']
	 * @param array $options['groupids']
	 * @param array $options['triggerids']
	 * @param array $options['applicationids']
	 * @param array $options['status']
	 * @param array $options['editable']
	 * @param array $options['count']
	 * @param array $options['pattern']
	 * @param array $options['limit']
	 * @param array $options['order']
	 * @return array|int item data as array or false if error
	 */
	public function get(array $options = array()) {
		$result = array();
		$userType = self::$userData['type'];
		$userid = self::$userData['userid'];

		// allowed columns for sorting
		$sortColumns = array('triggerid', 'description', 'status', 'priority', 'lastchange');

		// allowed output options for [ select_* ] params
		$subselectsAllowedOutputs = array(API_OUTPUT_REFER, API_OUTPUT_EXTEND);

		$sqlParts = array(
			'select'	=> array('triggers' => 't.triggerid'),
			'from'		=> array('t' => 'triggers t'),
			'where'		=> array('t.flags='.ZBX_FLAG_DISCOVERY_CHILD),
			'group'		=> array(),
			'order'		=> array(),
			'limit'		=> null
		);

		$defOptions = array(
			'nodeids'						=> null,
			'groupids'						=> null,
			'templateids'					=> null,
			'hostids'						=> null,
			'triggerids'					=> null,
			'itemids'						=> null,
			'applicationids'				=> null,
			'discoveryids'					=> null,
			'functions'						=> null,
			'inherited'						=> null,
			'templated'						=> null,
			'monitored' 					=> null,
			'active' 						=> null,
			'maintenance'					=> null,
			'withUnacknowledgedEvents'		=> null,
			'withAcknowledgedEvents'		=> null,
			'withLastEventUnacknowledged'	=> null,
			'skipDependent'					=> null,
			'nopermissions'					=> null,
			'editable'						=> null,
			// timing
			'lastChangeSince'				=> null,
			'lastChangeTill'				=> null,
			// filter
			'group'							=> null,
			'host'							=> null,
			'only_true'						=> null,
			'min_severity'					=> null,
			'filter'						=> null,
			'search'						=> null,
			'searchByAny'					=> null,
			'startSearch'					=> null,
			'excludeSearch'					=> null,
			'searchWildcardsEnabled'		=> null,
			// output
			'expandData'					=> null,
			'expandDescription'				=> null,
			'output'						=> API_OUTPUT_REFER,
			'selectGroups'					=> null,
			'selectHosts'					=> null,
			'selectItems'					=> null,
			'selectFunctions'				=> null,
			'selectDiscoveryRule'			=> null,
			'countOutput'					=> null,
			'groupCount'					=> null,
			'preservekeys'					=> null,
			'sortfield'						=> '',
			'sortorder'						=> '',
			'limit'							=> null,
			'limitSelects'					=> null
		);
		$options = zbx_array_merge($defOptions, $options);

		if (is_array($options['output'])) {
			unset($sqlParts['select']['triggers']);

			$dbTable = DB::getSchema('triggers');
			$sqlParts['select']['triggerid'] = 't.triggerid';
			foreach ($options['output'] as $field) {
				if (isset($dbTable['fields'][$field])) {
					$sqlParts['select'][$field] = 't.'.$field;
				}
			}
			$options['output'] = API_OUTPUT_CUSTOM;
		}

		// editable + permission check
		if (USER_TYPE_SUPER_ADMIN == $userType || $options['nopermissions']) {
		}
		else {
			$permission = $options['editable'] ? PERM_READ_WRITE : PERM_READ_ONLY;

			$sqlParts['from']['functions'] = 'functions f';
			$sqlParts['from']['items'] = 'items i';
			$sqlParts['from']['hosts_groups'] = 'hosts_groups hg';
			$sqlParts['from']['rights'] = 'rights r';
			$sqlParts['from']['users_groups'] = 'users_groups ug';
			$sqlParts['where']['ft'] = 'f.triggerid=t.triggerid';
			$sqlParts['where']['fi'] = 'f.itemid=i.itemid';
			$sqlParts['where']['hgi'] = 'hg.hostid=i.hostid';
			$sqlParts['where'][] = 'r.id=hg.groupid ';
			$sqlParts['where'][] = 'r.groupid=ug.usrgrpid';
			$sqlParts['where'][] = 'ug.userid='.$userid;
			$sqlParts['where'][] = 'r.permission>='.$permission;
			$sqlParts['where'][] = 'NOT EXISTS ('.
				' SELECT ff.triggerid'.
				' FROM functions ff,items ii'.
				' WHERE ff.triggerid=t.triggerid'.
					' AND ff.itemid=ii.itemid'.
					' AND EXISTS ('.
						' SELECT hgg.groupid'.
						' FROM hosts_groups hgg,rights rr,users_groups gg'.
						' WHERE hgg.hostid=ii.hostid'.
							' AND rr.id=hgg.groupid'.
							' AND rr.groupid=gg.usrgrpid'.
							' AND gg.userid='.$userid.
							' AND rr.permission<'.$permission.'))';
		}

		// nodeids
		$nodeids = !is_null($options['nodeids']) ? $options['nodeids'] : get_current_nodeid();

		// groupids
		if (!is_null($options['groupids'])) {
			zbx_value2array($options['groupids']);

			if ($options['output'] != API_OUTPUT_SHORTEN) {
				$sqlParts['select']['groupid'] = 'hg.groupid';
			}

			$sqlParts['from']['functions'] = 'functions f';
			$sqlParts['from']['items'] = 'items i';
			$sqlParts['from']['hosts_groups'] = 'hosts_groups hg';
			$sqlParts['where']['hgi'] = 'hg.hostid=i.hostid';
			$sqlParts['where']['ft'] = 'f.triggerid=t.triggerid';
			$sqlParts['where']['fi'] = 'f.itemid=i.itemid';
			$sqlParts['where']['groupid'] = DBcondition('hg.groupid', $options['groupids']);

			if (!is_null($options['groupCount'])) {
				$sqlParts['group']['hg'] = 'hg.groupid';
			}
		}

		// templateids
		if (!is_null($options['templateids'])) {
			zbx_value2array($options['templateids']);

			if (!is_null($options['hostids'])) {
				zbx_value2array($options['hostids']);
				$options['hostids'] = array_merge($options['hostids'], $options['templateids']);
			}
			else {
				$options['hostids'] = $options['templateids'];
			}
		}

		// hostids
		if (!is_null($options['hostids'])) {
			zbx_value2array($options['hostids']);

			if ($options['output'] != API_OUTPUT_SHORTEN) {
				$sqlParts['select']['hostid'] = 'i.hostid';
			}

			$sqlParts['from']['functions'] = 'functions f';
			$sqlParts['from']['items'] = 'items i';
			$sqlParts['where']['hostid'] = DBcondition('i.hostid', $options['hostids']);
			$sqlParts['where']['ft'] = 'f.triggerid=t.triggerid';
			$sqlParts['where']['fi'] = 'f.itemid=i.itemid';

			if (!is_null($options['groupCount'])) {
				$sqlParts['group']['i'] = 'i.hostid';
			}
		}

		// triggerids
		if (!is_null($options['triggerids'])) {
			zbx_value2array($options['triggerids']);

			$sqlParts['where']['triggerid'] = DBcondition('t.triggerid', $options['triggerids']);
		}

		// itemids
		if (!is_null($options['itemids'])) {
			zbx_value2array($options['itemids']);

			if ($options['output'] != API_OUTPUT_SHORTEN) {
				$sqlParts['select']['itemid'] = 'f.itemid';
			}

			$sqlParts['from']['functions'] = 'functions f';
			$sqlParts['where']['itemid'] = DBcondition('f.itemid', $options['itemids']);
			$sqlParts['where']['ft'] = 'f.triggerid=t.triggerid';

			if (!is_null($options['groupCount'])) {
				$sqlParts['group']['f'] = 'f.itemid';
			}
		}

		// applicationids
		if (!is_null($options['applicationids'])) {
			zbx_value2array($options['applicationids']);

			if ($options['output'] != API_OUTPUT_SHORTEN) {
				$sqlParts['select']['applicationid'] = 'a.applicationid';
			}

			$sqlParts['from']['functions'] = 'functions f';
			$sqlParts['from']['items'] = 'items i';
			$sqlParts['from']['applications'] = 'applications a';
			$sqlParts['where']['a'] = DBcondition('a.applicationid', $options['applicationids']);
			$sqlParts['where']['ia'] = 'i.hostid=a.hostid';
			$sqlParts['where']['ft'] = 'f.triggerid=t.triggerid';
			$sqlParts['where']['fi'] = 'f.itemid=i.itemid';
		}

		// discoveryids
		if (!is_null($options['discoveryids'])) {
			zbx_value2array($options['discoveryids']);

			if ($options['output'] != API_OUTPUT_SHORTEN) {
				$sqlParts['select']['itemid'] = 'id.parent_itemid';
			}
			$sqlParts['from']['functions'] = 'functions f';
			$sqlParts['from']['item_discovery'] = 'item_discovery id';
			$sqlParts['where']['fid'] = 'f.itemid=id.itemid';
			$sqlParts['where']['ft'] = 'f.triggerid=t.triggerid';
			$sqlParts['where'][] = DBcondition('id.parent_itemid', $options['discoveryids']);

			if (!is_null($options['groupCount'])) {
				$sqlParts['group']['id'] = 'id.parent_itemid';
			}
		}

		// functions
		if (!is_null($options['functions'])) {
			zbx_value2array($options['functions']);

			$sqlParts['from']['functions'] = 'functions f';
			$sqlParts['where']['ft'] = 'f.triggerid=t.triggerid';
			$sqlParts['where'][] = DBcondition('f.function', $options['functions']);
		}

		// monitored
		if (!is_null($options['monitored'])) {
			$sqlParts['where']['monitored'] = ''.
				' NOT EXISTS ('.
					' SELECT ff.functionid'.
					' FROM functions ff'.
					' WHERE ff.triggerid=t.triggerid'.
						' AND EXISTS ('.
								' SELECT ii.itemid'.
								' FROM items ii, hosts hh'.
								' WHERE ff.itemid=ii.itemid'.
									' AND hh.hostid=ii.hostid'.
									' AND ('.
										' ii.status<>'.ITEM_STATUS_ACTIVE.
										' OR hh.status<>'.HOST_STATUS_MONITORED.
									' )'.
						' )'.
				' )';
			$sqlParts['where']['status'] = 't.status='.TRIGGER_STATUS_ENABLED;
		}

		// active
		if (!is_null($options['active'])) {
			$sqlParts['where']['active'] = ''.
				' NOT EXISTS ('.
					' SELECT ff.functionid'.
					' FROM functions ff'.
					' WHERE ff.triggerid=t.triggerid'.
						' AND EXISTS ('.
							' SELECT ii.itemid'.
							' FROM items ii, hosts hh'.
							' WHERE ff.itemid=ii.itemid'.
								' AND hh.hostid=ii.hostid'.
								' AND  hh.status<>'.HOST_STATUS_MONITORED.
						' )'.
				' )';
			$sqlParts['where']['status'] = 't.status='.TRIGGER_STATUS_ENABLED;
		}

		// maintenance
		if (!is_null($options['maintenance'])) {
			$sqlParts['where'][] = (($options['maintenance'] == 0) ? ' NOT ':'').
				' EXISTS ('.
					' SELECT ff.functionid'.
					' FROM functions ff'.
					' WHERE ff.triggerid=t.triggerid'.
						' AND EXISTS ('.
								' SELECT ii.itemid'.
								' FROM items ii, hosts hh'.
								' WHERE ff.itemid=ii.itemid'.
									' AND hh.hostid=ii.hostid'.
									' AND hh.maintenance_status=1'.
						' )'.
				' )';
			$sqlParts['where'][] = 't.status='.TRIGGER_STATUS_ENABLED;
		}

		// lastChangeSince
		if (!is_null($options['lastChangeSince'])) {
			$sqlParts['where']['lastchangesince'] = 't.lastchange>'.$options['lastChangeSince'];
		}

		// lastChangeTill
		if (!is_null($options['lastChangeTill'])) {
			$sqlParts['where']['lastchangetill'] = 't.lastchange<'.$options['lastChangeTill'];
		}

		// withUnacknowledgedEvents
		if (!is_null($options['withUnacknowledgedEvents'])) {
			$sqlParts['where']['unack'] = ' EXISTS ('.
				' SELECT e.eventid'.
				' FROM events e'.
				' WHERE e.objectid=t.triggerid'.
					' AND e.object='.EVENT_OBJECT_TRIGGER.
					' AND e.value_changed='.TRIGGER_VALUE_CHANGED_YES.
					' AND e.value='.TRIGGER_VALUE_TRUE.
					' AND e.acknowledged=0)';
		}

		// withAcknowledgedEvents
		if (!is_null($options['withAcknowledgedEvents'])) {
			$sqlParts['where']['ack'] = 'NOT EXISTS ('.
				' SELECT e.eventid'.
				' FROM events e'.
				' WHERE e.objectid=t.triggerid'.
					' AND e.object='.EVENT_OBJECT_TRIGGER.
					' AND e.value_changed='.TRIGGER_VALUE_CHANGED_YES.
					' AND e.value='.TRIGGER_VALUE_TRUE.
					' AND e.acknowledged=0)';
		}

		// templated
		if (!is_null($options['templated'])) {
			$sqlParts['from']['functions'] = 'functions f';
			$sqlParts['from']['items'] = 'items i';
			$sqlParts['from']['hosts'] = 'hosts h';
			$sqlParts['where']['ft'] = 'f.triggerid=t.triggerid';
			$sqlParts['where']['fi'] = 'f.itemid=i.itemid';
			$sqlParts['where']['hi'] = 'h.hostid=i.hostid';

			if ($options['templated']) {
				$sqlParts['where'][] = 'h.status='.HOST_STATUS_TEMPLATE;
			}
			else {
				$sqlParts['where'][] = 'h.status<>'.HOST_STATUS_TEMPLATE;
			}
		}

		// inherited
		if (!is_null($options['inherited'])) {
			if ($options['inherited']) {
				$sqlParts['where'][] = 't.templateid IS NOT NULL';
			}
			else {
				$sqlParts['where'][] = 't.templateid IS NULL';
			}
		}

		// search
		if (is_array($options['search'])) {
			zbx_db_search('triggers t', $options, $sqlParts);
		}

		// filter
		if (is_array($options['filter'])) {
			zbx_db_filter('triggers t', $options, $sqlParts);

			if (isset($options['filter']['host']) && !is_null($options['filter']['host'])) {
				zbx_value2array($options['filter']['host']);

				$sqlParts['from']['functions'] = 'functions f';
				$sqlParts['from']['items'] = 'items i';
				$sqlParts['where']['ft'] = 'f.triggerid=t.triggerid';
				$sqlParts['where']['fi'] = 'f.itemid=i.itemid';

				$sqlParts['from']['hosts'] = 'hosts h';
				$sqlParts['where']['hi'] = 'h.hostid=i.hostid';
				$sqlParts['where']['host'] = DBcondition('h.host', $options['filter']['host']);
			}

			if (isset($options['filter']['hostid']) && !is_null($options['filter']['hostid'])) {
				zbx_value2array($options['filter']['hostid']);

				$sqlParts['from']['functions'] = 'functions f';
				$sqlParts['from']['items'] = 'items i';
				$sqlParts['where']['ft'] = 'f.triggerid=t.triggerid';
				$sqlParts['where']['fi'] = 'f.itemid=i.itemid';

				$sqlParts['where']['hostid'] = DBcondition('i.hostid', $options['filter']['hostid']);
			}
		}

		// group
		if (!is_null($options['group'])) {
			if ($options['output'] != API_OUTPUT_SHORTEN) {
				$sqlParts['select']['name'] = 'g.name';
			}

			$sqlParts['from']['functions'] = 'functions f';
			$sqlParts['from']['items'] = 'items i';
			$sqlParts['from']['hosts_groups'] = 'hosts_groups hg';
			$sqlParts['from']['groups'] = 'groups g';
			$sqlParts['where']['ft'] = 'f.triggerid=t.triggerid';
			$sqlParts['where']['fi'] = 'f.itemid=i.itemid';
			$sqlParts['where']['hgi'] = 'hg.hostid=i.hostid';
			$sqlParts['where']['ghg'] = 'g.groupid = hg.groupid';
			$sqlParts['where']['group'] = ' UPPER(g.name)='.zbx_dbstr(zbx_strtoupper($options['group']));
		}

		// host
		if (!is_null($options['host'])) {
			if ($options['output'] != API_OUTPUT_SHORTEN) {
				$sqlParts['select']['host'] = 'h.host';
			}

			$sqlParts['from']['functions'] = 'functions f';
			$sqlParts['from']['items'] = 'items i';
			$sqlParts['from']['hosts'] = 'hosts h';
			$sqlParts['where']['i'] = DBcondition('i.hostid', $options['hostids']);
			$sqlParts['where']['ft'] = 'f.triggerid=t.triggerid';
			$sqlParts['where']['fi'] = 'f.itemid=i.itemid';
			$sqlParts['where']['hi'] = 'h.hostid=i.hostid';
			$sqlParts['where']['host'] = ' UPPER(h.host)='.zbx_dbstr(zbx_strtoupper($options['host']));
		}

		// only_true
		if (!is_null($options['only_true'])) {
			$config = select_config();

			$sqlParts['where']['ot'] = '(t.value='.TRIGGER_VALUE_TRUE.
				' OR '.
				'(t.value='.TRIGGER_VALUE_FALSE.' AND t.lastchange>'.(time() - $config['ok_period']).'))';
		}

		// min_severity
		if (!is_null($options['min_severity'])) {
			$sqlParts['where'][] = 't.priority>='.$options['min_severity'];
		}

		// output
		if ($options['output'] == API_OUTPUT_EXTEND) {
			$sqlParts['select']['triggers'] = 't.*';
		}

		// expandData
		if (!is_null($options['expandData'])) {
			$sqlParts['select']['host'] = 'h.host';
			$sqlParts['select']['hostid'] = 'h.hostid';
			$sqlParts['from']['functions'] = 'functions f';
			$sqlParts['from']['items'] = 'items i';
			$sqlParts['from']['hosts'] = 'hosts h';
			$sqlParts['where']['ft'] = 'f.triggerid=t.triggerid';
			$sqlParts['where']['fi'] = 'f.itemid=i.itemid';
			$sqlParts['where']['hi'] = 'h.hostid=i.hostid';
		}

		// countOutput
		if (!is_null($options['countOutput'])) {
			$options['sortfield'] = '';
			$sqlParts['select'] = array('COUNT(DISTINCT t.triggerid) as rowscount');

			// groupCount
			if (!is_null($options['groupCount'])) {
				foreach ($sqlParts['group'] as $key => $fields) {
					$sqlParts['select'][$key] = $fields;
				}
			}
		}

		// sorting
		zbx_db_sorting($sqlParts, $options, $sortColumns, 't');

		// limit
		if (zbx_ctype_digit($options['limit']) && $options['limit']) {
			$sqlParts['limit'] = $options['limit'];
		}

		$triggerids = array();

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
		if (!empty($sqlParts['where'])) {
			$sqlWhere .= ' AND '.implode(' AND ', $sqlParts['where']);
		}
		if (!empty($sqlParts['group'])) {
			$sqlWhere .= ' GROUP BY '.implode(',', $sqlParts['group']);
		}
		if (!empty($sqlParts['order'])) {
			$sqlOrder .= ' ORDER BY '.implode(',', $sqlParts['order']);
		}
		$sqlLimit = $sqlParts['limit'];

		$sql = 'SELECT '.zbx_db_distinct($sqlParts).' '.$sqlSelect.
				' FROM '.$sqlFrom.
				' WHERE '.DBin_node('t.triggerid', $nodeids).
					$sqlWhere.
					$sqlGroup.
					$sqlOrder;

		$dbRes = DBselect($sql, $sqlLimit);
		while ($trigger = DBfetch($dbRes)) {
			if (!is_null($options['countOutput'])) {
				if (!is_null($options['groupCount'])) {
					$result[] = $trigger;
				}
				else {
					$result = $trigger['rowscount'];
				}
			}
			else {
				$triggerids[$trigger['triggerid']] = $trigger['triggerid'];

				if ($options['output'] == API_OUTPUT_SHORTEN) {
					$result[$trigger['triggerid']] = array('triggerid' => $trigger['triggerid']);
				}
				else {
					if (!isset($result[$trigger['triggerid']])) {
						$result[$trigger['triggerid']] = array();
					}
					if (!is_null($options['selectHosts']) && !isset($result[$trigger['triggerid']]['hosts'])) {
						$result[$trigger['triggerid']]['hosts'] = array();
					}
					if (!is_null($options['selectItems']) && !isset($result[$trigger['triggerid']]['items'])) {
						$result[$trigger['triggerid']]['items'] = array();
					}
					if (!is_null($options['selectFunctions']) && !isset($result[$trigger['triggerid']]['functions'])) {
						$result[$trigger['triggerid']]['functions'] = array();
					}
					if (!is_null($options['selectDiscoveryRule']) && !isset($result[$trigger['triggerid']]['discoveryRule'])) {
						$result[$trigger['triggerid']]['discoveryRule'] = array();
					}

					// groups
					if (isset($trigger['groupid']) && is_null($options['selectGroups'])) {
						if (!isset($result[$trigger['triggerid']]['groups'])) {
							$result[$trigger['triggerid']]['groups'] = array();
						}

						$result[$trigger['triggerid']]['groups'][] = array('groupid' => $trigger['groupid']);
						unset($trigger['groupid']);
					}

					// hostids
					if (isset($trigger['hostid']) && is_null($options['selectHosts'])) {
						if (!isset($result[$trigger['triggerid']]['hosts'])) {
							$result[$trigger['triggerid']]['hosts'] = array();
						}

						$result[$trigger['triggerid']]['hosts'][] = array('hostid' => $trigger['hostid']);

						if (is_null($options['expandData'])) {
							unset($trigger['hostid']);
						}
					}

					// itemids
					if (isset($trigger['itemid']) && is_null($options['selectItems'])) {
						if (!isset($result[$trigger['triggerid']]['items'])) {
							$result[$trigger['triggerid']]['items'] = array();
						}

						$result[$trigger['triggerid']]['items'][] = array('itemid' => $trigger['itemid']);
						unset($trigger['itemid']);
					}

					$result[$trigger['triggerid']] += $trigger;
				}
			}
		}

		if (!is_null($options['countOutput'])) {
			return $result;
		}

		// skipDependent
		if (!is_null($options['skipDependent'])) {
			$tids = $triggerids;
			$map = array();

			do {
				$dbResult = DBselect(
					'SELECT d.triggerid_down,d.triggerid_up,t.value'.
					' FROM trigger_depends d,triggers t'.
					' WHERE '.DBcondition('d.triggerid_down', $tids).
						' AND d.triggerid_up=t.triggerid'
				);
				$tids = array();
				while ($row = DBfetch($dbResult)) {
					if (TRIGGER_VALUE_TRUE == $row['value']) {
						if (isset($map[$row['triggerid_down']])) {
							foreach ($map[$row['triggerid_down']] as $triggerid => $state) {
								unset($result[$triggerid], $triggerids[$triggerid]);
							}
						}
						else {
							unset($result[$row['triggerid_down']], $triggerids[$row['triggerid_down']]);
						}
					}
					else {
						if (isset($map[$row['triggerid_down']])) {
							if (!isset($map[$row['triggerid_up']])) {
								$map[$row['triggerid_up']] = array();
							}

							$map[$row['triggerid_up']] += $map[$row['triggerid_down']];
						}
						else {
							if (!isset($map[$row['triggerid_up']])) {
								$map[$row['triggerid_up']] = array();
							}

							$map[$row['triggerid_up']][$row['triggerid_down']] = 1;
						}
						$tids[] = $row['triggerid_up'];
					}
				}
			} while (!empty($tids));
		}

		// withLastEventUnacknowledged
		if (!is_null($options['withLastEventUnacknowledged'])) {
			$eventids = array();

			$eventsDb = DBselect(
				'SELECT MAX(e.eventid) AS eventid,e.objectid'.
				' FROM events e'.
				' WHERE e.object='.EVENT_OBJECT_TRIGGER.
					' AND '.DBcondition('e.objectid', $triggerids).
					' AND '.DBcondition('e.value', array(TRIGGER_VALUE_TRUE)).
					' AND e.value_changed='.TRIGGER_VALUE_CHANGED_YES.
				' GROUP BY e.objectid'
			);
			while ($event = DBfetch($eventsDb)) {
				$eventids[] = $event['eventid'];
			}

			$correctTriggerids = array();
			$triggersDb = DBselect(
				'SELECT e.objectid'.
				' FROM events e'.
				' WHERE '.DBcondition('e.eventid', $eventids).
					' AND e.acknowledged=0'
			);
			while ($trigger = DBfetch($triggersDb)) {
				$correctTriggerids[$trigger['objectid']] = $trigger['objectid'];
			}
			foreach ($result as $triggerid => $trigger) {
				if (!isset($correctTriggerids[$triggerid])) {
					unset($result[$triggerid], $triggerids[$triggerid]);
				}
			}
		}

		/*
		 * Adding objects
		 */
		// adding groups
		if (!is_null($options['selectGroups']) && str_in_array($options['selectGroups'], $subselectsAllowedOutputs)) {
			$groups = API::HostGroup()->get(array(
				'nodeids' => $nodeids,
				'output' => $options['selectGroups'],
				'triggerids' => $triggerids,
				'preservekeys' => true
			));
			foreach ($groups as $group) {
				$gtriggers = $group['triggers'];
				unset($group['triggers']);

				foreach ($gtriggers as $trigger) {
					$result[$trigger['triggerid']]['groups'][] = $group;
				}
			}
		}

		// adding hosts
		if (!is_null($options['selectHosts'])) {
			$objParams = array(
				'nodeids' => $nodeids,
				'triggerids' => $triggerids,
				'templated_hosts' => 1,
				'nopermissions' => true,
				'preservekeys' => true
			);

			if (is_array($options['selectHosts']) || str_in_array($options['selectHosts'], $subselectsAllowedOutputs)) {
				$objParams['output'] = $options['selectHosts'];
				$hosts = API::Host()->get($objParams);

				if (!is_null($options['limitSelects'])) {
					order_result($hosts, 'host');
				}
				foreach ($hosts as $hostid => $host) {
					unset($hosts[$hostid]['triggers']);

					$count = array();
					foreach ($host['triggers'] as $trigger) {
						if (!is_null($options['limitSelects'])) {
							if (!isset($count[$trigger['triggerid']])) {
								$count[$trigger['triggerid']] = 0;
							}
							$count[$trigger['triggerid']]++;

							if ($count[$trigger['triggerid']] > $options['limitSelects']) {
								continue;
							}
						}

						$result[$trigger['triggerid']]['hosts'][] = &$hosts[$hostid];
					}
				}
			}
			elseif (API_OUTPUT_COUNT == $options['selectHosts']) {
				$objParams['countOutput'] = 1;
				$objParams['groupCount'] = 1;

				$hosts = API::Host()->get($objParams);
				$hosts = zbx_toHash($hosts, 'hostid');
				foreach ($result as $triggerid => $trigger) {
					$result[$triggerid]['hosts'] = isset($hosts[$triggerid]) ? $hosts[$triggerid]['rowscount'] : 0;
				}
			}
		}

		// adding functions
		if (!is_null($options['selectFunctions']) && str_in_array($options['selectFunctions'], $subselectsAllowedOutputs)) {
			$sqlSelect = ($options['selectFunctions'] == API_OUTPUT_EXTEND) ? 'f.*' : 'f.functionid, f.triggerid';

			$res = DBselect(
				'SELECT '.$sqlSelect.
				' FROM functions f'.
				' WHERE '.DBcondition('f.triggerid', $triggerids)
			);
			while ($function = DBfetch($res)) {
				$triggerid = $function['triggerid'];
				unset($function['triggerid']);

				$result[$triggerid]['functions'][] = $function;
			}
		}

		// adding items
		if (!is_null($options['selectItems']) && (is_array($options['selectItems']) || str_in_array($options['selectItems'], $subselectsAllowedOutputs))) {
			$objParams = array(
				'nodeids' => $nodeids,
				'output' => $options['selectItems'],
				'triggerids' => $triggerids,
				'webitems' => 1,
				'nopermissions' => true,
				'preservekeys' => true
			);
			$items = API::Item()->get($objParams);
			foreach ($items as $itemid => $item) {
				$itriggers = $item['triggers'];
				unset($item['triggers']);
				foreach ($itriggers as $trigger) {
					$result[$trigger['triggerid']]['items'][] = $item;
				}
			}
		}

		// adding discoveryrule
		if (!is_null($options['selectDiscoveryRule'])) {
			$ruleids = $ruleMap = array();

			$dbRules = DBselect(
				'SELECT id.parent_itemid,f.triggerid'.
				' FROM item_discovery id,functions f'.
				' WHERE '.DBcondition('f.triggerid', $triggerids).
					' AND f.itemid=id.itemid'
			);
			while ($rule = DBfetch($dbRules)) {
				$ruleids[$rule['parent_itemid']] = $rule['parent_itemid'];
				$ruleMap[$rule['triggerid']] = $rule['parent_itemid'];
			}

			$objParams = array(
				'nodeids' => $nodeids,
				'itemids' => $ruleids,
				'nopermissions' => true,
				'preservekeys' => true
			);

			if (is_array($options['selectDiscoveryRule']) || str_in_array($options['selectDiscoveryRule'], $subselectsAllowedOutputs)) {
				$objParams['output'] = $options['selectDiscoveryRule'];
				$discoveryRules = API::Item()->get($objParams);

				foreach ($result as $triggerid => $trigger) {
					if (isset($ruleMap[$triggerid]) && isset($discoveryRules[$ruleMap[$triggerid]])) {
						$result[$triggerid]['discoveryRule'] = $discoveryRules[$ruleMap[$triggerid]];
					}
				}
			}
		}

		// expandDescription
		if (!is_null($options['expandDescription'])) {
			foreach ($result as $tnum => $trigger) {
				preg_match_all('/\$([1-9])/u', $trigger['description'], $numbers);
				preg_match_all('~{[0-9]+}[+\-\*/<>=#]?[\(]*(?P<val>[+\-0-9]+)[\)]*~u', $trigger['expression'], $matches);

				foreach ($numbers[1] as $i) {
					$rep = isset($matches['val'][$i - 1]) ? $matches['val'][$i - 1] : '';
					$result[$tnum]['description'] = str_replace('$'.($i), $rep, $result[$tnum]['description']);
				}
			}

			$functionids = array();
			$triggersToExpandHosts = array();
			$triggersToExpandItems = array();
			$triggersToExpandItems2 = array();
			foreach ($result as $tnum => $trigger) {
				preg_match_all('/{HOST\.NAME([1-9]?)}/u', $trigger['description'], $hnums);
				if (!empty($hnums[1])) {
					preg_match_all('/{([0-9]+)}/u', $trigger['expression'], $funcs);
					$funcs = $funcs[1];

					foreach ($hnums[1] as $fnum) {
						$fnum = $fnum ? $fnum : 1;
						if (isset($funcs[$fnum - 1])) {
							$functionid = $funcs[$fnum - 1];
							$functionids[$functionid] = $functionid;
							$triggersToExpandHosts[$trigger['triggerid']][$functionid] = $fnum;
						}
					}
				}

				preg_match_all('/{HOSTNAME([1-9]?)}/u', $trigger['description'], $hnums);
				if (!empty($hnums[1])) {
					preg_match_all('/{([0-9]+)}/u', $trigger['expression'], $funcs);
					$funcs = $funcs[1];

					foreach ($hnums[1] as $fnum) {
						$fnum = $fnum ? $fnum : 1;
						if (isset($funcs[$fnum - 1])) {
							$functionid = $funcs[$fnum - 1];
							$functionids[$functionid] = $functionid;
							$triggersToExpandHosts[$trigger['triggerid']][$functionid] = $fnum;
						}
					}
				}

				preg_match_all('/{HOST\.HOST([1-9]?)}/u', $trigger['description'], $hnums);
				if (!empty($hnums[1])) {
					preg_match_all('/{([0-9]+)}/u', $trigger['expression'], $funcs);
					$funcs = $funcs[1];

					foreach ($hnums[1] as $fnum) {
						$fnum = $fnum ? $fnum : 1;
						if (isset($funcs[$fnum - 1])) {
							$functionid = $funcs[$fnum - 1];
							$functionids[$functionid] = $functionid;
							$triggersToExpandHosts[$trigger['triggerid']][$functionid] = $fnum;
						}
					}
				}

				preg_match_all('/{ITEM\.LASTVALUE([1-9]?)}/u', $trigger['description'], $inums);
				if (!empty($inums[1])) {
					preg_match_all('/{([0-9]+)}/u', $trigger['expression'], $funcs);
					$funcs = $funcs[1];

					foreach ($inums[1] as $fnum) {
						$fnum = $fnum ? $fnum : 1;
						if (isset($funcs[$fnum - 1])) {
							$functionid = $funcs[$fnum - 1];
							$functionids[$functionid] = $functionid;
							$triggersToExpandItems[$trigger['triggerid']][$functionid] = $fnum;
						}
					}
				}

				preg_match_all('/{ITEM\.VALUE([1-9]?)}/u', $trigger['description'], $inums);
				if (!empty($inums[1])) {
					preg_match_all('/{([0-9]+)}/u', $trigger['expression'], $funcs);
					$funcs = $funcs[1];

					foreach ($inums[1] as $fnum) {
						$fnum = $fnum ? $fnum : 1;
						if (isset($funcs[$fnum - 1])) {
							$functionid = $funcs[$fnum - 1];
							$functionids[$functionid] = $functionid;
							$triggersToExpandItems2[$trigger['triggerid']][$functionid] = $fnum;
						}
					}
				}
			}

			if (!empty($functionids)) {
				$dbFuncs = DBselect(
					'SELECT DISTINCT f.triggerid,f.functionid,h.host,h.name,i.lastvalue'.
					' FROM functions f,items i,hosts h'.
					' WHERE f.itemid=i.itemid'.
						' AND i.hostid=h.hostid'.
						' AND h.status<>'.HOST_STATUS_TEMPLATE.
						' AND '.DBcondition('f.functionid', $functionids)
				);
				while ($func = DBfetch($dbFuncs)) {
					if (isset($triggersToExpandHosts[$func['triggerid']][$func['functionid']])) {
						$fnum = $triggersToExpandHosts[$func['triggerid']][$func['functionid']];
						if ($fnum == 1) {
							$result[$func['triggerid']]['description'] = str_replace('{HOSTNAME}', $func['host'], $result[$func['triggerid']]['description']);
							$result[$func['triggerid']]['description'] = str_replace('{HOST.NAME}', $func['name'], $result[$func['triggerid']]['description']);
							$result[$func['triggerid']]['description'] = str_replace('{HOST.HOST}', $func['host'], $result[$func['triggerid']]['description']);
						}

						$result[$func['triggerid']]['description'] = str_replace('{HOSTNAME'.$fnum.'}', $func['host'], $result[$func['triggerid']]['description']);
						$result[$func['triggerid']]['description'] = str_replace('{HOST.NAME'.$fnum.'}', $func['name'], $result[$func['triggerid']]['description']);
						$result[$func['triggerid']]['description'] = str_replace('{HOST.HOST'.$fnum.'}', $func['host'], $result[$func['triggerid']]['description']);
					}

					if (isset($triggersToExpandItems[$func['triggerid']][$func['functionid']])) {
						$fnum = $triggersToExpandItems[$func['triggerid']][$func['functionid']];
						if ($fnum == 1) {
							$result[$func['triggerid']]['description'] = str_replace('{ITEM.LASTVALUE}', $func['lastvalue'], $result[$func['triggerid']]['description']);
						}

						$result[$func['triggerid']]['description'] = str_replace('{ITEM.LASTVALUE'.$fnum.'}', $func['lastvalue'], $result[$func['triggerid']]['description']);
					}

					if (isset($triggersToExpandItems2[$func['triggerid']][$func['functionid']])) {
						$fnum = $triggersToExpandItems2[$func['triggerid']][$func['functionid']];
						if ($fnum == 1) {
							$result[$func['triggerid']]['description'] = str_replace('{ITEM.VALUE}', $func['lastvalue'], $result[$func['triggerid']]['description']);
						}

						$result[$func['triggerid']]['description'] = str_replace('{ITEM.VALUE'.$fnum.'}', $func['lastvalue'], $result[$func['triggerid']]['description']);
					}
				}
			}

			foreach ($result as $tnum => $trigger) {
				if ($res = preg_match_all('/'.ZBX_PREG_EXPRESSION_USER_MACROS.'/', $trigger['description'], $arr)) {
					$macros = API::UserMacro()->getMacros(array('macros' => $arr[1], 'triggerid' => $trigger['triggerid']));

					$search = array_keys($macros);
					$values = array_values($macros);

					$result[$tnum]['description'] = str_replace($search, $values, $trigger['description']);
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
	 * Add triggers
	 *
	 * Trigger params: expression, description, type, priority, status, comments, url, templateid
	 *
	 * @param array $triggers
	 * @return boolean
	 */
	public function create($triggers) {
		$triggers = zbx_toArray($triggers);
		$triggerids = array();

		foreach ($triggers as $trigger) {
			$triggerDbFields = array(
				'description' => null,
				'expression' => null,
				'error' => _('Trigger just added. No status update so far.'),
				'value' => TRIGGER_VALUE_UNKNOWN
			);
			if (!check_db_fields($triggerDbFields, $trigger)) {
				self::exception(ZBX_API_ERROR_PARAMETERS, _('Wrong fields for trigger.'));
			}

			$expressionData = new CTriggerExpression(array('expression' => $trigger['expression']));

			if (!empty($expressionData->errors)) {
				self::exception(ZBX_API_ERROR_PARAMETERS, implode(' ', $expressionData->errors));
			}

			$this->checkIfExistsOnHost($trigger);
		}

		$this->createReal($triggers);

		$createdTriggers = $this->get(array(
			'triggerids' => zbx_objectValues($triggers, 'triggerid'),
			'output' => array('description', 'expression', 'flags'),
			'selectItems' => API_OUTPUT_EXTEND,
			'selectHosts' => array('name')
		));
		foreach ($createdTriggers as $createdTrigger) {
			$hasPrototype = false;

			foreach ($createdTrigger['items'] as $titem) {
				if ($titem['flags'] == ZBX_FLAG_DISCOVERY_CHILD) {
					$hasPrototype = true;
					break;
				}
			}
			if (!$hasPrototype) {
				self::exception(ZBX_API_ERROR_PARAMETERS,
				_s('Trigger "%1$s:%2$s" does not have item prototype.', $createdTrigger['description'], $createdTrigger['expression']));
			}
		}

		foreach ($createdTriggers as $trigger) {
			$trigger['expression'] = explode_exp($trigger['expression']);
			$this->inherit($trigger);
		}

		return array('triggerids' => $triggerids);
	}

	/**
	 * Update triggers
	 *
	 * @param array $triggers
	 * @return boolean
	 */
	public function update($triggers) {
		$triggers = zbx_toArray($triggers);
		$triggerids = zbx_objectValues($triggers, 'triggerid');

		$dbTriggers = $this->get(array(
			'triggerids' => $triggerids,
			'editable' => true,
			'output' => API_OUTPUT_EXTEND,
			'preservekeys' => true
		));
		foreach ($triggers as $tnum => $trigger) {
			if (!isset($dbTriggers[$trigger['triggerid']])) {
				self::exception(ZBX_API_ERROR_PARAMETERS, _('No permissions to referred object or it does not exist!'));
			}

			if (!isset($trigger['triggerid'])) {
				self::exception(ZBX_API_ERROR_PARAMETERS, _s('Wrong fields for trigger.'));
			}

			$dbTrigger = $dbTriggers[$trigger['triggerid']];

			if (isset($trigger['expression'])) {
				$expressionFull = explode_exp($dbTrigger['expression']);
				if (strcmp($trigger['expression'], $expressionFull) == 0) {
					unset($triggers[$tnum]['expression']);
				}
			}

			if (isset($trigger['description']) && strcmp($trigger['description'], $dbTrigger['comments']) == 0) {
				unset($triggers[$tnum]['description']);
			}
			if (isset($trigger['priority']) && $trigger['priority'] == $dbTrigger['priority']) {
				unset($triggers[$tnum]['priority']);
			}
			if (isset($trigger['type']) && $trigger['type'] == $dbTrigger['type']) {
				unset($triggers[$tnum]['type']);
			}
			if (isset($trigger['comments']) && strcmp($trigger['comments'], $dbTrigger['comments']) == 0) {
				unset($triggers[$tnum]['comments']);
			}
			if (isset($trigger['url']) && strcmp($trigger['url'], $dbTrigger['url']) == 0) {
				unset($triggers[$tnum]['url']);
			}
			if (isset($trigger['status']) && $trigger['status'] == $dbTrigger['status']) {
				unset($triggers[$tnum]['status']);
			}

			$this->checkIfExistsOnHost($trigger);
		}

		$this->updateReal($triggers);

		$updatedTriggers = $this->get(array(
			'triggerids' => zbx_objectValues($triggers, 'triggerid'),
			'output' => API_OUTPUT_REFER,
			'selectItems' => API_OUTPUT_EXTEND
		));
		foreach ($updatedTriggers as $updatedTrigger) {
			$hasPrototype = false;

			foreach ($updatedTrigger['items'] as $titem) {
				if ($titem['flags'] == ZBX_FLAG_DISCOVERY_CHILD) {
					$hasPrototype = true;
					break;
				}
			}
			if (!$hasPrototype) {
				self::exception(ZBX_API_ERROR_PARAMETERS,
						sprintf(_('Trigger "%1$s" does not have item prototype.'), $trigger['description']));
			}
		}

		foreach ($triggers as $trigger) {
			$trigger['flags'] = ZBX_FLAG_DISCOVERY_CHILD;
			$this->inherit($trigger);
		}

		return array('triggerids' => $triggerids);
	}

	/**
	 * Delete triggers
	 *
	 * @param array $triggerids array with trigger ids
	 * @return array
	 */
	public function delete($triggerids, $nopermissions = false) {
		$triggerids = zbx_toArray($triggerids);

		if (empty($triggerids)) {
			self::exception(ZBX_API_ERROR_PARAMETERS, _('Empty input parameter.'));
		}

		$delTriggers = $this->get(array(
			'triggerids' => $triggerids,
			'output' => API_OUTPUT_EXTEND,
			'editable' => true,
			'preservekeys' => true
		));

		// TODO: remove $nopermissions hack
		if (!$nopermissions) {
			foreach ($triggerids as $gnum => $triggerid) {
				if (!isset($delTriggers[$triggerid])) {
					self::exception(ZBX_API_ERROR_PARAMETERS, _('No permissions to referred object or it does not exist!'));
				}

				if ($delTriggers[$triggerid]['templateid'] != 0) {
					self::exception(ZBX_API_ERROR_PARAMETERS,
						sprintf(_('Cannot delete templated trigger "%1$s:%2$s".'),
							$delTriggers[$triggerid]['description'],
							explode_exp($delTriggers[$triggerid]['expression']))
					);
				}
			}
		}

		// get child triggers
		$parentTriggerids = $triggerids;
		do {
			$dbItems = DBselect('SELECT triggerid FROM triggers WHERE '.DBcondition('templateid', $parentTriggerids));
			$parentTriggerids = array();
			while ($dbTrigger = DBfetch($dbItems)) {
				$parentTriggerids[] = $dbTrigger['triggerid'];
				$triggerids[$dbTrigger['triggerid']] = $dbTrigger['triggerid'];
			}
		} while (!empty($parentTriggerids));

		// select all triggers which are deleted (include childs)
		$delTriggers = $this->get(array(
			'triggerids' => $triggerids,
			'output' => API_OUTPUT_EXTEND,
			'nopermissions' => true,
			'preservekeys' => true,
			'selectHosts' => array('name')
		));

		DB::delete('events', array(
			'objectid' => $triggerids,
			'object' => EVENT_OBJECT_TRIGGER
		));

		DB::delete('sysmaps_elements', array(
			'elementid' => $triggerids,
			'elementtype' => SYSMAP_ELEMENT_TYPE_TRIGGER
		));

		// disable actions
		$actionids = array();
		$dbActions = DBselect(
			'SELECT DISTINCT c.actionid'.
			' FROM conditions c'.
			' WHERE c.conditiontype='.CONDITION_TYPE_TRIGGER.
				' AND '.DBcondition('c.value', $triggerids, false, true)
		);
		while ($dbAction = DBfetch($dbActions)) {
			$actionids[$dbAction['actionid']] = $dbAction['actionid'];
		}

		DBexecute('UPDATE actions SET status='.ACTION_STATUS_DISABLED.' WHERE '.DBcondition('actionid', $actionids));

		// delete action conditions
		DB::delete('conditions', array(
			'conditiontype' => CONDITION_TYPE_TRIGGER,
			'value' => $triggerids
		));

		// TODO: REMOVE info
		foreach ($delTriggers as $triggerid => $trigger) {
			info(_s('Deleted: Trigger prototype "%1$s" on "%2$s".', $trigger['description'],
					implode(', ', zbx_objectValues($trigger['hosts'], 'name'))));

			add_audit_ext(AUDIT_ACTION_DELETE, AUDIT_RESOURCE_TRIGGER_PROTOTYPE, $trigger['triggerid'],
					$trigger['description'].':'.$trigger['expression'], null, null, null);
		}

		DB::delete('triggers', array('triggerid' => $triggerids));

		update_services_status_all();

		return array('triggerids' => $triggerids);
	}

	protected function createReal(array &$triggers) {
		$triggers = zbx_toArray($triggers);

		foreach ($triggers as &$trigger) {
			$trigger['flags'] = ZBX_FLAG_DISCOVERY_CHILD;
		}
		unset($trigger);

		// insert triggers without expression
		$triggersCopy = $triggers;
		for ($i = 0, $size = count($triggersCopy); $i < $size; $i++) {
			unset($triggersCopy[$i]['expression']);
		}
		$triggerids = DB::insert('triggers', $triggersCopy);
		unset($triggersCopy);

		foreach ($triggers as $tnum => $trigger) {
			$triggerid = $triggers[$tnum]['triggerid'] = $triggerids[$tnum];

			$hosts = array();
			$expression = implode_exp($trigger['expression'], $triggerid, $hosts);
			if (is_null($expression)) {
				self::exception(ZBX_API_ERROR_PARAMETERS, _s('Cannot implode expression "%s".', $trigger['expression']));
			}
			DB::update('triggers', array(
				'values' => array('expression' => $expression),
				'where' => array('triggerid' => $triggerid)
			));

			info(_s('Created: Trigger prototype "%1$s" on "%2$s".', $trigger['description'], implode(', ', $hosts)));
		}
	}

	protected function updateReal(array $triggers) {
		$triggers = zbx_toArray($triggers);

		$dbTriggers = $this->get(array(
			'triggerids' => zbx_objectValues($triggers, 'triggerid'),
			'output' => API_OUTPUT_EXTEND,
			'selectHosts' => array('name'),
			'preservekeys' => true,
			'nopermissions' => true
		));

		$descriptionChanged = $expressionChanged = false;
		foreach ($triggers as &$trigger) {
			$dbTrigger = $dbTriggers[$trigger['triggerid']];
			$hosts = zbx_objectValues($dbTrigger['hosts'], 'name');

			if (isset($trigger['description']) && strcmp($dbTrigger['description'], $trigger['description']) != 0) {
				$descriptionChanged = true;
			}

			$expressionFull = explode_exp($dbTrigger['expression']);
			if (isset($trigger['expression']) && strcmp($expressionFull, $trigger['expression']) != 0) {
				$expressionChanged = true;
				$expressionFull = $trigger['expression'];
				$trigger['error'] = 'Trigger expression updated. No status update so far.';
			}

			if ($descriptionChanged || $expressionChanged) {
				$expressionData = new CTriggerExpression(array('expression' => $expressionFull));

				if (!empty($expressionData->errors)) {
					self::exception(ZBX_API_ERROR_PARAMETERS, implode(' ', $expressionData->errors));
				}
			}

			if ($expressionChanged) {
				delete_function_by_triggerid($trigger['triggerid']);

				$trigger['expression'] = implode_exp($expressionFull, $trigger['triggerid'], $hosts);
				if (is_null($trigger['expression'])) {
					self::exception(ZBX_API_ERROR_PARAMETERS, _s('Cannot implode expression "%s".', $expressionFull));
				}
			}

			$triggerUpdate = $trigger;
			if (!$descriptionChanged) {
				unset($triggerUpdate['description']);
			}
			if (!$expressionChanged) {
				unset($triggerUpdate['expression']);
			}

			DB::update('triggers', array(
				'values' => $triggerUpdate,
				'where' => array('triggerid' => $trigger['triggerid'])
			));

			$description = isset($trigger['description']) ? $trigger['description'] : $dbTrigger['description'];
			$expression = $expressionChanged ? explode_exp($trigger['expression']) : $expressionFull;

			info(_s('Updated: Trigger prototype "%1$s" on "%2$s".', $description, implode(', ', $hosts)));
		}
		unset($trigger);
	}

	public function syncTemplates($data) {
		$data['templateids'] = zbx_toArray($data['templateids']);
		$data['hostids'] = zbx_toArray($data['hostids']);

		$allowedHosts = API::Host()->get(array(
			'hostids' => $data['hostids'],
			'editable' => true,
			'preservekeys' => true,
			'templated_hosts' => true,
			'output' => API_OUTPUT_SHORTEN
		));
		foreach ($data['hostids'] as $hostid) {
			if (!isset($allowedHosts[$hostid])) {
				self::exception(ZBX_API_ERROR_PERMISSIONS, _('No permissions to referred object or it does not exist!'));
			}
		}

		$allowedTemplates = API::Template()->get(array(
			'templateids' => $data['templateids'],
			'preservekeys' => true,
			'editable' => true,
			'output' => API_OUTPUT_SHORTEN
		));
		foreach ($data['templateids'] as $templateid) {
			if (!isset($allowedTemplates[$templateid])) {
				self::exception(ZBX_API_ERROR_PERMISSIONS, _('No permissions to referred object or it does not exist!'));
			}
		}

		$triggers = $this->get(array(
			'hostids' => $data['templateids'],
			'preservekeys' => true,
			'output' => API_OUTPUT_EXTEND,
			'selectDependencies' => true
		));

		foreach ($triggers as $trigger) {
			$trigger['expression'] = explode_exp($trigger['expression']);
			$this->inherit($trigger, $data['hostids']);
		}

		return true;
	}
}
?>
