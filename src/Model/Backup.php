<?php

namespace Spqr\Backups\Model;
use Pagekit\System\Model\DataModelTrait;


/**
 * @Entity(tableClass="@backups_backup")
 */
class Backup implements \JsonSerializable
{
	
	use BackupModelTrait, DataModelTrait;
	
	/* Backup in progress. */
	const STATUS_INPROGRESS = 0;
	
	/* Backup finished. */
	const STATUS_FINISHED = 1;
	
	/** @Column(type="integer") @Id */
	public $id;
	
	/** @Column(type="integer") */
	public $status;
	
	/** @Column(type="string") */
	public $slug;
	
	/** @Column(type="string") */
	public $filename;
	
	/** @Column(type="string") */
	public $type;
	
	/** @Column(type="datetime") */
	public $date;
	
	/**
	 * @HasMany(targetEntity="Queue", keyFrom="id", keyTo="backup_id")
	 */
	public $queue;
	
	/**
	 * @param $item
	 *
	 * @return mixed
	 */
	public static function getPrevious( $item )
	{
		return self::where(
			[ 'date > ?', 'date < ?', 'status = 1' ],
			[
				$item->date,
				new \DateTime
			]
		)->orderBy( 'date', 'ASC' )->first();
	}
	
	/**
	 * @param $item
	 *
	 * @return mixed
	 */
	public static function getNext( $item )
	{
		return self::where( [ 'date < ?', 'status = 1' ], [ $item->date ] )->orderBy( 'date', 'DESC' )->first();
	}
	
	/**
	 * @return mixed
	 */
	public function getStatusText()
	{
		$statuses = self::getStatuses();
		
		return isset( $statuses[ $this->status ] ) ? $statuses[ $this->status ] : __( 'Unknown' );
	}
	
	/**
	 * @return array
	 */
	public static function getStatuses()
	{
		return [
			self::STATUS_INPROGRESS => __( 'In Progress' ),
			self::STATUS_FINISHED   => __( 'Finished' )
		];
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function jsonSerialize()
	{
		return $this->toArray();
	}
}