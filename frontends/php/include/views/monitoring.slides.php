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
$slideWidget = new CWidget('hat_slides');

// create header form
$slideHeaderForm = new CForm('get');
$slideHeaderForm->setName('slideHeaderForm');

$configComboBox = new CComboBox('config', 'slides.php', 'javascript: redirect(this.options[this.selectedIndex].value);');
$configComboBox->addItem('screens.php', _('Screens'));
$configComboBox->addItem('slides.php', _('Slide shows'));
$slideHeaderForm->addItem($configComboBox);

if (empty($this->data['slideshows'])) {
	$slideWidget->addPageHeader(_('SLIDE SHOWS'), $slideHeaderForm);
	$slideWidget->addItem(BR());
	$slideWidget->addItem(new CTableInfo(_('No slide shows defined.')));
}
else {
	$effectiveperiod = navigation_bar_calc('web.slides', $this->data['elementid'], true);

	$favouriteIcon = $this->data['screen']
		? get_icon('favourite', array('fav' => 'web.favorite.screenids', 'elname' => 'slideshowid', 'elid' => $this->data['elementid']))
		: new CIcon(_('Favourites'), 'iconplus');

	$refreshIcon = new CIcon(_('Menu'), 'iconmenu');
	if (!empty($this->data['screen'])) {
		$refreshIcon->addAction('onclick', 'javascript: create_page_menu(event, \'hat_slides\');');
	}

	$slideWidget->addPageHeader(
		_('SLIDE SHOWS'),
		array(
			$slideHeaderForm,
			SPACE,
			$favouriteIcon,
			$refreshIcon,
			get_icon('fullscreen', array('fullscreen' => $this->data['fullscreen']))
		)
	);

	$slideForm = new CForm('get');
	$slideForm->setName('slideForm');
	$slideForm->addVar('fullscreen', $this->data['fullscreen']);

	$elementsComboBox = new CComboBox('elementid', $this->data['elementid'], 'submit()');
	foreach ($this->data['slideshows'] as $slideshow) {
		$elementsComboBox->addItem($slideshow['slideshowid'], get_node_name_by_elid($slideshow['slideshowid'], null, ': ').$slideshow['name']);
	}
	$slideForm->addItem(array(_('Slide show').SPACE, $elementsComboBox));

	$slideWidget->addHeader($this->data['slideshows'][$this->data['elementid']]['name'], $slideForm);

	if (!empty($this->data['screen'])) {
		// append groups to form
		if (!empty($this->data['page_groups'])) {
			$groupsComboBox = new CComboBox('groupid', $this->data['page_groups']['selected'], 'javascript: submit();');
			foreach ($this->data['page_groups']['groups'] as $groupid => $name) {
				$groupsComboBox->addItem($groupid, get_node_name_by_elid($groupid, null, ': ').$name);
			}
			$slideForm->addItem(array(SPACE._('Group').SPACE, $groupsComboBox));
		}

		// append hosts to form
		if (!empty($this->data['page_hosts'])) {
			$this->data['page_hosts']['hosts']['0'] = _('Default');
			$hostsComboBox = new CComboBox('hostid', $this->data['page_hosts']['selected'], 'javascript: submit();');
			foreach ($this->data['page_hosts']['hosts'] as $hostid => $name) {
				$hostsComboBox->addItem($hostid, get_node_name_by_elid($hostid, null, ': ').$name);
			}
			$slideForm->addItem(array(SPACE._('Host').SPACE, $hostsComboBox));
		}

		// js menu
		insert_js('var page_menu='.zbx_jsvalue($this->data['menu']).";\n".'var page_submenu='.zbx_jsvalue($this->data['submenu']).";\n");

		$refresh_tab = array(array(
			'id' => 'hat_slides',
			'frequency' => $this->data['element']['delay'] * $this->data['refresh_multiplier'],
			'url' => 'slides.php?elementid='.$this->data['elementid'].(is_null($this->data['tmpstime']) ? '' : '&stime='.$this->data['tmpstime']).url_param('period').url_param('groupid').url_param('hostid'),
			'params'=> array('lastupdate' => time())
		));
		add_doll_objects($refresh_tab);

		$effectiveperiod = navigation_bar_calc();
		if ($this->data['fullscreen'] != 2) {
			$timeline = array();
			$timeline['period'] = $effectiveperiod;
			$timeline['starttime'] = date('YmdHis', time() - ZBX_MAX_PERIOD);

			if (isset($_REQUEST['stime'])) {
				$timeline['usertime'] = date('YmdHis', zbxDateToTime($_REQUEST['stime']) + $timeline['period']);
			}

			$scrollDiv = new CDiv();
			$scrollDiv->setAttribute('id', 'scrollbar_cntr');
			$slideWidget->addFlicker($scrollDiv, CProfile::get('web.slides.filter.state', 1));
			$slideWidget->addFlicker(BR(), CProfile::get('web.slides.filter.state', 1));

			$objData = array(
				'id' => $this->data['elementid'],
				'loadSBox' => 0,
				'loadImage' => 0,
				'loadScroll' => 1,
				'scrollWidthByImage' => 0,
				'dynamic' => 0,
				'mainObject' => 1,
				'periodFixed' => CProfile::get('web.slides.timelinefixed', 1),
				'sliderMaximumTimePeriod' => ZBX_MAX_PERIOD
			);
			zbx_add_post_js('timeControl.addObject(\'iframe\', '.zbx_jsvalue($timeline).', '.zbx_jsvalue($objData).');');
			zbx_add_post_js('timeControl.processObjects();');
		}
		$slideWidget->addItem(new CSpan(_('Loading...'), 'textcolorstyles'));
	}
	else {
		$slideWidget->addItem(new CTableInfo(_('No slides defined.')));
	}
}
return $slideWidget;
?>
