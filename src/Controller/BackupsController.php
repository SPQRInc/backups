<?php

namespace Spqr\Backups\Controller;

use Pagekit\Application as App;

/**
 * @Access(admin=true)
 * @return string
 */
class BackupsController
{
	/**
	 * @Access("backups: manage settings")
	 */
	public function settingsAction()
	{
		return [
			'$view' => [
				'title' => __( 'Backups Settings' ),
				'name'  => 'spqr/backups:views/admin/settings.php'
			],
			'$data' => [
				'config'   => App::module( 'spqr/backups' )->config(),
				'database' => App::info()->get()[ 'dbdriver' ]
			]
		];
	}
	
	/**
	 * @Request({"config": "array"}, csrf=true)
	 * @param array $config
	 *
	 * @return array
	 */
	public function saveAction( $config = [] )
	{
		App::config()->set( 'spqr/backups', $config );
		
		return [ 'message' => 'success' ];
	}
	
}