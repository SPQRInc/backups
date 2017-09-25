<?php

namespace Spqr\Backups\Model;

/**
 * @Entity(tableClass="@backups_queue")
 */
class Queue implements \JsonSerializable
{
	
	use QueueModelTrait;
	
	/* Queue queued. */
	const STATUS_QUEUED = 0;
	
	/* Queue finished. */
	const STATUS_FINISHED = 1;
	
	/** @Column(type="integer") @Id */
	public $id;
	
	/** @Column(type="integer") */
	public $backup_id;
	
	/** @Column(type="integer") */
	public $status;
	
	/** @Column(type="string") */
	public $path;
	
	/** @BelongsTo(targetEntity="Backup", keyFrom="backup_id") */
	public $backup;
	
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
			self::STATUS_QUEUED => __( 'Queued' ),
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