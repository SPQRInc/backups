<?php

namespace Spqr\Backups\Controller;

use Pagekit\Application as App;
use Spqr\Backups\Helper\BackupHelper;
use Spqr\Backups\Model\Backup;

/**
 * @Access("backups: manage backups")
 * @Route("backup", name="backup")
 */
class BackupApiController
{
	/**
	 * @param array $filter
	 * @param int   $page
	 * @param int   $limit
	 * @Route("/", methods="GET")
	 * @Request({"filter": "array", "page":"int", "limit":"int"})
	 *
	 * @return mixed
	 */
	public function indexAction( $filter = [], $page = 0, $limit = 0 )
	{
		$query  = Backup::query();
		$filter = array_merge( array_fill_keys( [ 'status', 'search', 'limit', 'order' ], '' ), $filter );
		extract( $filter, EXTR_SKIP );
		if ( is_numeric( $status ) ) {
			$query->where( [ 'status' => (int) $status ] );
		}
		if ( $search ) {
			$query->where(
				function( $query ) use ( $search ) {
					$query->orWhere(
						[
							'filename LIKE :search'
						],
						[ 'search' => "%{$search}%" ]
					);
				}
			);
		}
		if ( preg_match( '/^(filename|date)\s(asc|desc)$/i', $order, $match ) ) {
			$order = $match;
		} else {
			$order = [ 1 => 'date', 2 => 'desc' ];
		}
		$default = App::module( 'spqr/backups' )->config( 'items_per_page' );
		$limit   = min( max( 0, $limit ), $default ) ? : $default;
		$count   = $query->count();
		$pages   = ceil( $count / $limit );
		$page    = max( 0, min( $pages - 1, $page ) );
		$backups = array_values(
			$query->offset( $page * $limit )->limit( $limit )->orderBy( $order[ 1 ], $order[ 2 ] )->get()
		);
		
		return compact( 'backups', 'pages', 'count' );
	}
	
	/**
	 * @Route("/{id}", methods="GET", requirements={"id"="\d+"})
	 * @param $id
	 *
	 * @return static
	 */
	public function getAction( $id )
	{
		if ( !$backup = Backup::where( compact( 'id' ) )->first() ) {
			App::abort( 404, 'Backup not found.' );
		}
		
		return $backup;
	}
	
	/**
	 * @Route("/bulk", methods="POST")
	 * @Request({"backups": "array"}, csrf=true)
	 * @param array $backups
	 *
	 * @return array
	 */
	public function bulkSaveAction( $backups = [] )
	{
		foreach ( $backups as $data ) {
			$this->saveAction( $data, isset( $data[ 'id' ] ) ? $data[ 'id' ] : 0 );
		}
		
		return [ 'message' => 'success' ];
	}
	
	/**
	 * @Route("/", methods="POST")
	 * @Route("/{id}", methods="POST", requirements={"id"="\d+"})
	 * @Request({"backup": "array", "id": "int"}, csrf=true)
	 */
	public function saveAction( $data, $id = 0 )
	{
		if ( !$id || !$backup = Backup::find( $id ) ) {
			if ( $id ) {
				App::abort( 404, __( 'Backup not found.' ) );
			}
			$backup = Backup::create();
		}
		if ( !$data[ 'slug' ] = App::filter( $data[ 'slug' ] ? : $data[ 'filename' ], 'slugify' ) ) {
			App::abort( 400, __( 'Invalid slug.' ) );
		}
		
		$backup->save( $data );
		
		return [ 'message' => 'success', 'backup' => $backup ];
	}
	
	/**
	 * @Route("/bulk", methods="DELETE")
	 * @Request({"ids": "array"}, csrf=true)
	 * @param array $ids
	 *
	 * @return array
	 */
	public function bulkDeleteAction( $ids = [] )
	{
		foreach ( array_filter( $ids ) as $id ) {
			$this->deleteAction( $id );
		}
		
		return [ 'message' => 'success' ];
	}
	
	/**
	 * @Route("/{id}", methods="DELETE", requirements={"id"="\d+"})
	 * @Request({"id": "int"}, csrf=true)
	 * @param $id
	 *
	 * @return array
	 */
	public function deleteAction( $id )
	{
		if ( $backup = Backup::find( $id ) ) {
			if ( !App::user()->hasAccess( 'backups: manage backups' ) ) {
				App::abort( 400, __( 'Access denied.' ) );
			}
			
			$backup->delete();
		}
		
		return [ 'message' => 'success' ];
	}
	
	
	/**
	 * @Route("/preparebackup", methods="POST")
	 * @Request(csrf=true)
	 */
	public function preparebackupAction()
	{
		$backup = Backup::create(
			[
				'status' => Backup::STATUS_INPROGRESS,
				'date'   => new \DateTime(),
				'type'   => $config = App::module( 'spqr/backups' )->config( 'backup_method' ),
			]
		);
		
		$backup->save();
		
		$backup_helper = new BackupHelper( $backup );
		$files         = $backup_helper->prepareBackup();
		$backup->set('backup.files', $files);
		$backup->save();
		
		return [ 'message' => 'success', 'backup' => $backup ];
	}
	
	/**
	 * @Route("/buildqueue", methods="POST")
	 * @Request({"id": "int"}, csrf=true)
	 * @param int $id
	 *
	 * @return array
	 *
	 */
	public function buildqueueAction( $id )
	{
		$backup        = Backup::find( $id );
		$backup_helper = new BackupHelper( $backup );
		$files_left         = $backup_helper->prepareQueue();
		return [ 'message' => 'success', 'files' => count($files_left) ];
	}
	
	
	/**
	 * @Route("/preparebundle", methods="POST")
	 * @Request({"id": "int"}, csrf=true)
	 * @param int $id
	 *
	 * @return array
	 *
	 */
	public function preparebundleAction( $id )
	{
		$backup        = Backup::find( $id );
		$backup_helper = new BackupHelper( $backup );
		$files         = $backup_helper->prepareBundle();
		
		return [ 'message' => 'success' ];
	}
	
	/**
	 * @Route("/archivefiles", methods="POST")
	 * @Request({"id": "int"}, csrf=true)
	 * @param int $id
	 *
	 * @return array
	 *
	 */
	public function archivefilesAction( $id )
	{
		$backup        = Backup::find( $id );
		$backup_helper = new BackupHelper( $backup );
		$backup_files  = $backup_helper->backup( 'files' );
		
		return [ 'message' => 'success', 'files' => $backup_files ];
	}
	
	/**
	 * @Route("/archivedatabase", methods="POST")
	 * @Request({"id": "int"}, csrf=true)
	 * @param int $id
	 *
	 * @return array
	 *
	 */
	public function archivedatabaseAction( $id )
	{
		$backup        = Backup::find( $id );
		$backup_helper = new BackupHelper( $backup );
		$backup_helper->backup( 'database' );
		
		return [ 'message' => 'success' ];
	}
	
	/**
	 * @Route("/archivebundle", methods="POST")
	 * @Request({"id": "int"}, csrf=true)
	 * @param int $id
	 *
	 * @return array
	 *
	 */
	public function archivebundleAction( $id )
	{
		$backup        = Backup::find( $id );
		$backup_helper = new BackupHelper( $backup );
		$backup_files = $backup_helper->backup( 'bundle' );
		
		return [ 'message' => 'success', 'files' => $backup_files ];
	}
	
	/**
	 * @Route("/storebackup", methods="POST")
	 * @Request({"id": "int"}, csrf=true)
	 * @param int $id
	 *
	 * @return array
	 *
	 */
	public function storebackupAction( $id )
	{
		$backup        = Backup::find( $id );
		$backup_helper = new BackupHelper( $backup );
		$backup_helper->store();
		
		return [ 'message' => 'success' ];
	}
	
	/**
	 * @Route("/purgebackup", methods="POST")
	 * @Request({"id": "int"}, csrf=true)
	 * @param int $id
	 *
	 * @return array
	 *
	 */
	public function purgebackupAction( $id )
	{
		$backup        = Backup::find( $id );
		$backup_helper = new BackupHelper( $backup );
		$backup_helper->purge();
		
		return [ 'message' => 'success' ];
	}
	
	/**
	 * @Route("/prunebackup", methods="POST")
	 * @Request({"id": "int"}, csrf=true)
	 * @param int $id
	 *
	 * @return array
	 *
	 */
	public function prunebackupAction( $id )
	{
		$backup        = Backup::find( $id );
		$backup_helper = new BackupHelper( $backup );
		$backup_helper->prune();
		
		return [ 'message' => 'success' ];
	}
	
	/**
	 * @Route("/finalizebackup", methods="POST")
	 * @Request({"id": "int"}, csrf=true)
	 * @param int $id
	 *
	 * @return array
	 *
	 */
	public function finalizebackupAction( $id )
	{
		$backup         = Backup::find( $id );
		$backup->status = Backup::STATUS_FINISHED;
		$backup->save();
		
		return [ 'message' => 'success' ];
	}
	
}