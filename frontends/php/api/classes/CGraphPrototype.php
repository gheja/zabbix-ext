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
 * File containing graph class for API.
 * @package API
 */
/**
 * Class containing methods for operations with graphs
 */
class CGraphPrototype extends CZBXAPI {

	protected $tableName = 'graphs';
	protected $tableAlias = 'g';

	/**
	 * Get GraphPrototype data
	 *
	 * @param array $options
	 * @return array
	 */
	public function get($options = array()) {
		$result = array();
		$userType = self::$userData['type'];
		$userid = self::$userData['userid'];

		// allowed columns for sorting
		$sortColumns = array('graphid', 'name', 'graphtype');

		// allowed output options for [ select_* ] params
		$subselectsAllowedOutputs = array(API_OUTPUT_REFER, API_OUTPUT_EXTEND, API_OUTPUT_CUSTOM);

		$sqlParts = array(
			'select'	=> array('graphs' => 'g.graphid'),
			'from'		=> array('graphs' => 'graphs g'),
			'where'		=> array('g.flags='.ZBX_FLAG_DISCOVERY_CHILD),
			'group'		=> array(),
			'order'		=> array(),
			'limit'		=> null
		);

		$defOptions = array(
			'nodeids'					=> null,
			'groupids'					=> null,
			'templateids'				=> null,
			'hostids'					=> null,
			'graphids'					=> null,
			'itemids'					=> null,
			'discoveryids'				=> null,
			'type'						=> null,
			'templated'					=> null,
			'inherited'					=> null,
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
			'selectTemplates'			=> null,
			'selectHosts'				=> null,
			'selectItems'				=> null,
			'selectGraphItems'			=> null,
			'selectDiscoveryRule'		=> null,
			'countOutput'				=> null,
			'groupCount'				=> null,
			'preservekeys'				=> null,
			'sortfield'					=> '',
			'sortorder'					=> '',
			'limit'						=> null
		);
		$options = zbx_array_merge($defOptions, $options);

		if (is_array($options['output'])) {
			unset($sqlParts['select']['graphs']);

			$dbTable = DB::getSchema('graphs');
			foreach ($options['output'] as $field) {
				if (isset($dbTable['fields'][$field])) {
					$sqlParts['select'][$field] = 'g.'.$field;
				}
			}
			$options['output'] = API_OUTPUT_CUSTOM;
		}

		// editable + PERMISSION CHECK
		if (USER_TYPE_SUPER_ADMIN == $userType || $options['nopermissions']) {
		}
		else {
			$permission = $options['editable'] ? PERM_READ_WRITE : PERM_READ_ONLY;

			$sqlParts['from']['graphs_items'] = 'graphs_items gi';
			$sqlParts['from']['items'] = 'items i';
			$sqlParts['from']['hosts_groups'] = 'hosts_groups hg';
			$sqlParts['from']['rights'] = 'rights r';
			$sqlParts['from']['users_groups'] = 'users_groups ug';
			$sqlParts['where']['gig'] = 'gi.graphid=g.graphid';
			$sqlParts['where']['igi'] = 'i.itemid=gi.itemid';
			$sqlParts['where']['hgi'] = 'hg.hostid=i.hostid';
			$sqlParts['where'][] = 'r.id=hg.groupid ';
			$sqlParts['where'][] = 'r.groupid=ug.usrgrpid';
			$sqlParts['where'][] = 'ug.userid='.$userid;
			$sqlParts['where'][] = 'r.permission>='.$permission;
			$sqlParts['where'][] = 'NOT EXISTS ('.
				' SELECT gii.graphid'.
				' FROM graphs_items gii,items ii'.
				' WHERE gii.graphid=g.graphid'.
					' AND gii.itemid=ii.itemid'.
					' AND EXISTS ('.
						' SELECT hgg.groupid'.
						' FROM hosts_groups hgg,rights rr,users_groups ugg'.
						' WHERE ii.hostid=hgg.hostid'.
							' AND rr.id=hgg.groupid'.
							' AND rr.groupid=ugg.usrgrpid'.
							' AND ugg.userid='.$userid.
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

			$sqlParts['from']['graphs_items'] = 'graphs_items gi';
			$sqlParts['from']['items'] = 'items i';
			$sqlParts['from']['hosts_groups'] = 'hosts_groups hg';

			$sqlParts['where'][] = DBcondition('hg.groupid', $options['groupids']);
			$sqlParts['where'][] = 'hg.hostid=i.hostid';
			$sqlParts['where']['gig'] = 'gi.graphid=g.graphid';
			$sqlParts['where']['igi'] = 'i.itemid=gi.itemid';
			$sqlParts['where']['hgi'] = 'hg.hostid=i.hostid';

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

			$sqlParts['from']['graphs_items'] = 'graphs_items gi';
			$sqlParts['from']['items'] = 'items i';
			$sqlParts['where'][] = DBcondition('i.hostid', $options['hostids']);
			$sqlParts['where']['gig'] = 'gi.graphid=g.graphid';
			$sqlParts['where']['igi'] = 'i.itemid=gi.itemid';

			if (!is_null($options['groupCount'])) {
				$sqlParts['group']['i'] = 'i.hostid';
			}
		}

		// graphids
		if (!is_null($options['graphids'])) {
			zbx_value2array($options['graphids']);

			$sqlParts['where'][] = DBcondition('g.graphid', $options['graphids']);
		}

		// itemids
		if (!is_null($options['itemids'])) {
			zbx_value2array($options['itemids']);
			if ($options['output'] != API_OUTPUT_SHORTEN) {
				$sqlParts['select']['itemid'] = 'gi.itemid';
			}
			$sqlParts['from']['graphs_items'] = 'graphs_items gi';
			$sqlParts['where']['gig'] = 'gi.graphid=g.graphid';
			$sqlParts['where'][] = DBcondition('gi.itemid', $options['itemids']);

			if (!is_null($options['groupCount'])) {
				$sqlParts['group']['gi'] = 'gi.itemid';
			}
		}

		// discoveryids
		if (!is_null($options['discoveryids'])) {
			zbx_value2array($options['discoveryids']);
			if ($options['output'] != API_OUTPUT_SHORTEN) {
				$sqlParts['select']['itemid'] = 'id.parent_itemid';
			}
			$sqlParts['from']['graphs_items'] = 'graphs_items gi';
			$sqlParts['from']['item_discovery'] = 'item_discovery id';
			$sqlParts['where']['gig'] = 'gi.graphid=g.graphid';
			$sqlParts['where']['giid'] = 'gi.itemid=id.itemid';
			$sqlParts['where'][] = DBcondition('id.parent_itemid', $options['discoveryids']);

			if (!is_null($options['groupCount'])) {
				$sqlParts['group']['id'] = 'id.parent_itemid';
			}
		}

		// type
		if (!is_null($options['type'] )) {
			$sqlParts['where'][] = 'g.type='.$options['type'];
		}

		// templated
		if (!is_null($options['templated'])) {
			$sqlParts['from']['graphs_items'] = 'graphs_items gi';
			$sqlParts['from']['items'] = 'items i';
			$sqlParts['from']['hosts'] = 'hosts h';
			$sqlParts['where']['igi'] = 'i.itemid=gi.itemid';
			$sqlParts['where']['ggi'] = 'g.graphid=gi.graphid';
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
				$sqlParts['where'][] = 'g.templateid IS NOT NULL';
			}
			else {
				$sqlParts['where'][] = 'g.templateid IS NULL';
			}
		}

		// output
		if ($options['output'] == API_OUTPUT_EXTEND) {
			$sqlParts['select']['graphs'] = 'g.*';
		}

		// countOutput
		if (!is_null($options['countOutput'])) {
			$options['sortfield'] = '';
			$sqlParts['select'] = array('count(DISTINCT g.graphid) as rowscount');

			// groupCount
			if (!is_null($options['groupCount'])) {
				foreach ($sqlParts['group'] as $key => $fields) {
					$sqlParts['select'][$key] = $fields;
				}
			}
		}

		// search
		if (is_array($options['search'])) {
			zbx_db_search('graphs g', $options, $sqlParts);
		}

		// filter
		if (is_array($options['filter'])) {
			zbx_db_filter('graphs g', $options, $sqlParts);

			if (isset($options['filter']['host'])) {
				zbx_value2array($options['filter']['host']);

				$sqlParts['from']['graphs_items'] = 'graphs_items gi';
				$sqlParts['from']['items'] = 'items i';
				$sqlParts['from']['hosts'] = 'hosts h';
				$sqlParts['where']['gig'] = 'gi.graphid=g.graphid';
				$sqlParts['where']['igi'] = 'i.itemid=gi.itemid';
				$sqlParts['where']['hi'] = 'h.hostid=i.hostid';
				$sqlParts['where']['host'] = DBcondition('h.host', $options['filter']['host']);
			}

			if (isset($options['filter']['hostid'])) {
				zbx_value2array($options['filter']['hostid']);

				$sqlParts['from']['graphs_items'] = 'graphs_items gi';
				$sqlParts['from']['items'] = 'items i';
				$sqlParts['where']['gig'] = 'gi.graphid=g.graphid';
				$sqlParts['where']['igi'] = 'i.itemid=gi.itemid';
				$sqlParts['where']['hostid'] = DBcondition('i.hostid', $options['filter']['hostid']);
			}
		}

		// sorting
		zbx_db_sorting($sqlParts, $options, $sortColumns, 'g');

		// limit
		if (zbx_ctype_digit($options['limit']) && $options['limit']) {
			$sqlParts['limit'] = $options['limit'];
		}

		$graphids = array();

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
				' WHERE '.DBin_node('g.graphid', $nodeids).
					$sqlWhere.
					$sqlGroup.
					$sqlOrder;

		$dbRes = DBselect($sql, $sqlLimit);
		while ($graph = DBfetch($dbRes)) {
			if (!is_null($options['countOutput'])) {
				if (!is_null($options['groupCount'])) {
					$result[] = $graph;
				}
				else {
					$result = $graph['rowscount'];
				}
			}
			else {
				$graphids[$graph['graphid']] = $graph['graphid'];

				if ($options['output'] == API_OUTPUT_SHORTEN) {
					$result[$graph['graphid']] = array('graphid' => $graph['graphid']);
				}
				else {
					if (!isset($result[$graph['graphid']])) {
						$result[$graph['graphid']]= array();
					}
					if (!is_null($options['selectHosts']) && !isset($result[$graph['graphid']]['hosts'])) {
						$result[$graph['graphid']]['hosts'] = array();
					}
					if (!is_null($options['selectGraphItems']) && !isset($result[$graph['graphid']]['gitems'])) {
						$result[$graph['graphid']]['gitems'] = array();
					}
					if (!is_null($options['selectTemplates']) && !isset($result[$graph['graphid']]['templates'])) {
						$result[$graph['graphid']]['templates'] = array();
					}
					if (!is_null($options['selectItems']) && !isset($result[$graph['graphid']]['items'])) {
						$result[$graph['graphid']]['items'] = array();
					}
					if (!is_null($options['selectDiscoveryRule']) && !isset($result[$graph['graphid']]['discoveryRule'])) {
						$result[$graph['graphid']]['discoveryRule'] = array();
					}

					// hostids
					if (isset($graph['hostid']) && is_null($options['selectHosts'])) {
						if (!isset($result[$graph['graphid']]['hosts'])) {
							$result[$graph['graphid']]['hosts'] = array();
						}
						$result[$graph['graphid']]['hosts'][] = array('hostid' => $graph['hostid']);
						unset($graph['hostid']);
					}

					// itemids
					if (isset($graph['itemid']) && is_null($options['selectItems'])) {
						if (!isset($result[$graph['graphid']]['items'])) {
							$result[$graph['graphid']]['items'] = array();
						}
						$result[$graph['graphid']]['items'][] = array('itemid' => $graph['itemid']);
						unset($graph['itemid']);
					}

					$result[$graph['graphid']] += $graph;
				}
			}
		}

		if (!is_null($options['countOutput'])) {
			return $result;
		}

		// adding GraphItems
		if (!is_null($options['selectGraphItems']) && str_in_array($options['selectGraphItems'], $subselectsAllowedOutputs)) {
			$gitems = API::GraphItem()->get(array(
				'nodeids' => $nodeids,
				'output' => $options['selectGraphItems'],
				'graphids' => $graphids,
				'nopermissions' => true,
				'preservekeys' => true
			));

			foreach ($gitems as $gitem) {
				$ggraphs = $gitem['graphs'];
				unset($gitem['graphs']);
				foreach ($ggraphs as $graph) {
					$result[$graph['graphid']]['gitems'][] = $gitem;
				}
			}
		}

		// adding Hostgroups
		if (!is_null($options['selectGroups'])) {
			if (is_array($options['selectGroups']) || str_in_array($options['selectGroups'], $subselectsAllowedOutputs)) {
				$groups = API::HostGroup()->get(array(
					'nodeids' => $nodeids,
					'output' => $options['selectGroups'],
					'graphids' => $graphids,
					'nopermissions' => true,
					'preservekeys' => true
				));

				foreach ($groups as $group) {
					$ggraphs = $group['graphs'];
					unset($group['graphs']);
					foreach ($ggraphs as $graph) {
						$result[$graph['graphid']]['groups'][] = $group;
					}
				}
			}
		}

		// adding Hosts
		if (!is_null($options['selectHosts'])) {
			if (is_array($options['selectHosts']) || str_in_array($options['selectHosts'], $subselectsAllowedOutputs)) {
				$hosts = API::Host()->get(array(
					'nodeids' => $nodeids,
					'output' => $options['selectHosts'],
					'graphids' => $graphids,
					'nopermissions' => true,
					'preservekeys' => true
				));
				foreach ($hosts as $host) {
					$hgraphs = $host['graphs'];
					unset($host['graphs']);
					foreach ($hgraphs as $graph) {
						$result[$graph['graphid']]['hosts'][] = $host;
					}
				}
			}
		}

		// adding Templates
		if (!is_null($options['selectTemplates']) && str_in_array($options['selectTemplates'], $subselectsAllowedOutputs)) {
			$templates = API::Template()->get(array(
				'nodeids' => $nodeids,
				'output' => $options['selectTemplates'],
				'graphids' => $graphids,
				'nopermissions' => true,
				'preservekeys' => true
			));
			foreach ($templates as $template) {
				$tgraphs = $template['graphs'];
				unset($template['graphs']);
				foreach ($tgraphs as $graph) {
					$result[$graph['graphid']]['templates'][] = $template;
				}
			}
		}

		// adding Items
		if (!is_null($options['selectItems']) && str_in_array($options['selectItems'], $subselectsAllowedOutputs)) {
			$items = API::Item()->get(array(
				'nodeids' => $nodeids,
				'output' => $options['selectItems'],
				'graphids' => $graphids,
				'nopermissions' => true,
				'preservekeys' => true
			));
			foreach ($items as $item) {
				$igraphs = $item['graphs'];
				unset($item['graphs']);
				foreach ($igraphs as $graph) {
					$result[$graph['graphid']]['items'][] = $item;
				}
			}
		}

		// adding discoveryRule
		if (!is_null($options['selectDiscoveryRule'])) {
			$ruleids = $ruleMap = array();

			$dbRules = DBselect(
				'SELECT id.parent_itemid,gi.graphid'.
					' FROM item_discovery id,graphs_items gi'.
					' WHERE '.DBcondition('gi.graphid', $graphids).
						' AND gi.itemid=id.itemid'
			);
			while ($rule = DBfetch($dbRules)) {
				$ruleids[$rule['parent_itemid']] = $rule['parent_itemid'];
				$ruleMap[$rule['graphid']] = $rule['parent_itemid'];
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

				foreach ($result as $graphid => $graph) {
					if (isset($ruleMap[$graphid]) && isset($discoveryRules[$ruleMap[$graphid]])) {
						$result[$graphid]['discoveryRule'] = $discoveryRules[$ruleMap[$graphid]];
					}
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
	 * Get graphid by graph name
	 *
	 * params: hostids, name
	 *
	 * @param array $graphData
	 * @return string|boolean
	 */
	public function getObjects($graphData) {
		$options = array(
			'filter' => $graphData,
			'output' => API_OUTPUT_EXTEND
		);
		if (isset($graphData['node'])) {
			$options['nodeids'] = getNodeIdByNodeName($graphData['node']);
		}
		elseif (isset($graphData['nodeids'])) {
			$options['nodeids'] = $graphData['nodeids'];
		}
		return $this->get($options);
	}

	public function exists($object) {
		$options = array(
			'filter' => array('flags' => null),
			'output' => API_OUTPUT_SHORTEN,
			'nopermissions' => true,
			'limit' => 1
		);
		if (isset($object['name'])) {
			$options['filter']['name'] = $object['name'];
		}
		if (isset($object['host'])) {
			$options['filter']['host'] = $object['host'];
		}
		if (isset($object['hostids'])) {
			$options['hostids'] = zbx_toArray($object['hostids']);
		}

		if (isset($object['node'])) {
			$options['nodeids'] = getNodeIdByNodeName($object['node']);
		}
		elseif (isset($object['nodeids'])) {
			$options['nodeids'] = $object['nodeids'];
		}

		$objs = $this->get($options);

		return !empty($objs);
	}

	/**
	 * Create new GraphPrototypes
	 *
	 * @param array $graphs
	 * @return boolean
	 */
	public function create($graphs) {
		$graphs = zbx_toArray($graphs);
		$graphids = array();

		$this->checkInput($graphs, false);

		foreach ($graphs as $graph) {
			$graphHosts = API::Host()->get(array(
				'itemids' => zbx_objectValues($graph['gitems'], 'itemid'),
				'output' => API_OUTPUT_EXTEND,
				'editable' => true,
				'templated_hosts' => true
			));

			// check - items from one template
			$templatedGraph = false;
			foreach ($graphHosts as $host) {
				if (HOST_STATUS_TEMPLATE == $host['status']) {
					$templatedGraph = $host['hostid'];
					break;
				}
			}
			if ($templatedGraph && count($graphHosts) > 1) {
				self::exception(ZBX_API_ERROR_PARAMETERS, _s('Graph prototype "%1$s" with template host cannot contain items from other hosts.', $graph['name']));
			}

			// check ymin, ymax items
			$this->checkAxisItems($graph, $templatedGraph);

			$graphid = $this->createReal($graph);

			if ($templatedGraph) {
				$graph['graphid'] = $graphid;
				$this->inherit($graph);
			}

			$graphids[] = $graphid;
		}

		return array('graphids' => $graphids);
	}

	/**
	 * Update existing GraphPrototype
	 *
	 * @param array $graphs
	 * @return boolean
	 */
	public function update($graphs) {
		$graphs = zbx_toArray($graphs);
		$graphids = zbx_objectValues($graphs, 'graphid');

		$updGraphs = $this->get(array(
			'graphids' => $graphids,
			'editable' => true,
			'preservekeys' => true,
			'output' => API_OUTPUT_SHORTEN,
			'selectGraphItems' => API_OUTPUT_EXTEND
		));

		foreach ($graphs as $gnum => $graph) {
			if (!isset($updGraphs[$graph['graphid']])) {
				self::exception(ZBX_API_ERROR_PARAMETERS, _('No permissions to referred object or it does not exist!'));
			}
			if (!isset($graph['gitems'])) {
				$graphs[$gnum]['gitems'] = $updGraphs[$graph['graphid']]['gitems'];
			}
		}

		$this->checkInput($graphs, true);

		foreach ($graphs as $graph) {
			unset($graph['templateid']);

			$graphHosts = API::Host()->get(array(
				'itemids' => zbx_objectValues($graph['gitems'], 'itemid'),
				'output' => API_OUTPUT_EXTEND,
				'editable' => true,
				'templated_hosts' => true
			));

			// mass templated items
			$templatedGraph = false;
			foreach ($graphHosts as $host) {
				if (HOST_STATUS_TEMPLATE == $host['status']) {
					$templatedGraph = $host['hostid'];
					break;
				}
			}
			if ($templatedGraph && count($graphHosts) > 1) {
				self::exception(ZBX_API_ERROR_PARAMETERS, _s('Graph prototype "%1$s" with template host cannot contain items from other hosts.', $graph['name']));
			}

			// check ymin, ymax items
			$this->checkAxisItems($graph, $templatedGraph);

			$this->updateReal($graph);

			// inheritance
			if ($templatedGraph) $this->inherit($graph);
		}

		return array('graphids' => $graphids);
	}

	protected function createReal($graph) {
		$graph['flags'] = ZBX_FLAG_DISCOVERY_CHILD;

		$graphids = DB::insert('graphs', array($graph));
		$graphid = reset($graphids);

		foreach ($graph['gitems'] as &$gitem) {
			$gitem['graphid'] = $graphid;
		}
		unset($gitem);

		DB::insert('graphs_items', $graph['gitems']);

		return $graphid;
	}

	protected function updateReal($graph) {
		$data = array(array('values' => $graph, 'where' => array('graphid' => $graph['graphid'])));
		DB::update('graphs', $data);

		if (isset($graph['gitems'])) {
			DB::delete('graphs_items', array('graphid' => $graph['graphid']));

			foreach ($graph['gitems'] as $gitem) {
				$gitem['graphid'] = $graph['graphid'];

				DB::insert('graphs_items', array($gitem));
			}
		}

		return $graph['graphid'];
	}

	protected function inherit($graph, $hostids = null) {
		$graphTemplates = API::Template()->get(array(
			'itemids' => zbx_objectValues($graph['gitems'], 'itemid'),
			'output' => API_OUTPUT_SHORTEN,
			'nopermissions' => true
		));
		if (empty($graphTemplates)) {
			return true;
		}

		$graphTemplate = reset($graphTemplates);

		$chdHosts = API::Host()->get(array(
			'templateids' => $graphTemplate['templateid'],
			'output' => array('hostid', 'host'),
			'preservekeys' => true,
			'hostids' => $hostids,
			'nopermissions' => true,
			'templated_hosts' => true
		));

		$graph = $this->get(array(
			'graphids' => $graph['graphid'],
			'nopermissions' => true,
			'filter' => array('flags' => null),
			'selectItems' => API_OUTPUT_EXTEND,
			'selectGraphItems' => API_OUTPUT_EXTEND,
			'output' => API_OUTPUT_EXTEND
		));
		$graph = reset($graph);

		foreach ($chdHosts as $chdHost) {
			$tmpGraph = $graph;
			$tmpGraph['templateid'] = $graph['graphid'];

			if (!$tmpGraph['gitems'] = get_same_graphitems_for_host($tmpGraph['gitems'], $chdHost['hostid'])) {
				self::exception(ZBX_API_ERROR_PARAMETERS, _s('Graph "%1$s" cannot inherit. No required items on "%2$s".', $tmpGraph['name'], $chdHost['host']));
			}

			if ($tmpGraph['ymax_itemid'] > 0) {
				$ymaxItemid = get_same_graphitems_for_host(array(array('itemid' => $tmpGraph['ymax_itemid'])), $chdHost['hostid']);
				if (!$ymaxItemid) {
					self::exception(ZBX_API_ERROR_PARAMETERS, _s('Graph "%1$s" cannot inherit. No required items on "%2$s" (Ymax value item).', $tmpGraph['name'], $chdHost['host']));
				}
				$ymaxItemid = reset($ymaxItemid);
				$tmpGraph['ymax_itemid'] = $ymaxItemid['itemid'];
			}
			if ($tmpGraph['ymin_itemid'] > 0) {
				$yminItemid = get_same_graphitems_for_host(array(array('itemid' => $tmpGraph['ymin_itemid'])), $chdHost['hostid']);
				if (!$yminItemid) {
					self::exception(ZBX_API_ERROR_PARAMETERS, _s('Graph "%1$s" cannot inherit. No required items on "%2$s" (Ymin value item).', $tmpGraph['name'], $chdHost['host']));
				}
				$yminItemid = reset($yminItemid);
				$tmpGraph['ymin_itemid'] = $yminItemid['itemid'];
			}

			// check if templated graph exists
			$chdGraphs = $this->get(array(
				'filter' => array('templateid' => $tmpGraph['graphid'], 'flags' => array(ZBX_FLAG_DISCOVERY_CHILD, ZBX_FLAG_DISCOVERY_NORMAL)),
				'output' => API_OUTPUT_EXTEND,
				'preservekeys' => true,
				'hostids' => $chdHost['hostid']
			));

			if ($chdGraph = reset($chdGraphs)) {
				if (zbx_strtolower($tmpGraph['name']) != zbx_strtolower($chdGraph['name'])
						&& $this->exists(array('name' => $tmpGraph['name'], 'hostids' => $chdHost['hostid']))) {
					self::exception(ZBX_API_ERROR_PARAMETERS, _s('Graph "%1$s" already exists on "%2$s".', $tmpGraph['name'], $chdHost['host']));
				}
				elseif ($chdGraph['flags'] != $tmpGraph['flags']) {
					self::exception(ZBX_API_ERROR_PARAMETERS, _('Graph with same name but other type exist.'));
				}

				$tmpGraph['graphid'] = $chdGraph['graphid'];
				$this->updateReal($tmpGraph);
			}
			// check if graph with same name and items exists
			else {
				$chdGraph = $this->get(array(
					'filter' => array('name' => $tmpGraph['name'], 'flags' => null),
					'output' => API_OUTPUT_EXTEND,
					'preservekeys' => true,
					'nopermissions' => true,
					'hostids' => $chdHost['hostid']
				));
				if ($chdGraph = reset($chdGraph)) {
					if ($chdGraph['templateid'] != 0) {
						self::exception(ZBX_API_ERROR_PARAMETERS, _s('Graph "%1$s" already exists on "%2$s" (inherited from another template).', $tmpGraph['name'], $chdHost['host']));
					}
					elseif ($chdGraph['flags'] != $tmpGraph['flags']) {
						self::exception(ZBX_API_ERROR_PARAMETERS, _('Graph with same name but other type exist.'));
					}

					$chdGraphItems = API::GraphItem()->get(array(
						'graphids' => $chdGraph['graphid'],
						'output' => API_OUTPUT_EXTEND,
						'preservekeys' => true,
						'expandData' => true,
						'nopermissions' => true
					));

					if (count($chdGraphItems) == count($tmpGraph['gitems'])) {
						foreach ($tmpGraph['gitems'] as $gitem) {
							foreach ($chdGraphItems as $chdItem) {
								if ($gitem['key_'] == $chdItem['key_'] && bccomp($chdHost['hostid'], $chdItem['hostid']) == 0) {
									continue 2;
								}
							}

							self::exception(ZBX_API_ERROR_PARAMETERS, _s('Graph "%1$s" already exists on "%2$s" (items are not identical).', $tmpGraph['name'], $chdHost['host']));
						}

						$tmpGraph['graphid'] = $chdGraph['graphid'];
						$this->updateReal($tmpGraph);
					}
					else {
						self::exception(ZBX_API_ERROR_PARAMETERS, _s('Graph "%1$s" already exists on "%2$s" (items are not identical).', $tmpGraph['name'], $chdHost['host']));
					}
				}
				else {
					$graphid = $this->createReal($tmpGraph);
					$tmpGraph['graphid'] = $graphid;
				}
			}
			$this->inherit($tmpGraph);
		}
	}

	/**
	 * Inherit template graphs from template to host
	 *
	 * @param array $data
	 * @return boolean
	 */
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
				self::exception(ZBX_API_ERROR_PERMISSIONS, _('You do not have permission to perform this operation.'));
			}
		}

		$allowedTemplates = API::Template()->get(array(
			'templateids' => $data['templateids'],
			'preservekeys' => true,
			'output' => API_OUTPUT_SHORTEN
		));
		foreach ($data['templateids'] as $templateid) {
			if (!isset($allowedTemplates[$templateid])) {
				self::exception(ZBX_API_ERROR_PERMISSIONS, _('You do not have permission to perform this operation.'));
			}
		}

		$dbLinks = DBSelect(
			'SELECT ht.hostid,ht.templateid'.
			' FROM hosts_templates ht'.
			' WHERE '.DBcondition('ht.hostid', $data['hostids']).
				' AND '.DBcondition('ht.templateid', $data['templateids'])
		);
		$linkage = array();
		while ($link = DBfetch($dbLinks)) {
			if (!isset($linkage[$link['templateid']])) {
				$linkage[$link['templateid']] = array();
			}
			$linkage[$link['templateid']][$link['hostid']] = 1;
		}

		$graphs = $this->get(array(
			'hostids' => $data['templateids'],
			'preservekeys' => true,
			'output' => API_OUTPUT_EXTEND,
			'selectGraphItems' => API_OUTPUT_EXTEND,
			'filter' => array('flags' => null)
		));

		foreach ($graphs as $graph) {
			foreach ($data['hostids'] as $hostid) {
				if (isset($linkage[$graph['hosts'][0]['hostid']][$hostid])) {
					$this->inherit($graph, $hostid);
				}
			}
		}

		return true;
	}

	/**
	 * Delete GraphPrototype
	 *
	 * @param array $graphs
	 * @param array $graphs['graphids']
	 * @return array
	 */
	public function delete($graphids, $nopermissions = false) {
		if (empty($graphids)) {
			self::exception(ZBX_API_ERROR_PARAMETERS, _('Empty input parameter.'));
		}

		$graphids = zbx_toArray($graphids);

		$delGraphs = $this->get(array(
			'graphids' => $graphids,
			'editable' => true,
			'output' => API_OUTPUT_EXTEND,
			'preservekeys' => true
		));

		if (!$nopermissions) {
			foreach ($graphids as $graphid) {
				if (!isset($delGraphs[$graphid])) {
					self::exception(ZBX_API_ERROR_PERMISSIONS, _('You do not have permission to perform this operation.'));
				}
				if ($delGraphs[$graphid]['templateid'] != 0) {
					self::exception(ZBX_API_ERROR_PERMISSIONS, _('Cannot delete templated graphs.'));
				}
			}
		}

		$parentGraphids = $graphids;
		do {
			$dbGraphs = DBselect('SELECT g.graphid FROM graphs g WHERE '.DBcondition('g.templateid', $parentGraphids));
			$parentGraphids = array();
			while ($dbGraph = DBfetch($dbGraphs)) {
				$parentGraphids[] = $dbGraph['graphid'];
				$graphids[$dbGraph['graphid']] = $dbGraph['graphid'];
			}
		} while (!empty($parentGraphids));

		$createdGraphs = array();

		$dbGraphs = DBselect('SELECT gd.graphid FROM graph_discovery gd WHERE '.DBcondition('gd.parent_graphid', $graphids));
		while ($graph = DBfetch($dbGraphs)) {
			$createdGraphs[$graph['graphid']] = $graph['graphid'];
		}
		if (!empty($createdGraphs)) {
			$result = API::Graph()->delete($createdGraphs, true);
			if (!$result) {
				self::exception(ZBX_API_ERROR_PARAMETERS, _('Cannot delete graph prototype.'));
			}
		}

		DB::delete('screens_items', array(
			'resourceid' => $graphids,
			'resourcetype' => SCREEN_RESOURCE_GRAPH
		));

		DB::delete('profiles', array(
			'idx' => 'web.favorite.graphids',
			'source' => 'graphid',
			'value_id' => $graphids
		));

		DB::delete('graphs', array(
			'graphid' => $graphids
		));

		foreach ($delGraphs as $graph) {
			info(_s('Graph prototype "%s" deleted.', $graph['name']));
		}

		return array('graphids' => $graphids);
	}

	private function checkInput($graphs, $update = false) {
		$itemids = array();

		foreach ($graphs as $graph) {
			// graph fields
			$fields = array('name' => null);
			if (!$update && !check_db_fields($fields, $graph)) {
				self::exception(ZBX_API_ERROR_PARAMETERS, _('Wrong fields for graph.'));
			}

			// no items
			if (!isset($graph['gitems']) || !is_array($graph['gitems']) || empty($graph['gitems'])) {
				self::exception(ZBX_API_ERROR_PARAMETERS, _('Missing items for graph "%1$s".', $graph['name']));
			}

			// items fields
			$fields = array('itemid' => null);
			foreach ($graph['gitems'] as $gitem) {
				if (!check_db_fields($fields, $gitem)) {
					self::exception(ZBX_API_ERROR_PARAMETERS, _('Wrong fields for items.'));
				}

				$itemids[$gitem['itemid']] = $gitem['itemid'];
			}

			// more than one sum type item for pie graph
			if ($graph['graphtype'] == GRAPH_TYPE_PIE || $graph['graphtype'] == GRAPH_TYPE_EXPLODED) {
				$sumItems = 0;
				foreach ($graph['gitems'] as $gitem) {
					if ($gitem['type'] == GRAPH_ITEM_SUM) {
						$sumItems++;
					}
				}
				if ($sumItems > 1) {
					self::exception(ZBX_API_ERROR_PARAMETERS, _s('Cannot add more than one item with type "Graph sum" on graph "%1$s".', $graph['name']));
				}
			}
		}

		if (!empty($itemids)) {
			$allowedItems = API::Item()->get(array(
				'nodeids' => get_current_nodeid(true),
				'itemids' => array_unique($itemids),
				'webitems' => true,
				'editable' => true,
				'output' => API_OUTPUT_EXTEND,
				'preservekeys' => true
			));

			foreach ($itemids as $itemid) {
				if (!isset($allowedItems[$itemid])) {
					self::exception(ZBX_API_ERROR_PARAMETERS, _('No permissions to referred object or it does not exist!'));
				}
			}
		}

		foreach ($graphs as $graph) {
			// check if the graph has at least one prototype
			$hasPrototype = false;
			foreach ($graph['gitems'] as $gitem) {
				if ($allowedItems[$gitem['itemid']]['flags'] == ZBX_FLAG_DISCOVERY_CHILD) {
					$hasPrototype = true;
					break;
				}
			}
			if (!$hasPrototype) {
				self::exception(ZBX_API_ERROR_PARAMETERS, _('Graph prototype must have at least one prototype.'));
			}

			// check if the host has any graphs with the same name
			$hosts = API::Host()->get(array(
				'itemids' => zbx_objectValues($graph['gitems'], 'itemid'),
				'output' => API_OUTPUT_SHORTEN,
				'nopermissions' => true,
				'preservekeys' => true
			));

			$graphsExists = API::Graph()->get(array(
				'hostids' => array_keys($hosts),
				'output' => API_OUTPUT_SHORTEN,
				'filter' => array('name' => $graph['name'], 'flags' => null),
				'nopermissions' => true,
				'limit' => 1
			));
			foreach ($graphsExists as $graphExists) {
				if (!$update || bccomp($graphExists['graphid'], $graph['graphid']) != 0) {
					self::exception(ZBX_API_ERROR_PARAMETERS, _s('Graph with name "%1$s" already exists.', $graph['name']));
				}
			}
		}

		return true;
	}

	protected function checkAxisItems($graph, $tpl = false) {
		$axisItems = array();
		if (isset($graph['ymin_type']) && $graph['ymin_type'] == GRAPH_YAXIS_TYPE_ITEM_VALUE) {
			$axisItems[$graph['ymin_itemid']] = $graph['ymin_itemid'];
		}
		if (isset($graph['ymax_type']) && $graph['ymax_type'] == GRAPH_YAXIS_TYPE_ITEM_VALUE) {
			$axisItems[$graph['ymax_itemid']] = $graph['ymax_itemid'];
		}

		if (!empty($axisItems)) {
			$options = array(
				'itemids' => $axisItems,
				'output' => API_OUTPUT_SHORTEN,
				'countOutput' => 1
			);
			if ($tpl) {
				$options['hostids'] = $tpl;
			}
			else {
				$options['templated'] = false;
			}

			$cntExist = API::Item()->get($options);

			if ($cntExist != count($axisItems)) {
				self::exception(ZBX_API_ERROR_PARAMETERS, _('Incorrect item for axis value.'));
			}
		}

		return true;
	}
}
?>
