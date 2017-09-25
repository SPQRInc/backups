<?php

namespace Spqr\Backups\Helper;

use Ifsnop\Mysqldump as IMysqldump;
use Pagekit\Application as App;
use Pagekit\Application\Exception;

/**
 * Class BackupDatabaseHelper
 * @package Spqr\Backups\Helper
 */
class BackupDatabaseHelper
{
	/**
	 * @param $destination
	 *
	 * @return bool
	 */
	public function createDump( $destination )
	{
		$host     = App::db()->getHost();
		$user     = App::db()->getUsername();
		$password = App::db()->getPassword();
		$database = App::db()->getDatabase();
		
		$config = App::module( 'spqr/backups' )->config();
		
		if ( $config[ 'database' ][ 'usemysqldump' ] ) {
			try {
				$binary = $config[ 'database' ][ 'mysqldump' ];
				$backup = exec(
					$binary . ' --host ' . $host . ' --user ' . $user . ' --password ' . $password . ' ' . $database . ' --result-file=' . $destination
				);
			} catch ( \Exception $e ) {
				throw new Exception( __( 'Unable to backup database.' ) );
			}
		} else {
			$dumpSettings = [
				'compress'                   => IMysqldump\Mysqldump::NONE,
				'no-data'                    => false,
				'add-drop-table'             => true,
				'single-transaction'         => true,
				'lock-tables'                => true,
				'add-locks'                  => true,
				'extended-insert'            => true,
				'disable-foreign-keys-check' => true,
				'skip-triggers'              => false,
				'add-drop-trigger'           => true,
				'databases'                  => true,
				'add-drop-database'          => true,
				'hex-blob'                   => true
			];
			
			try {
				$dump = new IMysqldump\Mysqldump(
					"mysql:host=$host;dbname=$database", "$user", "$password", $dumpSettings
				);
				$dump->start( $destination );
			} catch ( \Exception $e ) {
				throw new Exception( __( 'Unable to backup database.' ) );
			}
		}
		
		return true;
	}
}