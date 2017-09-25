<?php

return [
	
	/*
	 * Installation hook
	 *
	 */
	'install'   => function( $app ) {
		$util = $app[ 'db' ]->getUtility();
		if ( $util->tableExists( '@backups_backup' ) === false ) {
			$util->createTable(
				'@backups_backup',
				function( $table ) {
					$table->addColumn(
						'id',
						'integer',
						[
							'unsigned'      => true,
							'length'        => 10,
							'autoincrement' => true
						]
					);
					$table->addColumn( 'status', 'smallint' );
					$table->addColumn( 'slug', 'string', [ 'length' => 255 ] );
					$table->addColumn( 'filename', 'string', [ 'length' => 255 ] );
					$table->addColumn( 'type', 'string', [ 'length' => 255 ] );
					$table->addColumn( 'data', 'json_array', [ 'notnull' => false ] );
					$table->addColumn( 'date', 'datetime', [ 'notnull' => false ] );
					$table->setPrimaryKey( [ 'id' ] );
					$table->addUniqueIndex( [ 'slug' ], '@BACKUPS_SLUG' );
				}
			);
		}
		if ( $util->tableExists( '@backups_queue' ) === false ) {
			$util->createTable(
				'@backups_queue',
				function( $table ) {
					$table->addColumn(
						'id',
						'integer',
						[
							'unsigned'      => true,
							'length'        => 10,
							'autoincrement' => true
						]
					);
					$table->addColumn( 'backup_id', 'integer', [ 'unsigned' => true, 'length' => 10, 'default' => 0 ] );
					$table->addColumn( 'status', 'smallint' );
					$table->addColumn( 'path', 'text' );
					$table->setPrimaryKey( [ 'id' ] );
				}
			);
		}
	},
	
	/*
	 * Enable hook
	 *
	 */
	'enable'    => function( $app ) {
	},
	
	/*
	 * Uninstall hook
	 *
	 */
	'uninstall' => function( $app ) {
		// remove the tables
		$util = $app[ 'db' ]->getUtility();
		if ( $util->tableExists( '@backups_backup' ) ) {
			$util->dropTable( '@backups_backup' );
		}
		if ( $util->tableExists( '@backups_queue' ) ) {
			$util->dropTable( '@backups_queue' );
		}
		
		// remove the config
		$app[ 'config' ]->remove( 'spqr/backups' );
	},
	
	/*
	 * Runs all updates that are newer than the current version.
	 *
	 */
	'updates'   => [],

];