<?php

namespace Spqr\Backups\Helper;

use FtpClient\FtpClient;
use Pagekit\Application as App;
use Pagekit\Application\Exception;
use Spqr\Backups\Model\Backup;

/**
 * Class BackupStoreHelper
 * @package Spqr\Backups\Helper
 */
class BackupStoreHelper
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
	 * BackupStoreHelper constructor.
	 *
	 * @param \Spqr\Backups\Model\Backup $backup
	 */
	public function __construct( Backup $backup )
	{
		$this->config = App::module( 'spqr/backups' )->config();
		$this->backup = $backup;
	}
	
	/**
	 * @return bool
	 */
	public function store()
	{
		if ( $this->config[ 'backup_method' ] == 'ftp' )
			$this->ftpUpload();
		if ( $this->config[ 'backup_method' ] == 'local' )
			$this->localUpload();
		
		return true;
	}
	
	/**
	 * @return bool
	 */
	private function ftpUpload()
	{
		try {
			if ( empty( $this->config[ 'ftp' ][ 'directory' ] ) )
				$this->config[ 'ftp' ][ 'directory' ] = '/';
			
			$ftp_client = $this->getFtpClient();
			$ftp_client->putAll( $this->backup->get( 'backup.bundle_path' ), $this->config[ 'ftp' ][ 'directory' ] );
			
			$this->backup->set( 'ftp.host', $this->config[ 'ftp' ][ 'host' ] );
			$this->backup->set( 'ftp.ssl', $this->config[ 'ftp' ][ 'ssl' ] );
			$this->backup->set( 'ftp.port', $this->config[ 'ftp' ][ 'port' ] );
			$this->backup->set( 'ftp.passive', $this->config[ 'ftp' ][ 'passive' ] );
			$this->backup->set( 'ftp.user', $this->config[ 'ftp' ][ 'user' ] );
			$this->backup->set( 'ftp.password', $this->config[ 'ftp' ][ 'password' ] );
			$this->backup->set( 'ftp.directory', $this->config[ 'ftp' ][ 'directory' ] );
			$this->backup->save();
			
		} catch ( \Exception $e ) {
			throw new Exception( __( 'Unable to upload file to FTP.' ) );
		}
		
		return true;
	}
	
	/**
	 * @param null $host
	 * @param null $ssl
	 * @param null $port
	 * @param null $passive
	 * @param null $user
	 * @param null $password
	 *
	 * @return \FtpClient\FtpClient
	 */
	private function getFtpClient(
		$host = null,
		$ssl = null,
		$port = null,
		$passive = null,
		$user = null,
		$password = null
	)
	{
		if ( $host === null ) {
			$host = $this->config[ 'ftp' ][ 'host' ];
		}
		if ( $ssl === null ) {
			$ssl = $this->config[ 'ftp' ][ 'ssl' ];
		}
		if ( $port === null ) {
			$port = $this->config[ 'ftp' ][ 'port' ];
		}
		if ( $passive === null ) {
			$passive = $this->config[ 'ftp' ][ 'passive' ];
		}
		if ( $user === null ) {
			$user = $this->config[ 'ftp' ][ 'user' ];
		}
		if ( $password === null ) {
			$password = $this->config[ 'ftp' ][ 'password' ];
		}
		
		try {
			$ftp_client = new FtpClient();
			$ftp_client->connect(
				$host,
				$ssl,
				$port
			);
			
			if ( $passive ) {
				$ftp_client->pasv( true );
			}
			
			$ftp_client->login( $user, $password );
		} catch ( \Exception $e ) {
			throw new Exception( __( 'Unable to connect to FTP.' ) );
		}
		
		return $ftp_client;
	}
	
	/**
	 * @return bool
	 */
	private function localUpload()
	{
		
		try {
			if ( empty( $this->config[ 'local' ][ 'path' ] ) )
				$this->config[ 'local' ][ 'path' ] = '/';
			
			App::file()->copy(
				$this->backup->get( 'backup.bundle_target' ),
				$this->config[ 'local' ][ 'path' ] . DIRECTORY_SEPARATOR . $this->backup->filename
			);
			
			$this->backup->set( 'backup.file_path', $this->config[ 'local' ][ 'path' ] );
			
			$this->backup->save();
			
		} catch ( \Exception $e ) {
			throw new Exception( __( 'Unable to copy file to local directory.' ) );
		}
		
		return true;
	}
	
	/**
	 * @return bool
	 */
	public function remove()
	{
		if ( $this->backup->get( 'backup.linked' ) == true ) {
			if ( $this->backup->type == 'ftp' ) {
				$this->ftpRemove();
			}
			
			if ( $this->backup->type == 'local' ) {
				$this->localRemove();
			}
			
		}
		
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
	private function ftpRemove()
	{
		try {
			$ftp_client = $this->getFtpClient(
				$this->backup->get( 'ftp.host' ),
				$this->backup->get( 'ftp.ssl' ),
				$this->backup->get( 'ftp.port' ),
				$this->backup->get( 'ftp.passive' ),
				$this->backup->get( 'ftp.user' ),
				$this->backup->get( 'ftp.password' )
			);
			
			if ( empty( $this->backup->get( 'ftp.directory' ) ) || $this->backup->get( 'ftp.directory' ) == "/" ) {
				$delete_file = $this->backup->filename;
			} else {
				$delete_file = $this->backup->get( 'ftp.directory' ) . "/" . $this->backup->filename;
			}
			$ftp_client->delete( $delete_file );
			
		} catch ( \Exception $e ) {
			throw new Exception( __( 'Unable to remove file from FTP.' ) );
		}
		
		return true;
	}
	
	/**
	 * @return bool
	 */
	private function localRemove()
	{
		if ( $this->backup->get( 'backup.linked' ) == true ) {
			try {
				App::file()->delete(
					$this->backup->get( 'backup.file_path' ) . DIRECTORY_SEPARATOR . $this->backup->filename
				);
				
			} catch ( \Exception $e ) {
				throw new Exception( __( 'Unable to remove file from local directory.' ) );
			}
		}
		
		return true;
	}
}