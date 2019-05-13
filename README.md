# Map Index Module

## Installation

### Composer.json
	"require" : {
		"zendframework/zend-authentication" : "*",
		"zendframework/zend-db" : "*",
		"zendframework/zend-i18n" : "*",
		"zendframework/zend-form" : "*",
		"zendframework/zend-crypt" : "*",
		"zendframework/zend-session" : "*",
	}, 
	"autoload" : {
		"psr-4" : {
			"Annotation\\" : "module/Annotation/src",
			"Mapindex\\" : "modules/Mapindex/src",
			"Midnet\\" : "module/Midnet/src",
			"User\\" : "module/User/src",
		},
	},

### modules.config.php
	return [
		'Mapindex',
		'Midnet',
	   'Streetlamp',
	   'User',
	];
	
### local.php

Edit your local.php to include specific settings for your database.  Be sure to enter proper values for host, database name, user and pass.  This module uses a database connection called `mapindex-model-primary-adapter-config`, which is aliased to `model-primary-adapter-config` in the Map Index Module Config.  This allows for one database to hold multiple module tables, or point to different databases.

	return [
		'service_manager' => [
		    'services' => [
		        'model-primary-adapter-config' => [
		            'driver' => 'PDO',
		            'dsn' => 'mysql:host=host.domain.com;dbname=custom-database_name',
		            'username' => 'user-name',
		            'password' => 'CoMpLeXpAsSwOrD',
		        ],
		    ],
		],
	];

### Database Table Structure

	CREATE TABLE IF NOT EXISTS `streetlamps` (
	  `UUID` varchar(36) NOT NULL,
	  `STREET` varchar(255) NOT NULL,
	  `INTERSECTING_STREET` varchar(255) NOT NULL,
	  `HOUSE_NUMBER` int(11) NOT NULL,
	  `POLE_NUMBER` int(11) NOT NULL,
	  `ACTION` varchar(36) NOT NULL,
	  `DESCRIPTION` text NOT NULL,
	  `PHONE_NUMBER` varchar(255) NOT NULL,
	  `DATE_CREATED` timestamp NULL DEFAULT NULL,
	  `DATE_MODIFIED` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	
	CREATE TABLE IF NOT EXISTS `actions` (
		`UUID` varchar(36) NOT NULL,
		`ACTION` varchar(255) NOT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

	ALTER TABLE `streetlamps`
	  ADD PRIMARY KEY (`UUID`),
	  ADD UNIQUE KEY `UUID` (`UUID`);
	  
	ALTER TABLE `actions`
		ADD PRIMARY KEY (`UUID`);