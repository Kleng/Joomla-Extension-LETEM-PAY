# Files
DROP TABLE IF EXISTS `#__letempay_files_details`;

CREATE TABLE `#__letempay_files_details` (
    `id` integer NOT NULL auto_increment,
    `file_blob` longblob,
    `file_title` varchar(255) NOT NULL default '',
    `file_name` varchar(200) DEFAULT NULL,
    `file_size` varchar(45) DEFAULT NULL,
    `file_type` varchar(45) DEFAULT NULL,
    `hits` int(10) unsigned NOT NULL DEFAULT '0',
    `state` tinyint(3) NOT NULL DEFAULT '0',
    `checked_out` integer unsigned NOT NULL default '0',
    `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
    `ordering` integer NOT NULL default '0',
    `params` text NOT NULL,
    `catid` integer NOT NULL default '0',
    `created` datetime NOT NULL default '0000-00-00 00:00:00',
    `created_by` int(10) unsigned NOT NULL default '0',
    `created_by_alias` varchar(255) NOT NULL default '',
    `modified` datetime NOT NULL default '0000-00-00 00:00:00',
    `modified_by` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_state` (`state`),
  KEY `idx_catid` (`catid`),
  KEY `idx_createdby` (`created_by`)
)  DEFAULT CHARSET=utf8;


# Transaction
DROP TABLE IF EXISTS `#__letempay_transactions`;

CREATE TABLE `#__letempay_transactions` (
    `id` integer NOT NULL auto_increment,
    `API_Environment` varchar(50) DEFAULT NULL,
    `catid` integer NOT NULL default '0',
    `token` text DEFAULT NULL,
    `request_SetExpressCheckoutDG` text DEFAULT NULL,
    `result_SetExpressCheckoutDG` text DEFAULT NULL,
    `request_GetExpressCheckoutDetails` text DEFAULT NULL,
    `result_GetExpressCheckoutDetails` text DEFAULT NULL,
    `request_ConfirmPayment` text DEFAULT NULL,
    `result_ConfirmPayment` text DEFAULT NULL,
    `state` tinyint(3) NOT NULL DEFAULT '0',
    `params` text NOT NULL,
    `created` datetime NOT NULL default '0000-00-00 00:00:00',
    `created_by` int(10) unsigned NOT NULL default '0',
    `modified` datetime NOT NULL default '0000-00-00 00:00:00',
    `modified_by` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_state` (`state`),
  KEY `idx_catid` (`catid`),
  KEY `idx_createdby` (`created_by`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;