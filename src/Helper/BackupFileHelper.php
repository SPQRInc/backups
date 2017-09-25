<?php

namespace Spqr\Backups\Helper;

use Pagekit\Application as App;
use PhpZip\ZipFile;
use Spqr\Backups\Model\Backup;
use Spqr\Backups\Model\Queue;

/**
 * Class BackupFileHelper
 * @package Spqr\Backups\Helper
 */
class BackupFileHelper
{
	/**
	 * @var
	 */
	protected $zippy;
	
	/**
	 * @var
	 */
	protected $zip;
	
	/**
	 * @var
	 */
	protected $compression;
	
	/**
	 * BackupFileHelper constructor.
	 *
	 */
	public function __construct()
	{
		$this->zip         = new ZipFile();
		$this->compression = ZipFile::METHOD_DEFLATED;
	}
	
	/**
	 * @param $compression
	 *
	 * @return bool
	 */
	public function setCompression( $compression )
	{
		switch ( $compression ) {
			case "deflated":
				$this->compression = ZipFile::METHOD_DEFLATED;
				break;
			case "stored":
				$this->compression = ZipFile::METHOD_STORED;
				break;
			case "bzip2":
				$this->compression = ZipFile::METHOD_BZIP2;
				break;
			default:
				$this->compression = ZipFile::METHOD_DEFLATED;
		}
		
		return true;
	}
	
	/**
	 * @param      $path
	 * @param null $exclusions
	 *
	 * @return array
	 */
	public function buildDirectoryTree( $path, $exclusions = null )
	{
		$iterator = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $path ) );
		$paths    = [];
		foreach ( $iterator as $file ) {
			if ( $file->isDir() )
				continue;
			
			if ( !empty( $exclusions ) ) {
				if ( is_array( $exclusions ) ) {
					foreach ( $exclusions as $exclusion ) {
						if ( strrpos( $file->getPathname(), $exclusion ) !== false )
							continue 2;
						
					}
				} else {
					if ( strrpos( $file->getPathname(), $exclusions ) !== false )
						continue;
				}
			}
			
			$paths[] = $file->getPathname();
		}
		
		return $paths;
	}
	
	/**
	 * @param \Spqr\Backups\Model\Backup $backup
	 * @param                            $destination
	 * @param null                       $removepath
	 *
	 * @return mixed
	 */
	public function processQueue( Backup $backup, $destination, $removepath = null )
	{
		$config = App::module( 'spqr/backups' )->config();
		
		$query = Queue::where( [ 'backup_id' => $backup->id, 'status' => Queue::STATUS_QUEUED ] );
		$queue = $query->limit( $config[ 'process_files' ] )->get();
		
		if ( App::file()->exists( $destination ) ) {
			$this->zip->openFile( $destination );
		}
		
		foreach ( $queue as $file ) {
			$archived = $this->archiveFile( $file->path, $removepath );
			if ( $archived ) {
				$file->status = Queue::STATUS_FINISHED;
				$file->save();
			}
		}
		
		$this->zip->saveAsFile( $destination );
		
		$this->zip->close();
		
		$count = $query->count();
		
		while ( !App::file()->exists( $destination ) ) {
			sleep( 10 );
		}
		
		return $count;
	}
	
	
	/**
	 * @param        $file
	 * @param null   $removepath
	 *
	 * @return bool
	 */
	private function archiveFile( $file, $removepath = null )
	{
		if ( $removepath != null ) {
			$path = str_replace( $removepath, '', $file );
		} else {
			$path = $file;
		}
		
		$folder = ltrim( dirname( $path ), '/' );
		
		if ( !empty( $folder ) && !isset( $this->zip[ $folder ] ) ) {
			$this->zip->addEmptyDir( $folder );
		}
		
		$this->zip->addFile( $file, ltrim( ( $path ), '/' ), $this->compression );
		
		return true;
	}
	
}