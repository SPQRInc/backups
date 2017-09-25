<?php

namespace Spqr\Backups\Helper;

use Pagekit\Application as App;
use Pagekit\Application\Exception;
use Spqr\Backups\Model\Backup;
use Spqr\Backups\Model\Queue;

/**
 * Class BackupHelper
 * @package Spqr\Backups\Helper
 */
class BackupHelper
{
	/**
	 * @var
	 */
	protected $config;
	
	/**
	 * @var
	 */
	protected $backup;
	
	/**
	 * @var
	 */
	protected $path;
	
	/**
	 * @var
	 */
	protected $temp_path;
	
	/**
	 * BackupHelper constructor.
	 *
	 * @param \Spqr\Backups\Model\Backup $backup
	 */
	public function __construct( Backup $backup )
	{
		
		$this->config = App::module( 'spqr/backups' )->config();
		$this->backup = $backup;
		
		if ( $this->config[ 'overwrite_memorylimit' ] ) {
			$memory = trim( ini_get( 'memory_limit' ) );
			if ( $memory != -1 && $this->memoryInBytes( $memory ) < $this->config[ 'memorylimit' ] ) {
				@ini_set( 'memory_limit', $this->config[ 'memorylimit' ] . "M" );
			}
		}
		
		if ( $this->config[ 'overwrite_executiontime' ] ) {
			$executiontime = ini_get( 'max_execution_time' );
			
			if ( $executiontime != -1 && $executiontime < $this->config[ 'executiontime' ] ) {
				@ini_set( 'max_execution_time', $this->config[ 'executiontime' ] );
			}
		}
		
		$hash = substr(
			sha1(
				App::module( 'system' )->config( 'secret' ) . rand( 0, 9999 ) . date_format(
					$this->backup->date,
					'd/m/Y H:i:s'
				)
			),
			0,
			20
		);
		
		$date = $backup->date->format( 'Y-m-d-H-i' );
		
		$this->path      = App::get( 'path' );
		$this->temp_path = App::get( 'path.temp' );
		$extension_path  = 'spqr-backups';
		
		if ( empty( $this->backup->get( 'backup.hash_path' ) ) ) {
			$hash_path = $this->temp_path . DIRECTORY_SEPARATOR . $extension_path . DIRECTORY_SEPARATOR . $hash;
			$this->backup->set( 'backup.hash_path', $hash_path );
		}
		if ( empty( $this->backup->get( 'backup.backup_path' ) ) ) {
			$backup_path = $this->backup->get( 'backup.hash_path' ) . DIRECTORY_SEPARATOR . $date;
			$this->backup->set( 'backup.backup_path', $backup_path );
		}
		if ( empty( $this->backup->get( 'backup.files_path' ) ) ) {
			$files_path = $this->backup->get( 'backup.backup_path' ) . DIRECTORY_SEPARATOR . "files";
			$this->backup->set( 'backup.files_path', $files_path );
		}
		if ( empty( $this->backup->get( 'backup.database_path' ) ) ) {
			$database_path = $this->backup->get( 'backup.backup_path' ) . DIRECTORY_SEPARATOR . "database";
			$this->backup->set( 'backup.database_path', $database_path );
		}
		if ( empty( $this->backup->get( 'backup.installer_path' ) ) ) {
			$installer_path = $this->backup->get( 'backup.backup_path' ) . DIRECTORY_SEPARATOR . "installer";
			$this->backup->set( 'backup.installer_path', $installer_path );
		}
		if ( empty( $this->backup->get( 'backup.bundle_path' ) ) ) {
			$bundle_path = $this->backup->get( 'backup.backup_path' ) . DIRECTORY_SEPARATOR . "bundle";
			$this->backup->set( 'backup.bundle_path', $bundle_path );
		}
		
		if ( empty( $this->backup->get( 'backup.files_target' ) ) ) {
			$files_target = $this->backup->get( 'backup.files_path' ) . DIRECTORY_SEPARATOR . "files.zip";
			$this->backup->set( 'backup.files_target', $files_target );
		}
		if ( empty( $this->backup->get( 'backup.database_target' ) ) ) {
			$database_target = $this->backup->get( 'backup.database_path' ) . DIRECTORY_SEPARATOR . "database.sql";
			$this->backup->set( 'backup.database_target', $database_target );
		}
		if ( empty( $this->backup->get( 'backup.bundle_target' ) ) ) {
			$bundle_target = $this->backup->get( 'backup.bundle_path' ) . DIRECTORY_SEPARATOR . "$date.zip";
			$this->backup->set( 'backup.bundle_target', $bundle_target );
		}
		
		$this->backup->save();
		
	}
	
	/**
	 * @param $value
	 *
	 * @return int
	 */
	protected function memoryInBytes( $value )
	{
		$unit  = strtolower( substr( $value, -1, 1 ) );
		$value = (int) $value;
		switch ( $unit ) {
			case 'g':
				$value *= 1024;
			// no break (cumulative multiplier)
			case 'm':
				$value *= 1024;
			// no break (cumulative multiplier)
			case 'k':
				$value *= 1024;
		}
		
		return $value;
	}
	
	/**
	 * @return array
	 */
	public function prepareBackup()
	{
		$exclusions   = $this->config[ 'exclusions' ];
		$exclusions[] = $this->temp_path;
		
		if ( $this->config[ 'backup_method' ] == 'local' ) {
			$exclusions[] = $this->config[ 'local' ][ 'path' ];
		}
		
		$file_helper = new BackupFileHelper;
		$files_tree  = $file_helper->buildDirectoryTree( App::get( 'path' ), $exclusions );
		
		return $files_tree;
	}
	
	/**
	 * @return array|int|mixed
	 */
	public function prepareQueue()
	{
		
		$files = $this->backup->get( 'backup.files' );
		
		if ( is_array( $files ) ) {
			$i = 0;
			
			foreach ( $files as $file ) {
				if ( $i < 400 ) {
					$build[] = $file;
					$i++;
				} else continue;
			}
			
			$files = array_slice( $files, 400 );
			
			$this->backup->set( 'backup.files', $files );
			$this->backup->save();
			
			$this->buildQueue( $build );
			
			return $files;
		} else return 0;
	}
	
	
	/**
	 * @param array $tree
	 *
	 * @return bool
	 */
	public function buildQueue( array $tree )
	{
		if ( !empty( $tree ) ) {
			foreach ( $tree as $file ) {
				if ( $file ) {
					$queue = Queue::create(
						[
							'backup_id' => $this->backup->id,
							'status'    => Queue::STATUS_QUEUED,
							'path'      => $file
						]
					);
					$queue->save();
					
				}
			}
		}
		
		return true;
	}
	
	/**
	 * @param $type
	 *
	 * @return bool
	 */
	public function backup( $type )
	{
		if ( $type === 'files' ) {
			return $this->backupFiles();
		}
		if ( $type === 'database' ) {
			return $this->backupDatabase();
		}
		if ( $type === 'bundle' ) {
			return $this->backupBundle();
		}
		
		return true;
	}
	
	/**
	 * @return bool
	 */
	private function backupFiles()
	{
		try {
			if ( !App::file()->exists( $this->backup->get( 'backup.files_path' ) ) ) {
				App::file()->makeDir( $this->backup->get( 'backup.files_path' ) );
			}
		} catch ( \Exception $e ) {
			throw new Exception( __( 'Unable to create temp file directory.' ) );
		}
		
		try {
			$file_helper = new BackupFileHelper;
			$queue       =
				$file_helper->processQueue(
					$this->backup,
					$this->backup->get( 'backup.files_target' ),
					App::get( 'path' )
				);
		} catch ( \Exception $e ) {
			throw new Exception( __( 'Unable to backup files.' ) );
		}
		
		return $queue;
	}
	
	/**
	 * @return bool
	 */
	private function backupDatabase()
	{
		if ( App::info()->get()[ 'dbdriver' ] == 'sqlite' ) {
			return true;
		}
		
		try {
			if ( !App::file()->exists( $this->backup->get( 'backup.database_path' ) ) ) {
				App::file()->makeDir( $this->backup->get( 'backup.database_path' ) );
				
			}
		} catch ( \Exception $e ) {
			throw new Exception( __( 'Unable to create temp database directory.' ) );
		}
		try {
			$database_helper = new BackupDatabaseHelper;
			$database_helper->createDump( $this->backup->get( 'backup.database_target' ) );
			
		} catch ( \Exception $e ) {
			throw new Exception( __( 'Unable to create database dump.' ) );
		}
		
		return true;
	}
	
	/**
	 * @return bool
	 */
	private function backupBundle()
	{
		try {
			if ( !App::file()->exists( $this->backup->get( 'backup.bundle_path' ) ) ) {
				App::file()->makeDir( $this->backup->get( 'backup.bundle_path' ) );
			}
		} catch ( \Exception $e ) {
			throw new Exception( __( 'Unable to create temp file directory.' ) );
		}
		try {
			$file_helper = new BackupFileHelper;
			$file_helper->setCompression('stored');
			$queue       = $file_helper->processQueue(
				$this->backup,
				$this->backup->get( 'backup.bundle_target' ),
				$this->backup->get( 'backup.hash_path' )
			);
		} catch ( \Exception $e ) {
			throw new Exception( __( 'Unable to create bundle.' ) );
		}
		
		return $queue;
	}
	
	/**
	 * @return bool
	 */
	public function prepareBundle()
	{
		$file_helper = new BackupFileHelper;
		
		$files_tree  = $file_helper->buildDirectoryTree( $this->backup->get( 'backup.backup_path' ) );
		$files_queue = $this->buildQueue( $files_tree );
		
		return true;
	}
	
	/**
	 * @return bool
	 */
	public function store()
	{
		$store_helper = new BackupStoreHelper( $this->backup );
		$store_helper->store();
		$this->backup->set('backup.linked', true);
		$this->backup->save();
		return true;
	}
	
	/**
	 * @return bool
	 */
	public function purge()
	{
		try {
			if ( App::file()->exists( $this->backup->get( 'backup.hash_path' ) ) ) {
				App::file()->delete( $this->backup->get( 'backup.hash_path' ) );
			}
		} catch ( \Exception $e ) {
			throw new Exception( __( 'Unable to delete temp directory.' ) );
		}
		
		return true;
	}
	
	/**
	 * @return bool
	 */
	public function prune()
	{
		$query        = Backup::where( [ 'status = ?' ], [ Backup::STATUS_FINISHED ] );
		$backup_count = $query->orderBy( 'date', 'DESC' )->count() + 1;
		
		if ( $this->config[ 'auto_prune' ] && ( $this->config[ 'backup_number' ] < $backup_count ) ) {
			
			$backups = $query->get();
			$i       = 1;
			
			foreach ( $backups as $backup ) {
				$i++;
				if ( $i > $this->config[ 'backup_number' ] ) {
					$backup->delete();
				}
			}
		}
		
		return true;
	}
	
}