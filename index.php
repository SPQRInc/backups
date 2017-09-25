<?php

return [
	'name' => 'spqr/backups',
	'type' => 'extension',
	'main' => function( $app ) {
	},
	
	'autoload' => [
		'Spqr\\Backups\\' => 'src'
	],
	
	'routes'  => [
		'/backups'     => [
			'name'       => '@backups',
			'controller' => [
				'Spqr\\Backups\\Controller\\BackupsController',
				'Spqr\\Backups\\Controller\\BackupController',
			]
		],
		'/api/backups' => [
			'name'       => '@backups/api',
			'controller' => [
				'Spqr\\Backups\\Controller\\BackupApiController'
			]
		]
	],
	'widgets' => [],
	
	'menu'        => [
		'backups'           => [
			'label'  => 'Backups',
			'url'    => '@backups/backup',
			'active' => '@backups/backup*',
			'icon'   => 'spqr/backups:icon.svg'
		],
		'backups: backup'   => [
			'parent' => 'backups',
			'label'  => 'Backups',
			'icon'   => 'spqr/backups:icon.svg',
			'url'    => '@backups/backup',
			'access' => 'backups: manage backups',
			'active' => '@backups/backup*'
		],
		'backups: settings' => [
			'parent' => 'backups',
			'label'  => 'Settings',
			'url'    => '@backups/settings',
			'access' => 'backups: manage settings'
		]
	],
	'permissions' => [
		'backups: manage settings' => [
			'title' => 'Manage settings'
		],
		'backups: manage backups'  => [
			'title' => 'Manage backups'
		]
	],
	
	'settings' => '@backups/settings',
	
	'resources' => [
		'spqr/backups:' => ''
	],
	
	'config' => [
		'overwrite_memorylimit'   => true,
		'memorylimit'             => 1024,
		'overwrite_executiontime' => true,
		'executiontime'           => 500,
		'process_files'           => 50,
		'auto_prune'              => false,
		'backup_number'           => 14,
		'exclusions'              => [],
		'backup_method'           => 'ftp',
		'ftp'                     => [
			'host'      => '',
			'port'      => 21,
			'ssl'       => true,
			'passive'   => true,
			'directory' => '/',
			'user'      => '',
			'password'  => ''
		],
		'local'                   => [ 'path' => '/' ],
		'database'                => [ 'usemysqldump' => false, 'mysqldump' => 'mysqldump' ],
		'items_per_page'          => 20,
	],
	
	'events' => [
		'boot' => function( $event, $app ) {
		},
		'site' => function( $event, $app ) {
		}
	]
];