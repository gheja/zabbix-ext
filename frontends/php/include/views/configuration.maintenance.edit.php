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
$maintenanceWidget = new CWidget();
$maintenanceWidget->addPageHeader(_('CONFIGURATION OF MAINTENANCE PERIODS'));

// create form
$maintenanceForm = new CForm();
$maintenanceForm->setName('maintenanceForm');
$maintenanceForm->addVar('form', $this->data['form']);
if (isset($this->data['maintenanceid'])) {
	$maintenanceForm->addVar('maintenanceid', $this->data['maintenanceid']);
}

/*
 * Maintenance tab
 */
$maintenanceFormList = new CFormList('maintenanceFormList');
$maintenanceFormList->addRow(_('Name'), new CTextBox('mname', $this->data['mname'], ZBX_TEXTBOX_STANDARD_SIZE));
$typeComboBox = new CComboBox('maintenance_type', $this->data['maintenance_type']);
$typeComboBox->addItem(MAINTENANCE_TYPE_NORMAL, _('With data collection'));
$typeComboBox->addItem(MAINTENANCE_TYPE_NODATA, _('No data collection'));
$maintenanceFormList->addRow(_('Maintenance type'), $typeComboBox);
$maintenanceForm->addVar('active_since', date('YmdHi', $this->data['active_since']));
$maintenanceForm->addVar('active_till', date('YmdHi', $this->data['active_till']));

$calendarIcon = new CImg('images/general/bar/cal.gif', 'calendar', 16, 12, 'pointer');
$calendarIcon->addAction('onclick', 'javascript: var pos = getPosition(this); pos.top += 10; pos.left += 16; CLNDR["mntc_active_since"].clndr.clndrshow(pos.top, pos.left); CLNDR["mntc_active_till"].clndr.clndrhide();');
$maintenanceSinceDay = new CNumericBox('mntc_since_day', $this->data['active_since'] > 0 ? date('d', $this->data['active_since']) : '', 2);
$maintenanceSinceDay->setAttribute('placeholder', _('dd'));
$maintenanceSinceMonth = new CNumericBox('mntc_since_month', $this->data['active_since'] > 0 ? date('m', $this->data['active_since']) : '', 2);
$maintenanceSinceMonth->setAttribute('placeholder', _('mm'));
$maintenanceSinceYear = new CNumericBox('mntc_since_year', $this->data['active_since'] > 0 ? date('Y', $this->data['active_since']) : '', 4);
$maintenanceSinceYear->setAttribute('placeholder', _('yyyy'));
$maintenanceSinceHour = new CNumericBox('mntc_since_hour', $this->data['active_since'] > 0 ? date('H', $this->data['active_since']) : '', 2);
$maintenanceSinceHour->setAttribute('placeholder', _('hh'));
$maintenanceSinceMinute = new CNumericBox('mntc_since_minute', $this->data['active_since'] > 0 ? date('i', $this->data['active_since']) : '', 2);
$maintenanceSinceMinute->setAttribute('placeholder', _('mm'));

$maintenanceFormList->addRow(_('Active since'), array($maintenanceSinceDay, '/', $maintenanceSinceMonth, '/', $maintenanceSinceYear, SPACE, $maintenanceSinceHour, ':', $maintenanceSinceMinute, $calendarIcon));
zbx_add_post_js('create_calendar(null, ["mntc_since_day", "mntc_since_month", "mntc_since_year", "mntc_since_hour", "mntc_since_minute"], "mntc_active_since", "active_since");');

$calendarIcon->addAction('onclick', 'javascript: var pos = getPosition(this); pos.top += 10; pos.left += 16; CLNDR["mntc_active_till"].clndr.clndrshow(pos.top, pos.left); CLNDR["mntc_active_since"].clndr.clndrhide();');
$maintenanceTillDay = new CNumericBox('mntc_till_day', $this->data['active_since'] > 0 ? date('d', $this->data['active_since']) : '', 2);
$maintenanceTillDay->setAttribute('placeholder', _('dd'));
$maintenanceTillMonth = new CNumericBox('mntc_till_month', $this->data['active_till'] > 0 ? date('m', $this->data['active_till']) : '', 2);
$maintenanceTillMonth->setAttribute('placeholder', _('mm'));
$maintenanceTillYear = new CNumericBox('mntc_till_year', $this->data['active_till'] > 0 ? date('Y', $this->data['active_till']) : '', 4);
$maintenanceTillYear->setAttribute('placeholder', _('yyyy'));
$maintenanceTillHour = new CNumericBox('mntc_till_hour', $this->data['active_till'] > 0 ? date('H', $this->data['active_till']) : '', 2);
$maintenanceTillHour->setAttribute('placeholder', _('hh'));
$maintenanceTillMinute = new CNumericBox('mntc_till_minute', $this->data['active_till'] > 0 ? date('i', $this->data['active_till']) : '', 2);
$maintenanceTillMinute->setAttribute('placeholder', _('mm'));

$maintenanceFormList->addRow(_('Active till'), array($maintenanceTillDay, '/', $maintenanceTillMonth, '/', $maintenanceTillYear, SPACE, $maintenanceTillHour, ':', $maintenanceTillMinute, $calendarIcon));
zbx_add_post_js('create_calendar(null, ["mntc_till_day", "mntc_till_month", "mntc_till_year", "mntc_till_hour", "mntc_till_minute"], "mntc_active_till", "active_till");');

$maintenanceFormList->addRow(_('Description'), new CTextArea('description', $this->data['description']));

/*
 * Maintenance period tab
 */
$maintenancePeriodFormList = new CFormList('maintenancePeriodFormList');
$maintenancePeriodTable = new CTableInfo(_('No maintenance period defined.'));
$maintenancePeriodTable->setHeader(array(
	new CCheckBox('all_periods', null, 'checkAll("'.$maintenanceForm->getName().'", "all_periods", "g_timeperiodid");'),
	_('Period type'),
	_('Schedule'),
	_('Period'),
	_('Action')
));

foreach ($this->data['timeperiods'] as $id => $timeperiod) {
	$maintenancePeriodTable->addRow(array(
		new CCheckBox('g_timeperiodid[]', 'no', null, $id),
		timeperiod_type2str($timeperiod['timeperiod_type']),
		new CCol(shedule2str($timeperiod), 'wraptext'),
		zbx_date2age(0, $timeperiod['period']),
		new CSubmit('edit_timeperiodid['.$id.']', _('Edit'), null, 'link_menu')
	));
	$maintenanceForm->addVar('timeperiods['.$id.'][timeperiod_type]', $timeperiod['timeperiod_type']);
	$maintenanceForm->addVar('timeperiods['.$id.'][every]', $timeperiod['every']);
	$maintenanceForm->addVar('timeperiods['.$id.'][month]', $timeperiod['month']);
	$maintenanceForm->addVar('timeperiods['.$id.'][dayofweek]', $timeperiod['dayofweek']);
	$maintenanceForm->addVar('timeperiods['.$id.'][day]', $timeperiod['day']);
	$maintenanceForm->addVar('timeperiods['.$id.'][start_time]', $timeperiod['start_time']);
	$maintenanceForm->addVar('timeperiods['.$id.'][start_date]', $timeperiod['start_date']);
	$maintenanceForm->addVar('timeperiods['.$id.'][period]', $timeperiod['period']);
}

$maintenancePeriodFormList->addRow(_('Periods'),
	array(
		$maintenancePeriodTable,
		new CSubmit('new_timeperiod', _('New'), null, 'link_menu'),
		SPACE,
		SPACE,
		new CSubmit('del_timeperiod', _('Delete selected'), null, 'link_menu')
	)
);

if (isset($_REQUEST['new_timeperiod'])) {
	$label = (is_array($_REQUEST['new_timeperiod']) && isset($_REQUEST['new_timeperiod']['id'])) ? _('Edit maintenance period') : _('New maintenance period');
	$maintenancePeriodFormList->addRow(SPACE, array(BR(), create_hat($label, get_timeperiod_form(), null, 'hat_new_timeperiod')));
}

/*
 * Hosts & groups tab
 */
$hostsAndGroupsFormList = new CFormList('hostsAndGroupsFormList');
$hostTweenBox = new CTweenBox($maintenanceForm, 'hostids', $this->data['hostids'], 10);
foreach ($this->data['hosts'] as $host) {
	$hostTweenBox->addItem($host['hostid'], $host['name']);
}
$groupsComboBox = new CComboBox('twb_groupid', $this->data['twb_groupid'], 'submit()');
foreach ($this->data['all_groups'] as $group) {
	$groupsComboBox->addItem($group['groupid'], $group['name']);
}
$hostTable = new CTable(null, 'formElementTable');
$hostTable->addRow($hostTweenBox->get(_('In maintenance'), array(_('Other hosts | Group').SPACE, $groupsComboBox)));
$hostsAndGroupsFormList->addRow(_('Hosts in maintenance'), $hostTable);

$groupTable = new CTable(null, 'formElementTable');
$groupTweenBox = new CTweenBox($maintenanceForm, 'groupids', $this->data['groupids'], 10);
foreach ($this->data['all_groups'] as $group) {
	$groupTweenBox->addItem($group['groupid'], $group['name']);
}
$groupTable->addRow($groupTweenBox->get(_('In maintenance'), _('Other groups')));

$hostsAndGroupsFormList->addRow(_('Groups in maintenance'), $groupTable);

// append tabs to form
$maintenanceTab = new CTabView(array('remember' => true));
if (!$this->data['form_refresh']) {
	$maintenanceTab->setSelected(0);
}
$maintenanceTab->addTab('maintenanceTab', _('Maintenance'), $maintenanceFormList);
$maintenanceTab->addTab('periodsTab', _('Periods'), $maintenancePeriodFormList);
$maintenanceTab->addTab('hostTab', _('Hosts & Groups'), $hostsAndGroupsFormList);
$maintenanceForm->addItem($maintenanceTab);

// append buttons to form
if (empty($this->data['maintenanceid'])) {
	$maintenanceForm->addItem(makeFormFooter(
		array(new CSubmit('save', _('Save'))),
		array(new CButtonCancel())
	));
}
else {
	$maintenanceForm->addItem(makeFormFooter(
		array(new CSubmit('save', _('Save'))),
		array(
			new CSubmit('clone', _('Clone')),
			new CButtonDelete(_('Delete maintenance period?'), url_param('form').url_param('maintenanceid')),
			new CButtonCancel())
	));
}

$maintenanceWidget->addItem($maintenanceForm);
return $maintenanceWidget;
?>
