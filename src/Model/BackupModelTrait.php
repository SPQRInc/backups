<?php

namespace Spqr\Backups\Model;

use Pagekit\Application as App;
use Pagekit\Database\ORM\ModelTrait;
use Spqr\Backups\Helper\BackupStoreHelper;

/**
 * Class BackupModelTrait
 * @package Spqr\Backups\Model
 */
trait BackupModelTrait
{
	use ModelTrait;
	
	/**
	 * @Saving
	 */
	public static function saving( $event, Backup $backup )
	{
		$i                = 2;
		$id               = $backup->id;
		$backup->filename = $backup->date->format( 'Y-m-d-H-i' ) . ".zip";
		$backup->slug     = App::filter( $backup->slug ? : $backup->filename, 'slugify' );
		
		while ( self::where( 'slug = ?', [ $backup->slug ] )->where(
			function( $query ) use ( $id ) {
				if ( $id ) {
					$query->where( 'id <> ?', [ $id ] );
				}
			}
		)->first() ) {
			$backup->slug = preg_replace( '/-\d+$/', '', $backup->slug ) . '-' . $i++;
		}
	}
	
	/**
	 * @Deleting
	 */
	public static function deleting( $event, Backup $backup )
	{
		self::getConnection()->delete( '@backups_queue', [ 'backup_id' => $backup->id ] );
		
		if ( $backup->status == self::STATUS_FINISHED ) {
			$store_helper = new BackupStoreHelper( $backup );
			$store_helper->remove();
		}
	}
	
}