CREATE TABLE `maintenances` (
	`maintenanceid`          bigint unsigned                           NOT NULL,
	`name`                   varchar(128)    DEFAULT ''                NOT NULL,
	`maintenance_type`       integer         DEFAULT '0'               NOT NULL,
	`description`            text                                      NOT NULL,
	`active_since`           integer         DEFAULT '0'               NOT NULL,
	`active_till`            integer         DEFAULT '0'               NOT NULL,
	PRIMARY KEY (maintenanceid)
) ENGINE=InnoDB;
CREATE INDEX `maintenances_1` ON `maintenances` (`active_since`,`active_till`);
CREATE TABLE `hosts` (
	`hostid`                 bigint unsigned                           NOT NULL,
	`proxy_hostid`           bigint unsigned                           NULL,
	`host`                   varchar(64)     DEFAULT ''                NOT NULL,
	`status`                 integer         DEFAULT '0'               NOT NULL,
	`disable_until`          integer         DEFAULT '0'               NOT NULL,
	`error`                  varchar(128)    DEFAULT ''                NOT NULL,
	`available`              integer         DEFAULT '0'               NOT NULL,
	`errors_from`            integer         DEFAULT '0'               NOT NULL,
	`lastaccess`             integer         DEFAULT '0'               NOT NULL,
	`ipmi_authtype`          integer         DEFAULT '0'               NOT NULL,
	`ipmi_privilege`         integer         DEFAULT '2'               NOT NULL,
	`ipmi_username`          varchar(16)     DEFAULT ''                NOT NULL,
	`ipmi_password`          varchar(20)     DEFAULT ''                NOT NULL,
	`ipmi_disable_until`     integer         DEFAULT '0'               NOT NULL,
	`ipmi_available`         integer         DEFAULT '0'               NOT NULL,
	`snmp_disable_until`     integer         DEFAULT '0'               NOT NULL,
	`snmp_available`         integer         DEFAULT '0'               NOT NULL,
	`maintenanceid`          bigint unsigned                           NULL,
	`maintenance_status`     integer         DEFAULT '0'               NOT NULL,
	`maintenance_type`       integer         DEFAULT '0'               NOT NULL,
	`maintenance_from`       integer         DEFAULT '0'               NOT NULL,
	`ipmi_errors_from`       integer         DEFAULT '0'               NOT NULL,
	`snmp_errors_from`       integer         DEFAULT '0'               NOT NULL,
	`ipmi_error`             varchar(128)    DEFAULT ''                NOT NULL,
	`snmp_error`             varchar(128)    DEFAULT ''                NOT NULL,
	`jmx_disable_until`      integer         DEFAULT '0'               NOT NULL,
	`jmx_available`          integer         DEFAULT '0'               NOT NULL,
	`jmx_errors_from`        integer         DEFAULT '0'               NOT NULL,
	`jmx_error`              varchar(128)    DEFAULT ''                NOT NULL,
	`name`                   varchar(64)     DEFAULT ''                NOT NULL,
	PRIMARY KEY (hostid)
) ENGINE=InnoDB;
CREATE INDEX `hosts_1` ON `hosts` (`host`);
CREATE INDEX `hosts_2` ON `hosts` (`status`);
CREATE INDEX `hosts_3` ON `hosts` (`proxy_hostid`);
CREATE INDEX `hosts_4` ON `hosts` (`name`);
CREATE TABLE `groups` (
	`groupid`                bigint unsigned                           NOT NULL,
	`name`                   varchar(64)     DEFAULT ''                NOT NULL,
	`internal`               integer         DEFAULT '0'               NOT NULL,
	PRIMARY KEY (groupid)
) ENGINE=InnoDB;
CREATE INDEX `groups_1` ON `groups` (`name`);
CREATE TABLE `screens` (
	`screenid`               bigint unsigned                           NOT NULL,
	`name`                   varchar(255)                              NOT NULL,
	`hsize`                  integer         DEFAULT '1'               NOT NULL,
	`vsize`                  integer         DEFAULT '1'               NOT NULL,
	`templateid`             bigint unsigned                           NULL,
	PRIMARY KEY (screenid)
) ENGINE=InnoDB;
CREATE TABLE `screens_items` (
	`screenitemid`           bigint unsigned                           NOT NULL,
	`screenid`               bigint unsigned                           NOT NULL,
	`resourcetype`           integer         DEFAULT '0'               NOT NULL,
	`resourceid`             bigint unsigned DEFAULT '0'               NOT NULL,
	`width`                  integer         DEFAULT '320'             NOT NULL,
	`height`                 integer         DEFAULT '200'             NOT NULL,
	`x`                      integer         DEFAULT '0'               NOT NULL,
	`y`                      integer         DEFAULT '0'               NOT NULL,
	`colspan`                integer         DEFAULT '0'               NOT NULL,
	`rowspan`                integer         DEFAULT '0'               NOT NULL,
	`elements`               integer         DEFAULT '25'              NOT NULL,
	`valign`                 integer         DEFAULT '0'               NOT NULL,
	`halign`                 integer         DEFAULT '0'               NOT NULL,
	`style`                  integer         DEFAULT '0'               NOT NULL,
	`url`                    varchar(255)    DEFAULT ''                NOT NULL,
	`dynamic`                integer         DEFAULT '0'               NOT NULL,
	`sort_triggers`          integer         DEFAULT '0'               NOT NULL,
	PRIMARY KEY (screenitemid)
) ENGINE=InnoDB;
CREATE TABLE `slideshows` (
	`slideshowid`            bigint unsigned                           NOT NULL,
	`name`                   varchar(255)    DEFAULT ''                NOT NULL,
	`delay`                  integer         DEFAULT '0'               NOT NULL,
	PRIMARY KEY (slideshowid)
) ENGINE=InnoDB;
CREATE TABLE `slides` (
	`slideid`                bigint unsigned                           NOT NULL,
	`slideshowid`            bigint unsigned                           NOT NULL,
	`screenid`               bigint unsigned                           NOT NULL,
	`step`                   integer         DEFAULT '0'               NOT NULL,
	`delay`                  integer         DEFAULT '0'               NOT NULL,
	PRIMARY KEY (slideid)
) ENGINE=InnoDB;
CREATE INDEX `slides_slides_1` ON `slides` (`slideshowid`);
CREATE TABLE `drules` (
	`druleid`                bigint unsigned                           NOT NULL,
	`proxy_hostid`           bigint unsigned                           NULL,
	`name`                   varchar(255)    DEFAULT ''                NOT NULL,
	`iprange`                varchar(255)    DEFAULT ''                NOT NULL,
	`delay`                  integer         DEFAULT '3600'            NOT NULL,
	`nextcheck`              integer         DEFAULT '0'               NOT NULL,
	`status`                 integer         DEFAULT '0'               NOT NULL,
	PRIMARY KEY (druleid)
) ENGINE=InnoDB;
CREATE TABLE `dchecks` (
	`dcheckid`               bigint unsigned                           NOT NULL,
	`druleid`                bigint unsigned                           NOT NULL,
	`type`                   integer         DEFAULT '0'               NOT NULL,
	`key_`                   varchar(255)    DEFAULT ''                NOT NULL,
	`snmp_community`         varchar(255)    DEFAULT ''                NOT NULL,
	`ports`                  varchar(255)    DEFAULT '0'               NOT NULL,
	`snmpv3_securityname`    varchar(64)     DEFAULT ''                NOT NULL,
	`snmpv3_securitylevel`   integer         DEFAULT '0'               NOT NULL,
	`snmpv3_authpassphrase`  varchar(64)     DEFAULT ''                NOT NULL,
	`snmpv3_privpassphrase`  varchar(64)     DEFAULT ''                NOT NULL,
	`uniq`                   integer         DEFAULT '0'               NOT NULL,
	PRIMARY KEY (dcheckid)
) ENGINE=InnoDB;
CREATE INDEX `dchecks_1` ON `dchecks` (`druleid`);
CREATE TABLE `applications` (
	`applicationid`          bigint unsigned                           NOT NULL,
	`hostid`                 bigint unsigned                           NOT NULL,
	`name`                   varchar(255)    DEFAULT ''                NOT NULL,
	`templateid`             bigint unsigned                           NULL,
	PRIMARY KEY (applicationid)
) ENGINE=InnoDB;
CREATE INDEX `applications_1` ON `applications` (`templateid`);
CREATE UNIQUE INDEX `applications_2` ON `applications` (`hostid`,`name`);
CREATE TABLE `httptest` (
	`httptestid`             bigint unsigned                           NOT NULL,
	`name`                   varchar(64)     DEFAULT ''                NOT NULL,
	`applicationid`          bigint unsigned                           NOT NULL,
	`nextcheck`              integer         DEFAULT '0'               NOT NULL,
	`delay`                  integer         DEFAULT '60'              NOT NULL,
	`status`                 integer         DEFAULT '0'               NOT NULL,
	`macros`                 text                                      NOT NULL,
	`agent`                  varchar(255)    DEFAULT ''                NOT NULL,
	`authentication`         integer         DEFAULT '0'               NOT NULL,
	`http_user`              varchar(64)     DEFAULT ''                NOT NULL,
	`http_password`          varchar(64)     DEFAULT ''                NOT NULL,
	PRIMARY KEY (httptestid)
) ENGINE=InnoDB;
CREATE INDEX `httptest_httptest_1` ON `httptest` (`applicationid`);
CREATE INDEX `httptest_2` ON `httptest` (`name`);
CREATE INDEX `httptest_3` ON `httptest` (`status`);
CREATE TABLE `httpstep` (
	`httpstepid`             bigint unsigned                           NOT NULL,
	`httptestid`             bigint unsigned                           NOT NULL,
	`name`                   varchar(64)     DEFAULT ''                NOT NULL,
	`no`                     integer         DEFAULT '0'               NOT NULL,
	`url`                    varchar(255)    DEFAULT ''                NOT NULL,
	`timeout`                integer         DEFAULT '30'              NOT NULL,
	`posts`                  text                                      NOT NULL,
	`required`               varchar(255)    DEFAULT ''                NOT NULL,
	`status_codes`           varchar(255)    DEFAULT ''                NOT NULL,
	PRIMARY KEY (httpstepid)
) ENGINE=InnoDB;
CREATE INDEX `httpstep_httpstep_1` ON `httpstep` (`httptestid`);
CREATE TABLE `interface` (
	`interfaceid`            bigint unsigned                           NOT NULL,
	`hostid`                 bigint unsigned                           NOT NULL,
	`main`                   integer         DEFAULT '0'               NOT NULL,
	`type`                   integer         DEFAULT '0'               NOT NULL,
	`useip`                  integer         DEFAULT '1'               NOT NULL,
	`ip`                     varchar(39)     DEFAULT '127.0.0.1'       NOT NULL,
	`dns`                    varchar(64)     DEFAULT ''                NOT NULL,
	`port`                   varchar(64)     DEFAULT '10050'           NOT NULL,
	PRIMARY KEY (interfaceid)
) ENGINE=InnoDB;
CREATE INDEX `interface_1` ON `interface` (`hostid`,`type`);
CREATE INDEX `interface_2` ON `interface` (`ip`,`dns`);
CREATE TABLE `valuemaps` (
	`valuemapid`             bigint unsigned                           NOT NULL,
	`name`                   varchar(64)     DEFAULT ''                NOT NULL,
	PRIMARY KEY (valuemapid)
) ENGINE=InnoDB;
CREATE INDEX `valuemaps_1` ON `valuemaps` (`name`);
CREATE TABLE `items` (
	`itemid`                 bigint unsigned                           NOT NULL,
	`type`                   integer         DEFAULT '0'               NOT NULL,
	`snmp_community`         varchar(64)     DEFAULT ''                NOT NULL,
	`snmp_oid`               varchar(255)    DEFAULT ''                NOT NULL,
	`hostid`                 bigint unsigned                           NOT NULL,
	`name`                   varchar(255)    DEFAULT ''                NOT NULL,
	`key_`                   varchar(255)    DEFAULT ''                NOT NULL,
	`delay`                  integer         DEFAULT '0'               NOT NULL,
	`history`                integer         DEFAULT '90'              NOT NULL,
	`trends`                 integer         DEFAULT '365'             NOT NULL,
	`lastvalue`              varchar(255)                              NULL,
	`lastclock`              integer                                   NULL,
	`prevvalue`              varchar(255)                              NULL,
	`status`                 integer         DEFAULT '0'               NOT NULL,
	`value_type`             integer         DEFAULT '0'               NOT NULL,
	`trapper_hosts`          varchar(255)    DEFAULT ''                NOT NULL,
	`units`                  varchar(255)    DEFAULT ''                NOT NULL,
	`multiplier`             integer         DEFAULT '0'               NOT NULL,
	`delta`                  integer         DEFAULT '0'               NOT NULL,
	`prevorgvalue`           varchar(255)                              NULL,
	`snmpv3_securityname`    varchar(64)     DEFAULT ''                NOT NULL,
	`snmpv3_securitylevel`   integer         DEFAULT '0'               NOT NULL,
	`snmpv3_authpassphrase`  varchar(64)     DEFAULT ''                NOT NULL,
	`snmpv3_privpassphrase`  varchar(64)     DEFAULT ''                NOT NULL,
	`formula`                varchar(255)    DEFAULT '1'               NOT NULL,
	`error`                  varchar(128)    DEFAULT ''                NOT NULL,
	`lastlogsize`            bigint unsigned DEFAULT '0'               NOT NULL,
	`logtimefmt`             varchar(64)     DEFAULT ''                NOT NULL,
	`templateid`             bigint unsigned                           NULL,
	`valuemapid`             bigint unsigned                           NULL,
	`delay_flex`             varchar(255)    DEFAULT ''                NOT NULL,
	`params`                 text                                      NOT NULL,
	`ipmi_sensor`            varchar(128)    DEFAULT ''                NOT NULL,
	`data_type`              integer         DEFAULT '0'               NOT NULL,
	`authtype`               integer         DEFAULT '0'               NOT NULL,
	`username`               varchar(64)     DEFAULT ''                NOT NULL,
	`password`               varchar(64)     DEFAULT ''                NOT NULL,
	`publickey`              varchar(64)     DEFAULT ''                NOT NULL,
	`privatekey`             varchar(64)     DEFAULT ''                NOT NULL,
	`mtime`                  integer         DEFAULT '0'               NOT NULL,
	`lastns`                 integer                                   NULL,
	`flags`                  integer         DEFAULT '0'               NOT NULL,
	`filter`                 varchar(255)    DEFAULT ''                NOT NULL,
	`interfaceid`            bigint unsigned                           NULL,
	`port`                   varchar(64)     DEFAULT ''                NOT NULL,
	`description`            text                                      NOT NULL,
	`inventory_link`         integer         DEFAULT '0'               NOT NULL,
	`lifetime`               varchar(64)     DEFAULT '30'              NOT NULL,
	PRIMARY KEY (itemid)
) ENGINE=InnoDB;
CREATE UNIQUE INDEX `items_1` ON `items` (`hostid`,`key_`);
CREATE INDEX `items_3` ON `items` (`status`);
CREATE INDEX `items_4` ON `items` (`templateid`);
CREATE INDEX `items_5` ON `items` (`valuemapid`);
CREATE TABLE `httpstepitem` (
	`httpstepitemid`         bigint unsigned                           NOT NULL,
	`httpstepid`             bigint unsigned                           NOT NULL,
	`itemid`                 bigint unsigned                           NOT NULL,
	`type`                   integer         DEFAULT '0'               NOT NULL,
	PRIMARY KEY (httpstepitemid)
) ENGINE=InnoDB;
CREATE UNIQUE INDEX `httpstepitem_httpstepitem_1` ON `httpstepitem` (`httpstepid`,`itemid`);
CREATE TABLE `httptestitem` (
	`httptestitemid`         bigint unsigned                           NOT NULL,
	`httptestid`             bigint unsigned                           NOT NULL,
	`itemid`                 bigint unsigned                           NOT NULL,
	`type`                   integer         DEFAULT '0'               NOT NULL,
	PRIMARY KEY (httptestitemid)
) ENGINE=InnoDB;
CREATE UNIQUE INDEX `httptestitem_httptestitem_1` ON `httptestitem` (`httptestid`,`itemid`);
CREATE TABLE `media_type` (
	`mediatypeid`            bigint unsigned                           NOT NULL,
	`type`                   integer         DEFAULT '0'               NOT NULL,
	`description`            varchar(100)    DEFAULT ''                NOT NULL,
	`smtp_server`            varchar(255)    DEFAULT ''                NOT NULL,
	`smtp_helo`              varchar(255)    DEFAULT ''                NOT NULL,
	`smtp_email`             varchar(255)    DEFAULT ''                NOT NULL,
	`exec_path`              varchar(255)    DEFAULT ''                NOT NULL,
	`gsm_modem`              varchar(255)    DEFAULT ''                NOT NULL,
	`username`               varchar(255)    DEFAULT ''                NOT NULL,
	`passwd`                 varchar(255)    DEFAULT ''                NOT NULL,
	`status`                 integer         DEFAULT '0'               NOT NULL,
	PRIMARY KEY (mediatypeid)
) ENGINE=InnoDB;
CREATE TABLE `users` (
	`userid`                 bigint unsigned                           NOT NULL,
	`alias`                  varchar(100)    DEFAULT ''                NOT NULL,
	`name`                   varchar(100)    DEFAULT ''                NOT NULL,
	`surname`                varchar(100)    DEFAULT ''                NOT NULL,
	`passwd`                 char(32)        DEFAULT ''                NOT NULL,
	`url`                    varchar(255)    DEFAULT ''                NOT NULL,
	`autologin`              integer         DEFAULT '0'               NOT NULL,
	`autologout`             integer         DEFAULT '900'             NOT NULL,
	`lang`                   varchar(5)      DEFAULT 'en_GB'           NOT NULL,
	`refresh`                integer         DEFAULT '30'              NOT NULL,
	`type`                   integer         DEFAULT '0'               NOT NULL,
	`theme`                  varchar(128)    DEFAULT 'default'         NOT NULL,
	`attempt_failed`         integer         DEFAULT 0                 NOT NULL,
	`attempt_ip`             varchar(39)     DEFAULT ''                NOT NULL,
	`attempt_clock`          integer         DEFAULT 0                 NOT NULL,
	`rows_per_page`          integer         DEFAULT 50                NOT NULL,
	PRIMARY KEY (userid)
) ENGINE=InnoDB;
CREATE INDEX `users_1` ON `users` (`alias`);
CREATE TABLE `usrgrp` (
	`usrgrpid`               bigint unsigned                           NOT NULL,
	`name`                   varchar(64)     DEFAULT ''                NOT NULL,
	`gui_access`             integer         DEFAULT '0'               NOT NULL,
	`users_status`           integer         DEFAULT '0'               NOT NULL,
	`debug_mode`             integer         DEFAULT '0'               NOT NULL,
	PRIMARY KEY (usrgrpid)
) ENGINE=InnoDB;
CREATE INDEX `usrgrp_1` ON `usrgrp` (`name`);
CREATE TABLE `users_groups` (
	`id`                     bigint unsigned                           NOT NULL,
	`usrgrpid`               bigint unsigned                           NOT NULL,
	`userid`                 bigint unsigned                           NOT NULL,
	PRIMARY KEY (id)
) ENGINE=InnoDB;
CREATE UNIQUE INDEX `users_groups_1` ON `users_groups` (`usrgrpid`,`userid`);
CREATE TABLE `scripts` (
	`scriptid`               bigint unsigned                           NOT NULL,
	`name`                   varchar(255)    DEFAULT ''                NOT NULL,
	`command`                varchar(255)    DEFAULT ''                NOT NULL,
	`host_access`            integer         DEFAULT '2'               NOT NULL,
	`usrgrpid`               bigint unsigned                           NULL,
	`groupid`                bigint unsigned                           NULL,
	`description`            text                                      NOT NULL,
	`confirmation`           varchar(255)    DEFAULT ''                NOT NULL,
	`type`                   integer         DEFAULT '0'               NOT NULL,
	`execute_on`             integer         DEFAULT '1'               NOT NULL,
	PRIMARY KEY (scriptid)
) ENGINE=InnoDB;
CREATE TABLE `actions` (
	`actionid`               bigint unsigned                           NOT NULL,
	`name`                   varchar(255)    DEFAULT ''                NOT NULL,
	`eventsource`            integer         DEFAULT '0'               NOT NULL,
	`evaltype`               integer         DEFAULT '0'               NOT NULL,
	`status`                 integer         DEFAULT '0'               NOT NULL,
	`esc_period`             integer         DEFAULT '0'               NOT NULL,
	`def_shortdata`          varchar(255)    DEFAULT ''                NOT NULL,
	`def_longdata`           text                                      NOT NULL,
	`recovery_msg`           integer         DEFAULT '0'               NOT NULL,
	`r_shortdata`            varchar(255)    DEFAULT ''                NOT NULL,
	`r_longdata`             text                                      NOT NULL,
	PRIMARY KEY (actionid)
) ENGINE=InnoDB;
CREATE INDEX `actions_1` ON `actions` (`eventsource`,`status`);
CREATE TABLE `operations` (
	`operationid`            bigint unsigned                           NOT NULL,
	`actionid`               bigint unsigned                           NOT NULL,
	`operationtype`          integer         DEFAULT '0'               NOT NULL,
	`esc_period`             integer         DEFAULT '0'               NOT NULL,
	`esc_step_from`          integer         DEFAULT '1'               NOT NULL,
	`esc_step_to`            integer         DEFAULT '1'               NOT NULL,
	`evaltype`               integer         DEFAULT '0'               NOT NULL,
	PRIMARY KEY (operationid)
) ENGINE=InnoDB;
CREATE INDEX `operations_1` ON `operations` (`actionid`);
CREATE TABLE `opmessage` (
	`operationid`            bigint unsigned                           NOT NULL,
	`default_msg`            integer         DEFAULT '0'               NOT NULL,
	`subject`                varchar(255)    DEFAULT ''                NOT NULL,
	`message`                text                                      NOT NULL,
	`mediatypeid`            bigint unsigned                           NULL,
	PRIMARY KEY (operationid)
) ENGINE=InnoDB;
CREATE TABLE `opmessage_grp` (
	`opmessage_grpid`        bigint unsigned                           NOT NULL,
	`operationid`            bigint unsigned                           NOT NULL,
	`usrgrpid`               bigint unsigned                           NOT NULL,
	PRIMARY KEY (opmessage_grpid)
) ENGINE=InnoDB;
CREATE UNIQUE INDEX `opmessage_grp_1` ON `opmessage_grp` (`operationid`,`usrgrpid`);
CREATE TABLE `opmessage_usr` (
	`opmessage_usrid`        bigint unsigned                           NOT NULL,
	`operationid`            bigint unsigned                           NOT NULL,
	`userid`                 bigint unsigned                           NOT NULL,
	PRIMARY KEY (opmessage_usrid)
) ENGINE=InnoDB;
CREATE UNIQUE INDEX `opmessage_usr_1` ON `opmessage_usr` (`operationid`,`userid`);
CREATE TABLE `opcommand` (
	`operationid`            bigint unsigned                           NOT NULL,
	`type`                   integer         DEFAULT '0'               NOT NULL,
	`scriptid`               bigint unsigned                           NULL,
	`execute_on`             integer         DEFAULT '0'               NOT NULL,
	`port`                   varchar(64)     DEFAULT ''                NOT NULL,
	`authtype`               integer         DEFAULT '0'               NOT NULL,
	`username`               varchar(64)     DEFAULT ''                NOT NULL,
	`password`               varchar(64)     DEFAULT ''                NOT NULL,
	`publickey`              varchar(64)     DEFAULT ''                NOT NULL,
	`privatekey`             varchar(64)     DEFAULT ''                NOT NULL,
	`command`                text                                      NOT NULL,
	PRIMARY KEY (operationid)
) ENGINE=InnoDB;
CREATE TABLE `opcommand_hst` (
	`opcommand_hstid`        bigint unsigned                           NOT NULL,
	`operationid`            bigint unsigned                           NOT NULL,
	`hostid`                 bigint unsigned                           NULL,
	PRIMARY KEY (opcommand_hstid)
) ENGINE=InnoDB;
CREATE INDEX `opcommand_hst_1` ON `opcommand_hst` (`operationid`);
CREATE TABLE `opcommand_grp` (
	`opcommand_grpid`        bigint unsigned                           NOT NULL,
	`operationid`            bigint unsigned                           NOT NULL,
	`groupid`                bigint unsigned                           NOT NULL,
	PRIMARY KEY (opcommand_grpid)
) ENGINE=InnoDB;
CREATE INDEX `opcommand_grp_1` ON `opcommand_grp` (`operationid`);
CREATE TABLE `opgroup` (
	`opgroupid`              bigint unsigned                           NOT NULL,
	`operationid`            bigint unsigned                           NOT NULL,
	`groupid`                bigint unsigned                           NOT NULL,
	PRIMARY KEY (opgroupid)
) ENGINE=InnoDB;
CREATE UNIQUE INDEX `opgroup_1` ON `opgroup` (`operationid`,`groupid`);
CREATE TABLE `optemplate` (
	`optemplateid`           bigint unsigned                           NOT NULL,
	`operationid`            bigint unsigned                           NOT NULL,
	`templateid`             bigint unsigned                           NOT NULL,
	PRIMARY KEY (optemplateid)
) ENGINE=InnoDB;
CREATE UNIQUE INDEX `optemplate_1` ON `optemplate` (`operationid`,`templateid`);
CREATE TABLE `opconditions` (
	`opconditionid`          bigint unsigned                           NOT NULL,
	`operationid`            bigint unsigned                           NOT NULL,
	`conditiontype`          integer         DEFAULT '0'               NOT NULL,
	`operator`               integer         DEFAULT '0'               NOT NULL,
	`value`                  varchar(255)    DEFAULT ''                NOT NULL,
	PRIMARY KEY (opconditionid)
) ENGINE=InnoDB;
CREATE INDEX `opconditions_1` ON `opconditions` (`operationid`);
CREATE TABLE `conditions` (
	`conditionid`            bigint unsigned                           NOT NULL,
	`actionid`               bigint unsigned                           NOT NULL,
	`conditiontype`          integer         DEFAULT '0'               NOT NULL,
	`operator`               integer         DEFAULT '0'               NOT NULL,
	`value`                  varchar(255)    DEFAULT ''                NOT NULL,
	PRIMARY KEY (conditionid)
) ENGINE=InnoDB;
CREATE INDEX `conditions_1` ON `conditions` (`actionid`);
CREATE TABLE `config` (
	`configid`               bigint unsigned                           NOT NULL,
	`alert_history`          integer         DEFAULT '0'               NOT NULL,
	`event_history`          integer         DEFAULT '0'               NOT NULL,
	`refresh_unsupported`    integer         DEFAULT '0'               NOT NULL,
	`work_period`            varchar(100)    DEFAULT '1-5,00:00-24:00' NOT NULL,
	`alert_usrgrpid`         bigint unsigned                           NULL,
	`event_ack_enable`       integer         DEFAULT '1'               NOT NULL,
	`event_expire`           integer         DEFAULT '7'               NOT NULL,
	`event_show_max`         integer         DEFAULT '100'             NOT NULL,
	`default_theme`          varchar(128)    DEFAULT 'originalblue'    NOT NULL,
	`authentication_type`    integer         DEFAULT '0'               NOT NULL,
	`ldap_host`              varchar(255)    DEFAULT ''                NOT NULL,
	`ldap_port`              integer         DEFAULT 389               NOT NULL,
	`ldap_base_dn`           varchar(255)    DEFAULT ''                NOT NULL,
	`ldap_bind_dn`           varchar(255)    DEFAULT ''                NOT NULL,
	`ldap_bind_password`     varchar(128)    DEFAULT ''                NOT NULL,
	`ldap_search_attribute`  varchar(128)    DEFAULT ''                NOT NULL,
	`dropdown_first_entry`   integer         DEFAULT '1'               NOT NULL,
	`dropdown_first_remember` integer         DEFAULT '1'               NOT NULL,
	`discovery_groupid`      bigint unsigned                           NOT NULL,
	`max_in_table`           integer         DEFAULT '50'              NOT NULL,
	`search_limit`           integer         DEFAULT '1000'            NOT NULL,
	`severity_color_0`       varchar(6)      DEFAULT 'DBDBDB'          NOT NULL,
	`severity_color_1`       varchar(6)      DEFAULT 'D6F6FF'          NOT NULL,
	`severity_color_2`       varchar(6)      DEFAULT 'FFF6A5'          NOT NULL,
	`severity_color_3`       varchar(6)      DEFAULT 'FFB689'          NOT NULL,
	`severity_color_4`       varchar(6)      DEFAULT 'FF9999'          NOT NULL,
	`severity_color_5`       varchar(6)      DEFAULT 'FF3838'          NOT NULL,
	`severity_name_0`        varchar(32)     DEFAULT 'Not classified'  NOT NULL,
	`severity_name_1`        varchar(32)     DEFAULT 'Information'     NOT NULL,
	`severity_name_2`        varchar(32)     DEFAULT 'Warning'         NOT NULL,
	`severity_name_3`        varchar(32)     DEFAULT 'Average'         NOT NULL,
	`severity_name_4`        varchar(32)     DEFAULT 'High'            NOT NULL,
	`severity_name_5`        varchar(32)     DEFAULT 'Disaster'        NOT NULL,
	`ok_period`              integer         DEFAULT '1800'            NOT NULL,
	`blink_period`           integer         DEFAULT '1800'            NOT NULL,
	`problem_unack_color`    varchar(6)      DEFAULT 'DC0000'          NOT NULL,
	`problem_ack_color`      varchar(6)      DEFAULT 'DC0000'          NOT NULL,
	`ok_unack_color`         varchar(6)      DEFAULT '00AA00'          NOT NULL,
	`ok_ack_color`           varchar(6)      DEFAULT '00AA00'          NOT NULL,
	`problem_unack_style`    integer         DEFAULT '1'               NOT NULL,
	`problem_ack_style`      integer         DEFAULT '1'               NOT NULL,
	`ok_unack_style`         integer         DEFAULT '1'               NOT NULL,
	`ok_ack_style`           integer         DEFAULT '1'               NOT NULL,
	`snmptrap_logging`       integer         DEFAULT '1'               NOT NULL,
	`server_check_interval`  integer         DEFAULT '60'              NOT NULL,
	PRIMARY KEY (configid)
) ENGINE=InnoDB;
CREATE TABLE `triggers` (
	`triggerid`              bigint unsigned                           NOT NULL,
	`expression`             varchar(255)    DEFAULT ''                NOT NULL,
	`description`            varchar(255)    DEFAULT ''                NOT NULL,
	`url`                    varchar(255)    DEFAULT ''                NOT NULL,
	`status`                 integer         DEFAULT '0'               NOT NULL,
	`value`                  integer         DEFAULT '0'               NOT NULL,
	`priority`               integer         DEFAULT '0'               NOT NULL,
	`lastchange`             integer         DEFAULT '0'               NOT NULL,
	`comments`               text                                      NOT NULL,
	`error`                  varchar(128)    DEFAULT ''                NOT NULL,
	`templateid`             bigint unsigned                           NULL,
	`type`                   integer         DEFAULT '0'               NOT NULL,
	`value_flags`            integer         DEFAULT '0'               NOT NULL,
	`flags`                  integer         DEFAULT '0'               NOT NULL,
	PRIMARY KEY (triggerid)
) ENGINE=InnoDB;
CREATE INDEX `triggers_1` ON `triggers` (`status`);
CREATE INDEX `triggers_2` ON `triggers` (`value`);
CREATE TABLE `trigger_depends` (
	`triggerdepid`           bigint unsigned                           NOT NULL,
	`triggerid_down`         bigint unsigned                           NOT NULL,
	`triggerid_up`           bigint unsigned                           NOT NULL,
	PRIMARY KEY (triggerdepid)
) ENGINE=InnoDB;
CREATE UNIQUE INDEX `trigger_depends_1` ON `trigger_depends` (`triggerid_down`,`triggerid_up`);
CREATE INDEX `trigger_depends_2` ON `trigger_depends` (`triggerid_up`);
CREATE TABLE `functions` (
	`functionid`             bigint unsigned                           NOT NULL,
	`itemid`                 bigint unsigned                           NOT NULL,
	`triggerid`              bigint unsigned                           NOT NULL,
	`function`               varchar(12)     DEFAULT ''                NOT NULL,
	`parameter`              varchar(255)    DEFAULT '0'               NOT NULL,
	PRIMARY KEY (functionid)
) ENGINE=InnoDB;
CREATE INDEX `functions_1` ON `functions` (`triggerid`);
CREATE INDEX `functions_2` ON `functions` (`itemid`,`function`,`parameter`);
CREATE TABLE `graphs` (
	`graphid`                bigint unsigned                           NOT NULL,
	`name`                   varchar(128)    DEFAULT ''                NOT NULL,
	`width`                  integer         DEFAULT '0'               NOT NULL,
	`height`                 integer         DEFAULT '0'               NOT NULL,
	`yaxismin`               double(16,4)    DEFAULT '0'               NOT NULL,
	`yaxismax`               double(16,4)    DEFAULT '0'               NOT NULL,
	`templateid`             bigint unsigned                           NULL,
	`show_work_period`       integer         DEFAULT '1'               NOT NULL,
	`show_triggers`          integer         DEFAULT '1'               NOT NULL,
	`graphtype`              integer         DEFAULT '0'               NOT NULL,
	`show_legend`            integer         DEFAULT '1'               NOT NULL,
	`show_3d`                integer         DEFAULT '0'               NOT NULL,
	`percent_left`           double(16,4)    DEFAULT '0'               NOT NULL,
	`percent_right`          double(16,4)    DEFAULT '0'               NOT NULL,
	`ymin_type`              integer         DEFAULT '0'               NOT NULL,
	`ymax_type`              integer         DEFAULT '0'               NOT NULL,
	`ymin_itemid`            bigint unsigned                           NULL,
	`ymax_itemid`            bigint unsigned                           NULL,
	`flags`                  integer         DEFAULT '0'               NOT NULL,
	PRIMARY KEY (graphid)
) ENGINE=InnoDB;
CREATE INDEX `graphs_graphs_1` ON `graphs` (`name`);
CREATE TABLE `graphs_items` (
	`gitemid`                bigint unsigned                           NOT NULL,
	`graphid`                bigint unsigned                           NOT NULL,
	`itemid`                 bigint unsigned                           NOT NULL,
	`drawtype`               integer         DEFAULT '0'               NOT NULL,
	`sortorder`              integer         DEFAULT '0'               NOT NULL,
	`color`                  varchar(6)      DEFAULT '009600'          NOT NULL,
	`yaxisside`              integer         DEFAULT '1'               NOT NULL,
	`calc_fnc`               integer         DEFAULT '2'               NOT NULL,
	`type`                   integer         DEFAULT '0'               NOT NULL,
	PRIMARY KEY (gitemid)
) ENGINE=InnoDB;
CREATE INDEX `graphs_items_1` ON `graphs_items` (`itemid`);
CREATE INDEX `graphs_items_2` ON `graphs_items` (`graphid`);
CREATE TABLE `graph_theme` (
	`graphthemeid`           bigint unsigned                           NOT NULL,
	`description`            varchar(64)     DEFAULT ''                NOT NULL,
	`theme`                  varchar(64)     DEFAULT ''                NOT NULL,
	`backgroundcolor`        varchar(6)      DEFAULT 'F0F0F0'          NOT NULL,
	`graphcolor`             varchar(6)      DEFAULT 'FFFFFF'          NOT NULL,
	`graphbordercolor`       varchar(6)      DEFAULT '222222'          NOT NULL,
	`gridcolor`              varchar(6)      DEFAULT 'CCCCCC'          NOT NULL,
	`maingridcolor`          varchar(6)      DEFAULT 'AAAAAA'          NOT NULL,
	`gridbordercolor`        varchar(6)      DEFAULT '000000'          NOT NULL,
	`textcolor`              varchar(6)      DEFAULT '202020'          NOT NULL,
	`highlightcolor`         varchar(6)      DEFAULT 'AA4444'          NOT NULL,
	`leftpercentilecolor`    varchar(6)      DEFAULT '11CC11'          NOT NULL,
	`rightpercentilecolor`   varchar(6)      DEFAULT 'CC1111'          NOT NULL,
	`nonworktimecolor`       varchar(6)      DEFAULT 'CCCCCC'          NOT NULL,
	`gridview`               integer         DEFAULT 1                 NOT NULL,
	`legendview`             integer         DEFAULT 1                 NOT NULL,
	PRIMARY KEY (graphthemeid)
) ENGINE=InnoDB;
CREATE INDEX `graph_theme_1` ON `graph_theme` (`description`);
CREATE INDEX `graph_theme_2` ON `graph_theme` (`theme`);
CREATE TABLE `help_items` (
	`itemtype`               integer         DEFAULT '0'               NOT NULL,
	`key_`                   varchar(255)    DEFAULT ''                NOT NULL,
	`description`            varchar(255)    DEFAULT ''                NOT NULL,
	PRIMARY KEY (itemtype,key_)
) ENGINE=InnoDB;
CREATE TABLE `globalmacro` (
	`globalmacroid`          bigint unsigned                           NOT NULL,
	`macro`                  varchar(64)     DEFAULT ''                NOT NULL,
	`value`                  varchar(255)    DEFAULT ''                NOT NULL,
	PRIMARY KEY (globalmacroid)
) ENGINE=InnoDB;
CREATE INDEX `globalmacro_1` ON `globalmacro` (`macro`);
CREATE TABLE `hostmacro` (
	`hostmacroid`            bigint unsigned                           NOT NULL,
	`hostid`                 bigint unsigned                           NOT NULL,
	`macro`                  varchar(64)     DEFAULT ''                NOT NULL,
	`value`                  varchar(255)    DEFAULT ''                NOT NULL,
	PRIMARY KEY (hostmacroid)
) ENGINE=InnoDB;
CREATE UNIQUE INDEX `hostmacro_1` ON `hostmacro` (`hostid`,`macro`);
CREATE TABLE `hosts_groups` (
	`hostgroupid`            bigint unsigned                           NOT NULL,
	`hostid`                 bigint unsigned                           NOT NULL,
	`groupid`                bigint unsigned                           NOT NULL,
	PRIMARY KEY (hostgroupid)
) ENGINE=InnoDB;
CREATE UNIQUE INDEX `hosts_groups_1` ON `hosts_groups` (`hostid`,`groupid`);
CREATE INDEX `hosts_groups_2` ON `hosts_groups` (`groupid`);
CREATE TABLE `hosts_templates` (
	`hosttemplateid`         bigint unsigned                           NOT NULL,
	`hostid`                 bigint unsigned                           NOT NULL,
	`templateid`             bigint unsigned                           NOT NULL,
	PRIMARY KEY (hosttemplateid)
) ENGINE=InnoDB;
CREATE UNIQUE INDEX `hosts_templates_1` ON `hosts_templates` (`hostid`,`templateid`);
CREATE INDEX `hosts_templates_2` ON `hosts_templates` (`templateid`);
CREATE TABLE `items_applications` (
	`itemappid`              bigint unsigned                           NOT NULL,
	`applicationid`          bigint unsigned                           NOT NULL,
	`itemid`                 bigint unsigned                           NOT NULL,
	PRIMARY KEY (itemappid)
) ENGINE=InnoDB;
CREATE UNIQUE INDEX `items_applications_1` ON `items_applications` (`applicationid`,`itemid`);
CREATE INDEX `items_applications_2` ON `items_applications` (`itemid`);
CREATE TABLE `mappings` (
	`mappingid`              bigint unsigned                           NOT NULL,
	`valuemapid`             bigint unsigned                           NOT NULL,
	`value`                  varchar(64)     DEFAULT ''                NOT NULL,
	`newvalue`               varchar(64)     DEFAULT ''                NOT NULL,
	PRIMARY KEY (mappingid)
) ENGINE=InnoDB;
CREATE INDEX `mappings_1` ON `mappings` (`valuemapid`);
CREATE TABLE `media` (
	`mediaid`                bigint unsigned                           NOT NULL,
	`userid`                 bigint unsigned                           NOT NULL,
	`mediatypeid`            bigint unsigned                           NOT NULL,
	`sendto`                 varchar(100)    DEFAULT ''                NOT NULL,
	`active`                 integer         DEFAULT '0'               NOT NULL,
	`severity`               integer         DEFAULT '63'              NOT NULL,
	`period`                 varchar(100)    DEFAULT '1-7,00:00-24:00' NOT NULL,
	PRIMARY KEY (mediaid)
) ENGINE=InnoDB;
CREATE INDEX `media_1` ON `media` (`userid`);
CREATE INDEX `media_2` ON `media` (`mediatypeid`);
CREATE TABLE `rights` (
	`rightid`                bigint unsigned                           NOT NULL,
	`groupid`                bigint unsigned                           NOT NULL,
	`permission`             integer         DEFAULT '0'               NOT NULL,
	`id`                     bigint unsigned                           NOT NULL,
	PRIMARY KEY (rightid)
) ENGINE=InnoDB;
CREATE INDEX `rights_1` ON `rights` (`groupid`);
CREATE INDEX `rights_2` ON `rights` (`id`);
CREATE TABLE `services` (
	`serviceid`              bigint unsigned                           NOT NULL,
	`name`                   varchar(128)    DEFAULT ''                NOT NULL,
	`status`                 integer         DEFAULT '0'               NOT NULL,
	`algorithm`              integer         DEFAULT '0'               NOT NULL,
	`triggerid`              bigint unsigned                           NULL,
	`showsla`                integer         DEFAULT '0'               NOT NULL,
	`goodsla`                double(16,4)    DEFAULT '99.9'            NOT NULL,
	`sortorder`              integer         DEFAULT '0'               NOT NULL,
	PRIMARY KEY (serviceid)
) ENGINE=InnoDB;
CREATE INDEX `services_1` ON `services` (`triggerid`);
CREATE TABLE `services_links` (
	`linkid`                 bigint unsigned                           NOT NULL,
	`serviceupid`            bigint unsigned                           NOT NULL,
	`servicedownid`          bigint unsigned                           NOT NULL,
	`soft`                   integer         DEFAULT '0'               NOT NULL,
	PRIMARY KEY (linkid)
) ENGINE=InnoDB;
CREATE INDEX `services_links_links_1` ON `services_links` (`servicedownid`);
CREATE UNIQUE INDEX `services_links_links_2` ON `services_links` (`serviceupid`,`servicedownid`);
CREATE TABLE `services_times` (
	`timeid`                 bigint unsigned                           NOT NULL,
	`serviceid`              bigint unsigned                           NOT NULL,
	`type`                   integer         DEFAULT '0'               NOT NULL,
	`ts_from`                integer         DEFAULT '0'               NOT NULL,
	`ts_to`                  integer         DEFAULT '0'               NOT NULL,
	`note`                   varchar(255)    DEFAULT ''                NOT NULL,
	PRIMARY KEY (timeid)
) ENGINE=InnoDB;
CREATE INDEX `services_times_times_1` ON `services_times` (`serviceid`,`type`,`ts_from`,`ts_to`);
CREATE TABLE `icon_map` (
	`iconmapid`              bigint unsigned                           NOT NULL,
	`name`                   varchar(64)     DEFAULT ''                NOT NULL,
	`default_iconid`         bigint unsigned                           NOT NULL,
	PRIMARY KEY (iconmapid)
) ENGINE=InnoDB;
CREATE INDEX `icon_map_1` ON `icon_map` (`name`);
CREATE TABLE `icon_mapping` (
	`iconmappingid`          bigint unsigned                           NOT NULL,
	`iconmapid`              bigint unsigned                           NOT NULL,
	`iconid`                 bigint unsigned                           NOT NULL,
	`inventory_link`         integer         DEFAULT '0'               NOT NULL,
	`expression`             varchar(64)     DEFAULT ''                NOT NULL,
	`sortorder`              integer         DEFAULT '0'               NOT NULL,
	PRIMARY KEY (iconmappingid)
) ENGINE=InnoDB;
CREATE INDEX `icon_mapping_1` ON `icon_mapping` (`iconmapid`);
CREATE TABLE `sysmaps` (
	`sysmapid`               bigint unsigned                           NOT NULL,
	`name`                   varchar(128)    DEFAULT ''                NOT NULL,
	`width`                  integer         DEFAULT '600'             NOT NULL,
	`height`                 integer         DEFAULT '400'             NOT NULL,
	`backgroundid`           bigint unsigned                           NULL,
	`label_type`             integer         DEFAULT '2'               NOT NULL,
	`label_location`         integer         DEFAULT '3'               NOT NULL,
	`highlight`              integer         DEFAULT '1'               NOT NULL,
	`expandproblem`          integer         DEFAULT '1'               NOT NULL,
	`markelements`           integer         DEFAULT '0'               NOT NULL,
	`show_unack`             integer         DEFAULT '0'               NOT NULL,
	`grid_size`              integer         DEFAULT '50'              NOT NULL,
	`grid_show`              integer         DEFAULT '1'               NOT NULL,
	`grid_align`             integer         DEFAULT '1'               NOT NULL,
	`label_format`           integer         DEFAULT '0'               NOT NULL,
	`label_type_host`        integer         DEFAULT '2'               NOT NULL,
	`label_type_hostgroup`   integer         DEFAULT '2'               NOT NULL,
	`label_type_trigger`     integer         DEFAULT '2'               NOT NULL,
	`label_type_map`         integer         DEFAULT '2'               NOT NULL,
	`label_type_image`       integer         DEFAULT '2'               NOT NULL,
	`label_string_host`      varchar(255)    DEFAULT ''                NOT NULL,
	`label_string_hostgroup` varchar(255)    DEFAULT ''                NOT NULL,
	`label_string_trigger`   varchar(255)    DEFAULT ''                NOT NULL,
	`label_string_map`       varchar(255)    DEFAULT ''                NOT NULL,
	`label_string_image`     varchar(255)    DEFAULT ''                NOT NULL,
	`iconmapid`              bigint unsigned                           NULL,
	`expand_macros`          integer         DEFAULT '0'               NOT NULL,
	PRIMARY KEY (sysmapid)
) ENGINE=InnoDB;
CREATE INDEX `sysmaps_1` ON `sysmaps` (`name`);
CREATE TABLE `sysmaps_elements` (
	`selementid`             bigint unsigned                           NOT NULL,
	`sysmapid`               bigint unsigned                           NOT NULL,
	`elementid`              bigint unsigned DEFAULT '0'               NOT NULL,
	`elementtype`            integer         DEFAULT '0'               NOT NULL,
	`iconid_off`             bigint unsigned                           NULL,
	`iconid_on`              bigint unsigned                           NULL,
	`label`                  varchar(255)    DEFAULT ''                NOT NULL,
	`label_location`         integer                                   NULL,
	`x`                      integer         DEFAULT '0'               NOT NULL,
	`y`                      integer         DEFAULT '0'               NOT NULL,
	`iconid_disabled`        bigint unsigned                           NULL,
	`iconid_maintenance`     bigint unsigned                           NULL,
	`elementsubtype`         integer         DEFAULT '0'               NOT NULL,
	`areatype`               integer         DEFAULT '0'               NOT NULL,
	`width`                  integer         DEFAULT '200'             NOT NULL,
	`height`                 integer         DEFAULT '200'             NOT NULL,
	`viewtype`               integer         DEFAULT '0'               NOT NULL,
	`use_iconmap`            integer         DEFAULT '1'               NOT NULL,
	PRIMARY KEY (selementid)
) ENGINE=InnoDB;
CREATE TABLE `sysmaps_links` (
	`linkid`                 bigint unsigned                           NOT NULL,
	`sysmapid`               bigint unsigned                           NOT NULL,
	`selementid1`            bigint unsigned                           NOT NULL,
	`selementid2`            bigint unsigned                           NOT NULL,
	`drawtype`               integer         DEFAULT '0'               NOT NULL,
	`color`                  varchar(6)      DEFAULT '000000'          NOT NULL,
	`label`                  varchar(255)    DEFAULT ''                NOT NULL,
	PRIMARY KEY (linkid)
) ENGINE=InnoDB;
CREATE TABLE `sysmaps_link_triggers` (
	`linktriggerid`          bigint unsigned                           NOT NULL,
	`linkid`                 bigint unsigned                           NOT NULL,
	`triggerid`              bigint unsigned                           NOT NULL,
	`drawtype`               integer         DEFAULT '0'               NOT NULL,
	`color`                  varchar(6)      DEFAULT '000000'          NOT NULL,
	PRIMARY KEY (linktriggerid)
) ENGINE=InnoDB;
CREATE UNIQUE INDEX `sysmaps_link_triggers_1` ON `sysmaps_link_triggers` (`linkid`,`triggerid`);
CREATE TABLE `sysmap_element_url` (
	`sysmapelementurlid`     bigint unsigned                           NOT NULL,
	`selementid`             bigint unsigned                           NOT NULL,
	`name`                   varchar(255)                              NOT NULL,
	`url`                    varchar(255)    DEFAULT ''                NOT NULL,
	PRIMARY KEY (sysmapelementurlid)
) ENGINE=InnoDB;
CREATE UNIQUE INDEX `sysmap_element_url_1` ON `sysmap_element_url` (`selementid`,`name`);
CREATE TABLE `sysmap_url` (
	`sysmapurlid`            bigint unsigned                           NOT NULL,
	`sysmapid`               bigint unsigned                           NOT NULL,
	`name`                   varchar(255)                              NOT NULL,
	`url`                    varchar(255)    DEFAULT ''                NOT NULL,
	`elementtype`            integer         DEFAULT '0'               NOT NULL,
	PRIMARY KEY (sysmapurlid)
) ENGINE=InnoDB;
CREATE UNIQUE INDEX `sysmap_url_1` ON `sysmap_url` (`sysmapid`,`name`);
CREATE TABLE `maintenances_hosts` (
	`maintenance_hostid`     bigint unsigned                           NOT NULL,
	`maintenanceid`          bigint unsigned                           NOT NULL,
	`hostid`                 bigint unsigned                           NOT NULL,
	PRIMARY KEY (maintenance_hostid)
) ENGINE=InnoDB;
CREATE UNIQUE INDEX `maintenances_hosts_1` ON `maintenances_hosts` (`maintenanceid`,`hostid`);
CREATE TABLE `maintenances_groups` (
	`maintenance_groupid`    bigint unsigned                           NOT NULL,
	`maintenanceid`          bigint unsigned                           NOT NULL,
	`groupid`                bigint unsigned                           NOT NULL,
	PRIMARY KEY (maintenance_groupid)
) ENGINE=InnoDB;
CREATE UNIQUE INDEX `maintenances_groups_1` ON `maintenances_groups` (`maintenanceid`,`groupid`);
CREATE TABLE `timeperiods` (
	`timeperiodid`           bigint unsigned                           NOT NULL,
	`timeperiod_type`        integer         DEFAULT '0'               NOT NULL,
	`every`                  integer         DEFAULT '0'               NOT NULL,
	`month`                  integer         DEFAULT '0'               NOT NULL,
	`dayofweek`              integer         DEFAULT '0'               NOT NULL,
	`day`                    integer         DEFAULT '0'               NOT NULL,
	`start_time`             integer         DEFAULT '0'               NOT NULL,
	`period`                 integer         DEFAULT '0'               NOT NULL,
	`start_date`             integer         DEFAULT '0'               NOT NULL,
	PRIMARY KEY (timeperiodid)
) ENGINE=InnoDB;
CREATE TABLE `maintenances_windows` (
	`maintenance_timeperiodid` bigint unsigned                           NOT NULL,
	`maintenanceid`          bigint unsigned                           NOT NULL,
	`timeperiodid`           bigint unsigned                           NOT NULL,
	PRIMARY KEY (maintenance_timeperiodid)
) ENGINE=InnoDB;
CREATE UNIQUE INDEX `maintenances_windows_1` ON `maintenances_windows` (`maintenanceid`,`timeperiodid`);
CREATE TABLE `regexps` (
	`regexpid`               bigint unsigned                           NOT NULL,
	`name`                   varchar(128)    DEFAULT ''                NOT NULL,
	`test_string`            text                                      NOT NULL,
	PRIMARY KEY (regexpid)
) ENGINE=InnoDB;
CREATE INDEX `regexps_1` ON `regexps` (`name`);
CREATE TABLE `expressions` (
	`expressionid`           bigint unsigned                           NOT NULL,
	`regexpid`               bigint unsigned                           NOT NULL,
	`expression`             varchar(255)    DEFAULT ''                NOT NULL,
	`expression_type`        integer         DEFAULT '0'               NOT NULL,
	`exp_delimiter`          varchar(1)      DEFAULT ''                NOT NULL,
	`case_sensitive`         integer         DEFAULT '0'               NOT NULL,
	PRIMARY KEY (expressionid)
) ENGINE=InnoDB;
CREATE INDEX `expressions_1` ON `expressions` (`regexpid`);
CREATE TABLE `nodes` (
	`nodeid`                 integer                                   NOT NULL,
	`name`                   varchar(64)     DEFAULT '0'               NOT NULL,
	`ip`                     varchar(39)     DEFAULT ''                NOT NULL,
	`port`                   integer         DEFAULT '10051'           NOT NULL,
	`nodetype`               integer         DEFAULT '0'               NOT NULL,
	`masterid`               integer                                   NULL,
	PRIMARY KEY (nodeid)
) ENGINE=InnoDB;
CREATE TABLE `node_cksum` (
	`nodeid`                 integer                                   NOT NULL,
	`tablename`              varchar(64)     DEFAULT ''                NOT NULL,
	`recordid`               bigint unsigned                           NOT NULL,
	`cksumtype`              integer         DEFAULT '0'               NOT NULL,
	`cksum`                  text                                      NOT NULL,
	`sync`                   char(128)       DEFAULT ''                NOT NULL
) ENGINE=InnoDB;
CREATE INDEX `node_cksum_1` ON `node_cksum` (`nodeid`,`cksumtype`,`tablename`,`recordid`);
CREATE TABLE `ids` (
	`nodeid`                 integer                                   NOT NULL,
	`table_name`             varchar(64)     DEFAULT ''                NOT NULL,
	`field_name`             varchar(64)     DEFAULT ''                NOT NULL,
	`nextid`                 bigint unsigned                           NOT NULL,
	PRIMARY KEY (nodeid,table_name,field_name)
) ENGINE=InnoDB;
CREATE TABLE `alerts` (
	`alertid`                bigint unsigned                           NOT NULL,
	`actionid`               bigint unsigned                           NOT NULL,
	`eventid`                bigint unsigned                           NOT NULL,
	`userid`                 bigint unsigned                           NULL,
	`clock`                  integer         DEFAULT '0'               NOT NULL,
	`mediatypeid`            bigint unsigned                           NULL,
	`sendto`                 varchar(100)    DEFAULT ''                NOT NULL,
	`subject`                varchar(255)    DEFAULT ''                NOT NULL,
	`message`                text                                      NOT NULL,
	`status`                 integer         DEFAULT '0'               NOT NULL,
	`retries`                integer         DEFAULT '0'               NOT NULL,
	`error`                  varchar(128)    DEFAULT ''                NOT NULL,
	`nextcheck`              integer         DEFAULT '0'               NOT NULL,
	`esc_step`               integer         DEFAULT '0'               NOT NULL,
	`alerttype`              integer         DEFAULT '0'               NOT NULL,
	PRIMARY KEY (alertid)
) ENGINE=InnoDB;
CREATE INDEX `alerts_1` ON `alerts` (`actionid`);
CREATE INDEX `alerts_2` ON `alerts` (`clock`);
CREATE INDEX `alerts_3` ON `alerts` (`eventid`);
CREATE INDEX `alerts_4` ON `alerts` (`status`,`retries`);
CREATE INDEX `alerts_5` ON `alerts` (`mediatypeid`);
CREATE INDEX `alerts_6` ON `alerts` (`userid`);
CREATE TABLE `history` (
	`itemid`                 bigint unsigned                           NOT NULL,
	`clock`                  integer         DEFAULT '0'               NOT NULL,
	`value`                  double(16,4)    DEFAULT '0.0000'          NOT NULL,
	`ns`                     integer         DEFAULT '0'               NOT NULL
) ENGINE=InnoDB;
CREATE INDEX `history_1` ON `history` (`itemid`,`clock`);
CREATE TABLE `history_sync` (
	`id`                     bigint unsigned                           NOT NULL auto_increment unique,
	`nodeid`                 integer                                   NOT NULL,
	`itemid`                 bigint unsigned                           NOT NULL,
	`clock`                  integer         DEFAULT '0'               NOT NULL,
	`value`                  double(16,4)    DEFAULT '0.0000'          NOT NULL,
	`ns`                     integer         DEFAULT '0'               NOT NULL,
	PRIMARY KEY (id)
) ENGINE=InnoDB;
CREATE INDEX `history_sync_1` ON `history_sync` (`nodeid`,`id`);
CREATE TABLE `history_uint` (
	`itemid`                 bigint unsigned                           NOT NULL,
	`clock`                  integer         DEFAULT '0'               NOT NULL,
	`value`                  bigint unsigned DEFAULT '0'               NOT NULL,
	`ns`                     integer         DEFAULT '0'               NOT NULL
) ENGINE=InnoDB;
CREATE INDEX `history_uint_1` ON `history_uint` (`itemid`,`clock`);
CREATE TABLE `history_uint_sync` (
	`id`                     bigint unsigned                           NOT NULL auto_increment unique,
	`nodeid`                 integer                                   NOT NULL,
	`itemid`                 bigint unsigned                           NOT NULL,
	`clock`                  integer         DEFAULT '0'               NOT NULL,
	`value`                  bigint unsigned DEFAULT '0'               NOT NULL,
	`ns`                     integer         DEFAULT '0'               NOT NULL,
	PRIMARY KEY (id)
) ENGINE=InnoDB;
CREATE INDEX `history_uint_sync_1` ON `history_uint_sync` (`nodeid`,`id`);
CREATE TABLE `history_str` (
	`itemid`                 bigint unsigned                           NOT NULL,
	`clock`                  integer         DEFAULT '0'               NOT NULL,
	`value`                  varchar(255)    DEFAULT ''                NOT NULL,
	`ns`                     integer         DEFAULT '0'               NOT NULL
) ENGINE=InnoDB;
CREATE INDEX `history_str_1` ON `history_str` (`itemid`,`clock`);
CREATE TABLE `history_str_sync` (
	`id`                     bigint unsigned                           NOT NULL auto_increment unique,
	`nodeid`                 integer                                   NOT NULL,
	`itemid`                 bigint unsigned                           NOT NULL,
	`clock`                  integer         DEFAULT '0'               NOT NULL,
	`value`                  varchar(255)    DEFAULT ''                NOT NULL,
	`ns`                     integer         DEFAULT '0'               NOT NULL,
	PRIMARY KEY (id)
) ENGINE=InnoDB;
CREATE INDEX `history_str_sync_1` ON `history_str_sync` (`nodeid`,`id`);
CREATE TABLE `history_log` (
	`id`                     bigint unsigned                           NOT NULL,
	`itemid`                 bigint unsigned                           NOT NULL,
	`clock`                  integer         DEFAULT '0'               NOT NULL,
	`timestamp`              integer         DEFAULT '0'               NOT NULL,
	`source`                 varchar(64)     DEFAULT ''                NOT NULL,
	`severity`               integer         DEFAULT '0'               NOT NULL,
	`value`                  text                                      NOT NULL,
	`logeventid`             integer         DEFAULT '0'               NOT NULL,
	`ns`                     integer         DEFAULT '0'               NOT NULL,
	PRIMARY KEY (id)
) ENGINE=InnoDB;
CREATE INDEX `history_log_1` ON `history_log` (`itemid`,`clock`);
CREATE UNIQUE INDEX `history_log_2` ON `history_log` (`itemid`,`id`);
CREATE TABLE `history_text` (
	`id`                     bigint unsigned                           NOT NULL,
	`itemid`                 bigint unsigned                           NOT NULL,
	`clock`                  integer         DEFAULT '0'               NOT NULL,
	`value`                  text                                      NOT NULL,
	`ns`                     integer         DEFAULT '0'               NOT NULL,
	PRIMARY KEY (id)
) ENGINE=InnoDB;
CREATE INDEX `history_text_1` ON `history_text` (`itemid`,`clock`);
CREATE UNIQUE INDEX `history_text_2` ON `history_text` (`itemid`,`id`);
CREATE TABLE `proxy_history` (
	`id`                     bigint unsigned                           NOT NULL auto_increment unique,
	`itemid`                 bigint unsigned                           NOT NULL,
	`clock`                  integer         DEFAULT '0'               NOT NULL,
	`timestamp`              integer         DEFAULT '0'               NOT NULL,
	`source`                 varchar(64)     DEFAULT ''                NOT NULL,
	`severity`               integer         DEFAULT '0'               NOT NULL,
	`value`                  longtext                                  NOT NULL,
	`logeventid`             integer         DEFAULT '0'               NOT NULL,
	`ns`                     integer         DEFAULT '0'               NOT NULL,
	`status`                 integer         DEFAULT '0'               NOT NULL,
	PRIMARY KEY (id)
) ENGINE=InnoDB;
CREATE INDEX `proxy_history_1` ON `proxy_history` (`clock`);
CREATE TABLE `proxy_dhistory` (
	`id`                     bigint unsigned                           NOT NULL auto_increment unique,
	`clock`                  integer         DEFAULT '0'               NOT NULL,
	`druleid`                bigint unsigned                           NOT NULL,
	`type`                   integer         DEFAULT '0'               NOT NULL,
	`ip`                     varchar(39)     DEFAULT ''                NOT NULL,
	`port`                   integer         DEFAULT '0'               NOT NULL,
	`key_`                   varchar(255)    DEFAULT ''                NOT NULL,
	`value`                  varchar(255)    DEFAULT ''                NOT NULL,
	`status`                 integer         DEFAULT '0'               NOT NULL,
	`dcheckid`               bigint unsigned                           NULL,
	`dns`                    varchar(64)     DEFAULT ''                NOT NULL,
	PRIMARY KEY (id)
) ENGINE=InnoDB;
CREATE INDEX `proxy_dhistory_1` ON `proxy_dhistory` (`clock`);
CREATE TABLE `events` (
	`eventid`                bigint unsigned                           NOT NULL,
	`source`                 integer         DEFAULT '0'               NOT NULL,
	`object`                 integer         DEFAULT '0'               NOT NULL,
	`objectid`               bigint unsigned DEFAULT '0'               NOT NULL,
	`clock`                  integer         DEFAULT '0'               NOT NULL,
	`value`                  integer         DEFAULT '0'               NOT NULL,
	`acknowledged`           integer         DEFAULT '0'               NOT NULL,
	`ns`                     integer         DEFAULT '0'               NOT NULL,
	`value_changed`          integer         DEFAULT '0'               NOT NULL,
	PRIMARY KEY (eventid)
) ENGINE=InnoDB;
CREATE INDEX `events_1` ON `events` (`object`,`objectid`,`eventid`);
CREATE INDEX `events_2` ON `events` (`clock`);
CREATE TABLE `trends` (
	`itemid`                 bigint unsigned                           NOT NULL,
	`clock`                  integer         DEFAULT '0'               NOT NULL,
	`num`                    integer         DEFAULT '0'               NOT NULL,
	`value_min`              double(16,4)    DEFAULT '0.0000'          NOT NULL,
	`value_avg`              double(16,4)    DEFAULT '0.0000'          NOT NULL,
	`value_max`              double(16,4)    DEFAULT '0.0000'          NOT NULL,
	PRIMARY KEY (itemid,clock)
) ENGINE=InnoDB;
CREATE TABLE `trends_uint` (
	`itemid`                 bigint unsigned                           NOT NULL,
	`clock`                  integer         DEFAULT '0'               NOT NULL,
	`num`                    integer         DEFAULT '0'               NOT NULL,
	`value_min`              bigint unsigned DEFAULT '0'               NOT NULL,
	`value_avg`              bigint unsigned DEFAULT '0'               NOT NULL,
	`value_max`              bigint unsigned DEFAULT '0'               NOT NULL,
	PRIMARY KEY (itemid,clock)
) ENGINE=InnoDB;
CREATE TABLE `acknowledges` (
	`acknowledgeid`          bigint unsigned                           NOT NULL,
	`userid`                 bigint unsigned                           NOT NULL,
	`eventid`                bigint unsigned                           NOT NULL,
	`clock`                  integer         DEFAULT '0'               NOT NULL,
	`message`                varchar(255)    DEFAULT ''                NOT NULL,
	PRIMARY KEY (acknowledgeid)
) ENGINE=InnoDB;
CREATE INDEX `acknowledges_1` ON `acknowledges` (`userid`);
CREATE INDEX `acknowledges_2` ON `acknowledges` (`eventid`);
CREATE INDEX `acknowledges_3` ON `acknowledges` (`clock`);
CREATE TABLE `auditlog` (
	`auditid`                bigint unsigned                           NOT NULL,
	`userid`                 bigint unsigned                           NOT NULL,
	`clock`                  integer         DEFAULT '0'               NOT NULL,
	`action`                 integer         DEFAULT '0'               NOT NULL,
	`resourcetype`           integer         DEFAULT '0'               NOT NULL,
	`details`                varchar(128)    DEFAULT '0'               NOT NULL,
	`ip`                     varchar(39)     DEFAULT ''                NOT NULL,
	`resourceid`             bigint unsigned DEFAULT '0'               NOT NULL,
	`resourcename`           varchar(255)    DEFAULT ''                NOT NULL,
	PRIMARY KEY (auditid)
) ENGINE=InnoDB;
CREATE INDEX `auditlog_1` ON `auditlog` (`userid`,`clock`);
CREATE INDEX `auditlog_2` ON `auditlog` (`clock`);
CREATE TABLE `auditlog_details` (
	`auditdetailid`          bigint unsigned                           NOT NULL,
	`auditid`                bigint unsigned                           NOT NULL,
	`table_name`             varchar(64)     DEFAULT ''                NOT NULL,
	`field_name`             varchar(64)     DEFAULT ''                NOT NULL,
	`oldvalue`               text                                      NOT NULL,
	`newvalue`               text                                      NOT NULL,
	PRIMARY KEY (auditdetailid)
) ENGINE=InnoDB;
CREATE INDEX `auditlog_details_1` ON `auditlog_details` (`auditid`);
CREATE TABLE `service_alarms` (
	`servicealarmid`         bigint unsigned                           NOT NULL,
	`serviceid`              bigint unsigned                           NOT NULL,
	`clock`                  integer         DEFAULT '0'               NOT NULL,
	`value`                  integer         DEFAULT '0'               NOT NULL,
	PRIMARY KEY (servicealarmid)
) ENGINE=InnoDB;
CREATE INDEX `service_alarms_1` ON `service_alarms` (`serviceid`,`clock`);
CREATE INDEX `service_alarms_2` ON `service_alarms` (`clock`);
CREATE TABLE `autoreg_host` (
	`autoreg_hostid`         bigint unsigned                           NOT NULL,
	`proxy_hostid`           bigint unsigned                           NULL,
	`host`                   varchar(64)     DEFAULT ''                NOT NULL,
	`listen_ip`              varchar(39)     DEFAULT ''                NOT NULL,
	`listen_port`            integer         DEFAULT '0'               NOT NULL,
	`listen_dns`             varchar(64)     DEFAULT ''                NOT NULL,
	PRIMARY KEY (autoreg_hostid)
) ENGINE=InnoDB;
CREATE INDEX `autoreg_host_1` ON `autoreg_host` (`proxy_hostid`,`host`);
CREATE TABLE `proxy_autoreg_host` (
	`id`                     bigint unsigned                           NOT NULL auto_increment unique,
	`clock`                  integer         DEFAULT '0'               NOT NULL,
	`host`                   varchar(64)     DEFAULT ''                NOT NULL,
	`listen_ip`              varchar(39)     DEFAULT ''                NOT NULL,
	`listen_port`            integer         DEFAULT '0'               NOT NULL,
	`listen_dns`             varchar(64)     DEFAULT ''                NOT NULL,
	PRIMARY KEY (id)
) ENGINE=InnoDB;
CREATE INDEX `proxy_autoreg_host_1` ON `proxy_autoreg_host` (`clock`);
CREATE TABLE `dhosts` (
	`dhostid`                bigint unsigned                           NOT NULL,
	`druleid`                bigint unsigned                           NOT NULL,
	`status`                 integer         DEFAULT '0'               NOT NULL,
	`lastup`                 integer         DEFAULT '0'               NOT NULL,
	`lastdown`               integer         DEFAULT '0'               NOT NULL,
	PRIMARY KEY (dhostid)
) ENGINE=InnoDB;
CREATE INDEX `dhosts_1` ON `dhosts` (`druleid`);
CREATE TABLE `dservices` (
	`dserviceid`             bigint unsigned                           NOT NULL,
	`dhostid`                bigint unsigned                           NOT NULL,
	`type`                   integer         DEFAULT '0'               NOT NULL,
	`key_`                   varchar(255)    DEFAULT ''                NOT NULL,
	`value`                  varchar(255)    DEFAULT ''                NOT NULL,
	`port`                   integer         DEFAULT '0'               NOT NULL,
	`status`                 integer         DEFAULT '0'               NOT NULL,
	`lastup`                 integer         DEFAULT '0'               NOT NULL,
	`lastdown`               integer         DEFAULT '0'               NOT NULL,
	`dcheckid`               bigint unsigned                           NOT NULL,
	`ip`                     varchar(39)     DEFAULT ''                NOT NULL,
	`dns`                    varchar(64)     DEFAULT ''                NOT NULL,
	PRIMARY KEY (dserviceid)
) ENGINE=InnoDB;
CREATE UNIQUE INDEX `dservices_1` ON `dservices` (`dcheckid`,`type`,`key_`,`ip`,`port`);
CREATE INDEX `dservices_2` ON `dservices` (`dhostid`);
CREATE TABLE `escalations` (
	`escalationid`           bigint unsigned                           NOT NULL,
	`actionid`               bigint unsigned                           NOT NULL,
	`triggerid`              bigint unsigned                           NULL,
	`eventid`                bigint unsigned                           NULL,
	`r_eventid`              bigint unsigned                           NULL,
	`nextcheck`              integer         DEFAULT '0'               NOT NULL,
	`esc_step`               integer         DEFAULT '0'               NOT NULL,
	`status`                 integer         DEFAULT '0'               NOT NULL,
	PRIMARY KEY (escalationid)
) ENGINE=InnoDB;
CREATE INDEX `escalations_1` ON `escalations` (`actionid`,`triggerid`);
CREATE TABLE `globalvars` (
	`globalvarid`            bigint unsigned                           NOT NULL,
	`snmp_lastsize`          integer         DEFAULT '0'               NOT NULL,
	PRIMARY KEY (globalvarid)
) ENGINE=InnoDB;
CREATE TABLE `graph_discovery` (
	`graphdiscoveryid`       bigint unsigned                           NOT NULL,
	`graphid`                bigint unsigned                           NOT NULL,
	`parent_graphid`         bigint unsigned                           NOT NULL,
	`name`                   varchar(128)    DEFAULT ''                NOT NULL,
	PRIMARY KEY (graphdiscoveryid)
) ENGINE=InnoDB;
CREATE UNIQUE INDEX `graph_discovery_1` ON `graph_discovery` (`graphid`,`parent_graphid`);
CREATE TABLE `host_inventory` (
	`hostid`                 bigint unsigned                           NOT NULL,
	`inventory_mode`         integer         DEFAULT '0'               NOT NULL,
	`type`                   varchar(64)     DEFAULT ''                NOT NULL,
	`type_full`              varchar(64)     DEFAULT ''                NOT NULL,
	`name`                   varchar(64)     DEFAULT ''                NOT NULL,
	`alias`                  varchar(64)     DEFAULT ''                NOT NULL,
	`os`                     varchar(64)     DEFAULT ''                NOT NULL,
	`os_full`                varchar(255)    DEFAULT ''                NOT NULL,
	`os_short`               varchar(64)     DEFAULT ''                NOT NULL,
	`serialno_a`             varchar(64)     DEFAULT ''                NOT NULL,
	`serialno_b`             varchar(64)     DEFAULT ''                NOT NULL,
	`tag`                    varchar(64)     DEFAULT ''                NOT NULL,
	`asset_tag`              varchar(64)     DEFAULT ''                NOT NULL,
	`macaddress_a`           varchar(64)     DEFAULT ''                NOT NULL,
	`macaddress_b`           varchar(64)     DEFAULT ''                NOT NULL,
	`hardware`               varchar(255)    DEFAULT ''                NOT NULL,
	`hardware_full`          text                                      NOT NULL,
	`software`               varchar(255)    DEFAULT ''                NOT NULL,
	`software_full`          text                                      NOT NULL,
	`software_app_a`         varchar(64)     DEFAULT ''                NOT NULL,
	`software_app_b`         varchar(64)     DEFAULT ''                NOT NULL,
	`software_app_c`         varchar(64)     DEFAULT ''                NOT NULL,
	`software_app_d`         varchar(64)     DEFAULT ''                NOT NULL,
	`software_app_e`         varchar(64)     DEFAULT ''                NOT NULL,
	`contact`                text                                      NOT NULL,
	`location`               text                                      NOT NULL,
	`location_lat`           varchar(16)     DEFAULT ''                NOT NULL,
	`location_lon`           varchar(16)     DEFAULT ''                NOT NULL,
	`notes`                  text                                      NOT NULL,
	`chassis`                varchar(64)     DEFAULT ''                NOT NULL,
	`model`                  varchar(64)     DEFAULT ''                NOT NULL,
	`hw_arch`                varchar(32)     DEFAULT ''                NOT NULL,
	`vendor`                 varchar(64)     DEFAULT ''                NOT NULL,
	`contract_number`        varchar(64)     DEFAULT ''                NOT NULL,
	`installer_name`         varchar(64)     DEFAULT ''                NOT NULL,
	`deployment_status`      varchar(64)     DEFAULT ''                NOT NULL,
	`url_a`                  varchar(255)    DEFAULT ''                NOT NULL,
	`url_b`                  varchar(255)    DEFAULT ''                NOT NULL,
	`url_c`                  varchar(255)    DEFAULT ''                NOT NULL,
	`host_networks`          text                                      NOT NULL,
	`host_netmask`           varchar(39)     DEFAULT ''                NOT NULL,
	`host_router`            varchar(39)     DEFAULT ''                NOT NULL,
	`oob_ip`                 varchar(39)     DEFAULT ''                NOT NULL,
	`oob_netmask`            varchar(39)     DEFAULT ''                NOT NULL,
	`oob_router`             varchar(39)     DEFAULT ''                NOT NULL,
	`date_hw_purchase`       varchar(64)     DEFAULT ''                NOT NULL,
	`date_hw_install`        varchar(64)     DEFAULT ''                NOT NULL,
	`date_hw_expiry`         varchar(64)     DEFAULT ''                NOT NULL,
	`date_hw_decomm`         varchar(64)     DEFAULT ''                NOT NULL,
	`site_address_a`         varchar(128)    DEFAULT ''                NOT NULL,
	`site_address_b`         varchar(128)    DEFAULT ''                NOT NULL,
	`site_address_c`         varchar(128)    DEFAULT ''                NOT NULL,
	`site_city`              varchar(128)    DEFAULT ''                NOT NULL,
	`site_state`             varchar(64)     DEFAULT ''                NOT NULL,
	`site_country`           varchar(64)     DEFAULT ''                NOT NULL,
	`site_zip`               varchar(64)     DEFAULT ''                NOT NULL,
	`site_rack`              varchar(128)    DEFAULT ''                NOT NULL,
	`site_notes`             text                                      NOT NULL,
	`poc_1_name`             varchar(128)    DEFAULT ''                NOT NULL,
	`poc_1_email`            varchar(128)    DEFAULT ''                NOT NULL,
	`poc_1_phone_a`          varchar(64)     DEFAULT ''                NOT NULL,
	`poc_1_phone_b`          varchar(64)     DEFAULT ''                NOT NULL,
	`poc_1_cell`             varchar(64)     DEFAULT ''                NOT NULL,
	`poc_1_screen`           varchar(64)     DEFAULT ''                NOT NULL,
	`poc_1_notes`            text                                      NOT NULL,
	`poc_2_name`             varchar(128)    DEFAULT ''                NOT NULL,
	`poc_2_email`            varchar(128)    DEFAULT ''                NOT NULL,
	`poc_2_phone_a`          varchar(64)     DEFAULT ''                NOT NULL,
	`poc_2_phone_b`          varchar(64)     DEFAULT ''                NOT NULL,
	`poc_2_cell`             varchar(64)     DEFAULT ''                NOT NULL,
	`poc_2_screen`           varchar(64)     DEFAULT ''                NOT NULL,
	`poc_2_notes`            text                                      NOT NULL,
	PRIMARY KEY (hostid)
) ENGINE=InnoDB;
CREATE TABLE `housekeeper` (
	`housekeeperid`          bigint unsigned                           NOT NULL,
	`tablename`              varchar(64)     DEFAULT ''                NOT NULL,
	`field`                  varchar(64)     DEFAULT ''                NOT NULL,
	`value`                  bigint unsigned                           NOT NULL,
	PRIMARY KEY (housekeeperid)
) ENGINE=InnoDB;
CREATE TABLE `images` (
	`imageid`                bigint unsigned                           NOT NULL,
	`imagetype`              integer         DEFAULT '0'               NOT NULL,
	`name`                   varchar(64)     DEFAULT '0'               NOT NULL,
	`image`                  longblob                                  NOT NULL,
	PRIMARY KEY (imageid)
) ENGINE=InnoDB;
CREATE INDEX `images_1` ON `images` (`imagetype`,`name`);
CREATE TABLE `item_discovery` (
	`itemdiscoveryid`        bigint unsigned                           NOT NULL,
	`itemid`                 bigint unsigned                           NOT NULL,
	`parent_itemid`          bigint unsigned                           NOT NULL,
	`key_`                   varchar(255)    DEFAULT ''                NOT NULL,
	`lastcheck`              integer         DEFAULT '0'               NOT NULL,
	`ts_delete`              integer         DEFAULT '0'               NOT NULL,
	PRIMARY KEY (itemdiscoveryid)
) ENGINE=InnoDB;
CREATE UNIQUE INDEX `item_discovery_1` ON `item_discovery` (`itemid`,`parent_itemid`);
CREATE TABLE `profiles` (
	`profileid`              bigint unsigned                           NOT NULL,
	`userid`                 bigint unsigned                           NOT NULL,
	`idx`                    varchar(96)     DEFAULT ''                NOT NULL,
	`idx2`                   bigint unsigned DEFAULT '0'               NOT NULL,
	`value_id`               bigint unsigned DEFAULT '0'               NOT NULL,
	`value_int`              integer         DEFAULT '0'               NOT NULL,
	`value_str`              varchar(255)    DEFAULT ''                NOT NULL,
	`source`                 varchar(96)     DEFAULT ''                NOT NULL,
	`type`                   integer         DEFAULT '0'               NOT NULL,
	PRIMARY KEY (profileid)
) ENGINE=InnoDB;
CREATE INDEX `profiles_1` ON `profiles` (`userid`,`idx`,`idx2`);
CREATE INDEX `profiles_2` ON `profiles` (`userid`,`profileid`);
CREATE TABLE `sessions` (
	`sessionid`              varchar(32)     DEFAULT ''                NOT NULL,
	`userid`                 bigint unsigned                           NOT NULL,
	`lastaccess`             integer         DEFAULT '0'               NOT NULL,
	`status`                 integer         DEFAULT '0'               NOT NULL,
	PRIMARY KEY (sessionid)
) ENGINE=InnoDB;
CREATE INDEX `sessions_1` ON `sessions` (`userid`,`status`);
CREATE TABLE `trigger_discovery` (
	`triggerdiscoveryid`     bigint unsigned                           NOT NULL,
	`triggerid`              bigint unsigned                           NOT NULL,
	`parent_triggerid`       bigint unsigned                           NOT NULL,
	`name`                   varchar(255)    DEFAULT ''                NOT NULL,
	PRIMARY KEY (triggerdiscoveryid)
) ENGINE=InnoDB;
CREATE UNIQUE INDEX `trigger_discovery_1` ON `trigger_discovery` (`triggerid`,`parent_triggerid`);
CREATE TABLE `user_history` (
	`userhistoryid`          bigint unsigned                           NOT NULL,
	`userid`                 bigint unsigned                           NOT NULL,
	`title1`                 varchar(255)    DEFAULT ''                NOT NULL,
	`url1`                   varchar(255)    DEFAULT ''                NOT NULL,
	`title2`                 varchar(255)    DEFAULT ''                NOT NULL,
	`url2`                   varchar(255)    DEFAULT ''                NOT NULL,
	`title3`                 varchar(255)    DEFAULT ''                NOT NULL,
	`url3`                   varchar(255)    DEFAULT ''                NOT NULL,
	`title4`                 varchar(255)    DEFAULT ''                NOT NULL,
	`url4`                   varchar(255)    DEFAULT ''                NOT NULL,
	`title5`                 varchar(255)    DEFAULT ''                NOT NULL,
	`url5`                   varchar(255)    DEFAULT ''                NOT NULL,
	PRIMARY KEY (userhistoryid)
) ENGINE=InnoDB;
CREATE UNIQUE INDEX `user_history_1` ON `user_history` (`userid`);
ALTER TABLE `hosts` ADD CONSTRAINT `c_hosts_1` FOREIGN KEY (`proxy_hostid`) REFERENCES `hosts` (`hostid`);
ALTER TABLE `hosts` ADD CONSTRAINT `c_hosts_2` FOREIGN KEY (`maintenanceid`) REFERENCES `maintenances` (`maintenanceid`);
ALTER TABLE `screens` ADD CONSTRAINT `c_screens_1` FOREIGN KEY (`templateid`) REFERENCES `hosts` (`hostid`) ON DELETE CASCADE;
ALTER TABLE `screens_items` ADD CONSTRAINT `c_screens_items_1` FOREIGN KEY (`screenid`) REFERENCES `screens` (`screenid`) ON DELETE CASCADE;
ALTER TABLE `slides` ADD CONSTRAINT `c_slides_1` FOREIGN KEY (`slideshowid`) REFERENCES `slideshows` (`slideshowid`) ON DELETE CASCADE;
ALTER TABLE `slides` ADD CONSTRAINT `c_slides_2` FOREIGN KEY (`screenid`) REFERENCES `screens` (`screenid`) ON DELETE CASCADE;
ALTER TABLE `drules` ADD CONSTRAINT `c_drules_1` FOREIGN KEY (`proxy_hostid`) REFERENCES `hosts` (`hostid`);
ALTER TABLE `dchecks` ADD CONSTRAINT `c_dchecks_1` FOREIGN KEY (`druleid`) REFERENCES `drules` (`druleid`) ON DELETE CASCADE;
ALTER TABLE `applications` ADD CONSTRAINT `c_applications_1` FOREIGN KEY (`hostid`) REFERENCES `hosts` (`hostid`) ON DELETE CASCADE;
ALTER TABLE `applications` ADD CONSTRAINT `c_applications_2` FOREIGN KEY (`templateid`) REFERENCES `applications` (`applicationid`) ON DELETE CASCADE;
ALTER TABLE `httptest` ADD CONSTRAINT `c_httptest_1` FOREIGN KEY (`applicationid`) REFERENCES `applications` (`applicationid`) ON DELETE CASCADE;
ALTER TABLE `httpstep` ADD CONSTRAINT `c_httpstep_1` FOREIGN KEY (`httptestid`) REFERENCES `httptest` (`httptestid`) ON DELETE CASCADE;
ALTER TABLE `interface` ADD CONSTRAINT `c_interface_1` FOREIGN KEY (`hostid`) REFERENCES `hosts` (`hostid`) ON DELETE CASCADE;
ALTER TABLE `items` ADD CONSTRAINT `c_items_1` FOREIGN KEY (`hostid`) REFERENCES `hosts` (`hostid`) ON DELETE CASCADE;
ALTER TABLE `items` ADD CONSTRAINT `c_items_2` FOREIGN KEY (`templateid`) REFERENCES `items` (`itemid`) ON DELETE CASCADE;
ALTER TABLE `items` ADD CONSTRAINT `c_items_3` FOREIGN KEY (`valuemapid`) REFERENCES `valuemaps` (`valuemapid`);
ALTER TABLE `items` ADD CONSTRAINT `c_items_4` FOREIGN KEY (`interfaceid`) REFERENCES `interface` (`interfaceid`);
ALTER TABLE `httpstepitem` ADD CONSTRAINT `c_httpstepitem_1` FOREIGN KEY (`httpstepid`) REFERENCES `httpstep` (`httpstepid`) ON DELETE CASCADE;
ALTER TABLE `httpstepitem` ADD CONSTRAINT `c_httpstepitem_2` FOREIGN KEY (`itemid`) REFERENCES `items` (`itemid`) ON DELETE CASCADE;
ALTER TABLE `httptestitem` ADD CONSTRAINT `c_httptestitem_1` FOREIGN KEY (`httptestid`) REFERENCES `httptest` (`httptestid`) ON DELETE CASCADE;
ALTER TABLE `httptestitem` ADD CONSTRAINT `c_httptestitem_2` FOREIGN KEY (`itemid`) REFERENCES `items` (`itemid`) ON DELETE CASCADE;
ALTER TABLE `users_groups` ADD CONSTRAINT `c_users_groups_1` FOREIGN KEY (`usrgrpid`) REFERENCES `usrgrp` (`usrgrpid`) ON DELETE CASCADE;
ALTER TABLE `users_groups` ADD CONSTRAINT `c_users_groups_2` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON DELETE CASCADE;
ALTER TABLE `scripts` ADD CONSTRAINT `c_scripts_1` FOREIGN KEY (`usrgrpid`) REFERENCES `usrgrp` (`usrgrpid`);
ALTER TABLE `scripts` ADD CONSTRAINT `c_scripts_2` FOREIGN KEY (`groupid`) REFERENCES `groups` (`groupid`);
ALTER TABLE `operations` ADD CONSTRAINT `c_operations_1` FOREIGN KEY (`actionid`) REFERENCES `actions` (`actionid`) ON DELETE CASCADE;
ALTER TABLE `opmessage` ADD CONSTRAINT `c_opmessage_1` FOREIGN KEY (`operationid`) REFERENCES `operations` (`operationid`) ON DELETE CASCADE;
ALTER TABLE `opmessage` ADD CONSTRAINT `c_opmessage_2` FOREIGN KEY (`mediatypeid`) REFERENCES `media_type` (`mediatypeid`);
ALTER TABLE `opmessage_grp` ADD CONSTRAINT `c_opmessage_grp_1` FOREIGN KEY (`operationid`) REFERENCES `operations` (`operationid`) ON DELETE CASCADE;
ALTER TABLE `opmessage_grp` ADD CONSTRAINT `c_opmessage_grp_2` FOREIGN KEY (`usrgrpid`) REFERENCES `usrgrp` (`usrgrpid`);
ALTER TABLE `opmessage_usr` ADD CONSTRAINT `c_opmessage_usr_1` FOREIGN KEY (`operationid`) REFERENCES `operations` (`operationid`) ON DELETE CASCADE;
ALTER TABLE `opmessage_usr` ADD CONSTRAINT `c_opmessage_usr_2` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`);
ALTER TABLE `opcommand` ADD CONSTRAINT `c_opcommand_1` FOREIGN KEY (`operationid`) REFERENCES `operations` (`operationid`) ON DELETE CASCADE;
ALTER TABLE `opcommand` ADD CONSTRAINT `c_opcommand_2` FOREIGN KEY (`scriptid`) REFERENCES `scripts` (`scriptid`);
ALTER TABLE `opcommand_hst` ADD CONSTRAINT `c_opcommand_hst_1` FOREIGN KEY (`operationid`) REFERENCES `operations` (`operationid`) ON DELETE CASCADE;
ALTER TABLE `opcommand_hst` ADD CONSTRAINT `c_opcommand_hst_2` FOREIGN KEY (`hostid`) REFERENCES `hosts` (`hostid`);
ALTER TABLE `opcommand_grp` ADD CONSTRAINT `c_opcommand_grp_1` FOREIGN KEY (`operationid`) REFERENCES `operations` (`operationid`) ON DELETE CASCADE;
ALTER TABLE `opcommand_grp` ADD CONSTRAINT `c_opcommand_grp_2` FOREIGN KEY (`groupid`) REFERENCES `groups` (`groupid`);
ALTER TABLE `opgroup` ADD CONSTRAINT `c_opgroup_1` FOREIGN KEY (`operationid`) REFERENCES `operations` (`operationid`) ON DELETE CASCADE;
ALTER TABLE `opgroup` ADD CONSTRAINT `c_opgroup_2` FOREIGN KEY (`groupid`) REFERENCES `groups` (`groupid`);
ALTER TABLE `optemplate` ADD CONSTRAINT `c_optemplate_1` FOREIGN KEY (`operationid`) REFERENCES `operations` (`operationid`) ON DELETE CASCADE;
ALTER TABLE `optemplate` ADD CONSTRAINT `c_optemplate_2` FOREIGN KEY (`templateid`) REFERENCES `hosts` (`hostid`);
ALTER TABLE `opconditions` ADD CONSTRAINT `c_opconditions_1` FOREIGN KEY (`operationid`) REFERENCES `operations` (`operationid`) ON DELETE CASCADE;
ALTER TABLE `conditions` ADD CONSTRAINT `c_conditions_1` FOREIGN KEY (`actionid`) REFERENCES `actions` (`actionid`) ON DELETE CASCADE;
ALTER TABLE `config` ADD CONSTRAINT `c_config_1` FOREIGN KEY (`alert_usrgrpid`) REFERENCES `usrgrp` (`usrgrpid`);
ALTER TABLE `config` ADD CONSTRAINT `c_config_2` FOREIGN KEY (`discovery_groupid`) REFERENCES `groups` (`groupid`);
ALTER TABLE `triggers` ADD CONSTRAINT `c_triggers_1` FOREIGN KEY (`templateid`) REFERENCES `triggers` (`triggerid`) ON DELETE CASCADE;
ALTER TABLE `trigger_depends` ADD CONSTRAINT `c_trigger_depends_1` FOREIGN KEY (`triggerid_down`) REFERENCES `triggers` (`triggerid`) ON DELETE CASCADE;
ALTER TABLE `trigger_depends` ADD CONSTRAINT `c_trigger_depends_2` FOREIGN KEY (`triggerid_up`) REFERENCES `triggers` (`triggerid`) ON DELETE CASCADE;
ALTER TABLE `functions` ADD CONSTRAINT `c_functions_1` FOREIGN KEY (`itemid`) REFERENCES `items` (`itemid`) ON DELETE CASCADE;
ALTER TABLE `functions` ADD CONSTRAINT `c_functions_2` FOREIGN KEY (`triggerid`) REFERENCES `triggers` (`triggerid`) ON DELETE CASCADE;
ALTER TABLE `graphs` ADD CONSTRAINT `c_graphs_1` FOREIGN KEY (`templateid`) REFERENCES `graphs` (`graphid`) ON DELETE CASCADE;
ALTER TABLE `graphs` ADD CONSTRAINT `c_graphs_2` FOREIGN KEY (`ymin_itemid`) REFERENCES `items` (`itemid`);
ALTER TABLE `graphs` ADD CONSTRAINT `c_graphs_3` FOREIGN KEY (`ymax_itemid`) REFERENCES `items` (`itemid`);
ALTER TABLE `graphs_items` ADD CONSTRAINT `c_graphs_items_1` FOREIGN KEY (`graphid`) REFERENCES `graphs` (`graphid`) ON DELETE CASCADE;
ALTER TABLE `graphs_items` ADD CONSTRAINT `c_graphs_items_2` FOREIGN KEY (`itemid`) REFERENCES `items` (`itemid`) ON DELETE CASCADE;
ALTER TABLE `hostmacro` ADD CONSTRAINT `c_hostmacro_1` FOREIGN KEY (`hostid`) REFERENCES `hosts` (`hostid`) ON DELETE CASCADE;
ALTER TABLE `hosts_groups` ADD CONSTRAINT `c_hosts_groups_1` FOREIGN KEY (`hostid`) REFERENCES `hosts` (`hostid`) ON DELETE CASCADE;
ALTER TABLE `hosts_groups` ADD CONSTRAINT `c_hosts_groups_2` FOREIGN KEY (`groupid`) REFERENCES `groups` (`groupid`) ON DELETE CASCADE;
ALTER TABLE `hosts_templates` ADD CONSTRAINT `c_hosts_templates_1` FOREIGN KEY (`hostid`) REFERENCES `hosts` (`hostid`) ON DELETE CASCADE;
ALTER TABLE `hosts_templates` ADD CONSTRAINT `c_hosts_templates_2` FOREIGN KEY (`templateid`) REFERENCES `hosts` (`hostid`) ON DELETE CASCADE;
ALTER TABLE `items_applications` ADD CONSTRAINT `c_items_applications_1` FOREIGN KEY (`applicationid`) REFERENCES `applications` (`applicationid`) ON DELETE CASCADE;
ALTER TABLE `items_applications` ADD CONSTRAINT `c_items_applications_2` FOREIGN KEY (`itemid`) REFERENCES `items` (`itemid`) ON DELETE CASCADE;
ALTER TABLE `mappings` ADD CONSTRAINT `c_mappings_1` FOREIGN KEY (`valuemapid`) REFERENCES `valuemaps` (`valuemapid`) ON DELETE CASCADE;
ALTER TABLE `media` ADD CONSTRAINT `c_media_1` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON DELETE CASCADE;
ALTER TABLE `media` ADD CONSTRAINT `c_media_2` FOREIGN KEY (`mediatypeid`) REFERENCES `media_type` (`mediatypeid`) ON DELETE CASCADE;
ALTER TABLE `rights` ADD CONSTRAINT `c_rights_1` FOREIGN KEY (`groupid`) REFERENCES `usrgrp` (`usrgrpid`) ON DELETE CASCADE;
ALTER TABLE `rights` ADD CONSTRAINT `c_rights_2` FOREIGN KEY (`id`) REFERENCES `groups` (`groupid`) ON DELETE CASCADE;
ALTER TABLE `services` ADD CONSTRAINT `c_services_1` FOREIGN KEY (`triggerid`) REFERENCES `triggers` (`triggerid`) ON DELETE CASCADE;
ALTER TABLE `services_links` ADD CONSTRAINT `c_services_links_1` FOREIGN KEY (`serviceupid`) REFERENCES `services` (`serviceid`) ON DELETE CASCADE;
ALTER TABLE `services_links` ADD CONSTRAINT `c_services_links_2` FOREIGN KEY (`servicedownid`) REFERENCES `services` (`serviceid`) ON DELETE CASCADE;
ALTER TABLE `services_times` ADD CONSTRAINT `c_services_times_1` FOREIGN KEY (`serviceid`) REFERENCES `services` (`serviceid`) ON DELETE CASCADE;
ALTER TABLE `icon_map` ADD CONSTRAINT `c_icon_map_1` FOREIGN KEY (`default_iconid`) REFERENCES `images` (`imageid`);
ALTER TABLE `icon_mapping` ADD CONSTRAINT `c_icon_mapping_1` FOREIGN KEY (`iconmapid`) REFERENCES `icon_map` (`iconmapid`) ON DELETE CASCADE;
ALTER TABLE `icon_mapping` ADD CONSTRAINT `c_icon_mapping_2` FOREIGN KEY (`iconid`) REFERENCES `images` (`imageid`);
ALTER TABLE `sysmaps` ADD CONSTRAINT `c_sysmaps_1` FOREIGN KEY (`backgroundid`) REFERENCES `images` (`imageid`);
ALTER TABLE `sysmaps` ADD CONSTRAINT `c_sysmaps_2` FOREIGN KEY (`iconmapid`) REFERENCES `icon_map` (`iconmapid`);
ALTER TABLE `sysmaps_elements` ADD CONSTRAINT `c_sysmaps_elements_1` FOREIGN KEY (`sysmapid`) REFERENCES `sysmaps` (`sysmapid`) ON DELETE CASCADE;
ALTER TABLE `sysmaps_elements` ADD CONSTRAINT `c_sysmaps_elements_2` FOREIGN KEY (`iconid_off`) REFERENCES `images` (`imageid`);
ALTER TABLE `sysmaps_elements` ADD CONSTRAINT `c_sysmaps_elements_3` FOREIGN KEY (`iconid_on`) REFERENCES `images` (`imageid`);
ALTER TABLE `sysmaps_elements` ADD CONSTRAINT `c_sysmaps_elements_4` FOREIGN KEY (`iconid_disabled`) REFERENCES `images` (`imageid`);
ALTER TABLE `sysmaps_elements` ADD CONSTRAINT `c_sysmaps_elements_5` FOREIGN KEY (`iconid_maintenance`) REFERENCES `images` (`imageid`);
ALTER TABLE `sysmaps_links` ADD CONSTRAINT `c_sysmaps_links_1` FOREIGN KEY (`sysmapid`) REFERENCES `sysmaps` (`sysmapid`) ON DELETE CASCADE;
ALTER TABLE `sysmaps_links` ADD CONSTRAINT `c_sysmaps_links_2` FOREIGN KEY (`selementid1`) REFERENCES `sysmaps_elements` (`selementid`) ON DELETE CASCADE;
ALTER TABLE `sysmaps_links` ADD CONSTRAINT `c_sysmaps_links_3` FOREIGN KEY (`selementid2`) REFERENCES `sysmaps_elements` (`selementid`) ON DELETE CASCADE;
ALTER TABLE `sysmaps_link_triggers` ADD CONSTRAINT `c_sysmaps_link_triggers_1` FOREIGN KEY (`linkid`) REFERENCES `sysmaps_links` (`linkid`) ON DELETE CASCADE;
ALTER TABLE `sysmaps_link_triggers` ADD CONSTRAINT `c_sysmaps_link_triggers_2` FOREIGN KEY (`triggerid`) REFERENCES `triggers` (`triggerid`) ON DELETE CASCADE;
ALTER TABLE `sysmap_element_url` ADD CONSTRAINT `c_sysmap_element_url_1` FOREIGN KEY (`selementid`) REFERENCES `sysmaps_elements` (`selementid`) ON DELETE CASCADE;
ALTER TABLE `sysmap_url` ADD CONSTRAINT `c_sysmap_url_1` FOREIGN KEY (`sysmapid`) REFERENCES `sysmaps` (`sysmapid`) ON DELETE CASCADE;
ALTER TABLE `maintenances_hosts` ADD CONSTRAINT `c_maintenances_hosts_1` FOREIGN KEY (`maintenanceid`) REFERENCES `maintenances` (`maintenanceid`) ON DELETE CASCADE;
ALTER TABLE `maintenances_hosts` ADD CONSTRAINT `c_maintenances_hosts_2` FOREIGN KEY (`hostid`) REFERENCES `hosts` (`hostid`) ON DELETE CASCADE;
ALTER TABLE `maintenances_groups` ADD CONSTRAINT `c_maintenances_groups_1` FOREIGN KEY (`maintenanceid`) REFERENCES `maintenances` (`maintenanceid`) ON DELETE CASCADE;
ALTER TABLE `maintenances_groups` ADD CONSTRAINT `c_maintenances_groups_2` FOREIGN KEY (`groupid`) REFERENCES `groups` (`groupid`) ON DELETE CASCADE;
ALTER TABLE `maintenances_windows` ADD CONSTRAINT `c_maintenances_windows_1` FOREIGN KEY (`maintenanceid`) REFERENCES `maintenances` (`maintenanceid`) ON DELETE CASCADE;
ALTER TABLE `maintenances_windows` ADD CONSTRAINT `c_maintenances_windows_2` FOREIGN KEY (`timeperiodid`) REFERENCES `timeperiods` (`timeperiodid`) ON DELETE CASCADE;
ALTER TABLE `expressions` ADD CONSTRAINT `c_expressions_1` FOREIGN KEY (`regexpid`) REFERENCES `regexps` (`regexpid`) ON DELETE CASCADE;
ALTER TABLE `nodes` ADD CONSTRAINT `c_nodes_1` FOREIGN KEY (`masterid`) REFERENCES `nodes` (`nodeid`);
ALTER TABLE `node_cksum` ADD CONSTRAINT `c_node_cksum_1` FOREIGN KEY (`nodeid`) REFERENCES `nodes` (`nodeid`) ON DELETE CASCADE;
ALTER TABLE `alerts` ADD CONSTRAINT `c_alerts_1` FOREIGN KEY (`actionid`) REFERENCES `actions` (`actionid`) ON DELETE CASCADE;
ALTER TABLE `alerts` ADD CONSTRAINT `c_alerts_2` FOREIGN KEY (`eventid`) REFERENCES `events` (`eventid`) ON DELETE CASCADE;
ALTER TABLE `alerts` ADD CONSTRAINT `c_alerts_3` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON DELETE CASCADE;
ALTER TABLE `alerts` ADD CONSTRAINT `c_alerts_4` FOREIGN KEY (`mediatypeid`) REFERENCES `media_type` (`mediatypeid`) ON DELETE CASCADE;
ALTER TABLE `acknowledges` ADD CONSTRAINT `c_acknowledges_1` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON DELETE CASCADE;
ALTER TABLE `acknowledges` ADD CONSTRAINT `c_acknowledges_2` FOREIGN KEY (`eventid`) REFERENCES `events` (`eventid`) ON DELETE CASCADE;
ALTER TABLE `auditlog` ADD CONSTRAINT `c_auditlog_1` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON DELETE CASCADE;
ALTER TABLE `auditlog_details` ADD CONSTRAINT `c_auditlog_details_1` FOREIGN KEY (`auditid`) REFERENCES `auditlog` (`auditid`) ON DELETE CASCADE;
ALTER TABLE `service_alarms` ADD CONSTRAINT `c_service_alarms_1` FOREIGN KEY (`serviceid`) REFERENCES `services` (`serviceid`) ON DELETE CASCADE;
ALTER TABLE `autoreg_host` ADD CONSTRAINT `c_autoreg_host_1` FOREIGN KEY (`proxy_hostid`) REFERENCES `hosts` (`hostid`) ON DELETE CASCADE;
ALTER TABLE `dhosts` ADD CONSTRAINT `c_dhosts_1` FOREIGN KEY (`druleid`) REFERENCES `drules` (`druleid`) ON DELETE CASCADE;
ALTER TABLE `dservices` ADD CONSTRAINT `c_dservices_1` FOREIGN KEY (`dhostid`) REFERENCES `dhosts` (`dhostid`) ON DELETE CASCADE;
ALTER TABLE `dservices` ADD CONSTRAINT `c_dservices_2` FOREIGN KEY (`dcheckid`) REFERENCES `dchecks` (`dcheckid`) ON DELETE CASCADE;
ALTER TABLE `graph_discovery` ADD CONSTRAINT `c_graph_discovery_1` FOREIGN KEY (`graphid`) REFERENCES `graphs` (`graphid`) ON DELETE CASCADE;
ALTER TABLE `graph_discovery` ADD CONSTRAINT `c_graph_discovery_2` FOREIGN KEY (`parent_graphid`) REFERENCES `graphs` (`graphid`) ON DELETE CASCADE;
ALTER TABLE `host_inventory` ADD CONSTRAINT `c_host_inventory_1` FOREIGN KEY (`hostid`) REFERENCES `hosts` (`hostid`) ON DELETE CASCADE;
ALTER TABLE `item_discovery` ADD CONSTRAINT `c_item_discovery_1` FOREIGN KEY (`itemid`) REFERENCES `items` (`itemid`) ON DELETE CASCADE;
ALTER TABLE `item_discovery` ADD CONSTRAINT `c_item_discovery_2` FOREIGN KEY (`parent_itemid`) REFERENCES `items` (`itemid`) ON DELETE CASCADE;
ALTER TABLE `profiles` ADD CONSTRAINT `c_profiles_1` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON DELETE CASCADE;
ALTER TABLE `sessions` ADD CONSTRAINT `c_sessions_1` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON DELETE CASCADE;
ALTER TABLE `trigger_discovery` ADD CONSTRAINT `c_trigger_discovery_1` FOREIGN KEY (`triggerid`) REFERENCES `triggers` (`triggerid`) ON DELETE CASCADE;
ALTER TABLE `trigger_discovery` ADD CONSTRAINT `c_trigger_discovery_2` FOREIGN KEY (`parent_triggerid`) REFERENCES `triggers` (`triggerid`) ON DELETE CASCADE;
ALTER TABLE `user_history` ADD CONSTRAINT `c_user_history_1` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON DELETE CASCADE;
