<?php

namespace Spqr\Backups\Controller;

use Pagekit\Application as App;
use Spqr\Backups\Model\Backup;

/**
 * @Access(admin=true)
 * @return string
 */
class BackupController
{
	/**
	 * @Access("backups: manage backups")
	 * @Request({"filter": "array", "page":"int"})
	 * @param null $filter
	 * @param int  $page
	 *
	 * @return array
	 */
	public function backupAction( $filter = null, $page = 0 )
	{
		return [
			'$view' => [ 'title' => 'Backups', 'name' => 'spqr/backups:views/admin/backup-index.php' ],
			'$data' => [
				'statuses' => Backup::getStatuses(),
				'config'   => [
					'filter' => (object) $filter,
					'page'   => $page
				]
			]
		];
	}
	
	/**
	 * @Route("/backup/edit", name="backup/edit")
	 * @Access("backups: manage backups")
	 * @Request({"id": "int"})
	 * @param int $id
	 *
	 * @return array
	 */
	public function editAction( $id = 0 )
	{
		try {
			$module = App::module( 'spqr/backups' );
			
			if ( !$backup = Backup::where( compact( 'id' ) )->first() ) {
				App::abort( 404, __( 'Invalid backup id.' ) );
			}
			
			return [
				'$view' => [
					'title' => $id ? __( 'Edit Backup' ) : __( 'Add Backup' ),
					'name'  => 'spqr/backups:views/admin/backup-edit.php'
				],
				'$data' => [
					'backup'   => $backup,
					'statuses' => Backup::getStatuses()
				]
			];
		} catch ( \Exception $e ) {
			App::message()->error( $e->getMessage() );
			
			return App::redirect( '@backups/backup' );
		}
	}
	
}