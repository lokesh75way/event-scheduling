<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Event Entity
 *
 * @property int $id
 * @property string|null $event_name
 * @property \Cake\I18n\FrozenTime|null $start_date_time
 * @property \Cake\I18n\FrozenTime|null $end_date_time
 * @property string|null $frequency
 * @property int|null $duration
 * @property string|null $invitees
 * @property bool|null $is_recurring
 * @property \Cake\I18n\FrozenTime|null $created
 * @property \Cake\I18n\FrozenTime|null $modified
 */
class Event extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        'event_name' => true,
        'start_date_time' => true,
        'end_date_time' => true,
        'frequency' => true,
        'duration' => true,
        'invitees' => true,
        'is_recurring' => true,
        'created' => true,
        'modified' => true,
    ];
}
