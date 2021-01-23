<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\ORM\TableRegistry;
/**
 * Event Model
 *
 * @method \App\Model\Entity\Event get($primaryKey, $options = [])
 * @method \App\Model\Entity\Event newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Event[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Event|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Event saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Event patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Event[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Event findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class EventTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('event');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('event_name')
            ->maxLength('event_name', 255)
            ->requirePresence('event_name');

        $validator
            ->dateTime('start_date_time')
            ->requirePresence('start_date_time')
            ->add('start_date_time',[
                'validateStartDate' => [
                    'rule'=>'validateStartDate',
                    'provider'=>'table',
                    'message'=>'The start date time should be greater than current time'
                ]
            ]);

        $validator
            ->dateTime('end_date_time')
            ->allowEmptyDateTime('end_date_time')
            ->add('end_date_time',[
                'validateEndDate' => [
                    'rule'=>'validateEndDate',
                    'provider'=>'table',
                    'message'=>'The end date time should be greater than start date time'
                ]
            ]);

        $validator
            ->maxLength('frequency', 255)
            ->requirePresence('frequency')
            ->add('frequency',[
                'validateFrequency' => [
                    'rule'=>'validateFrequency',
                    'provider'=>'table',
                    'message'=>'Frequency should be either one out of these [once_off, weekly, monthly]'
                ]
            ]);

        $validator
            ->integer('duration')
            ->allowEmptyString('duration')
            ->range('duration', [1, 1440], 'Duration range should be between 1-1440 minutes');

        $validator
            ->allowEmptyString('invitees')
            ->add('invitees',[
                'validateInviteesFromUserTable' => [
                    'rule'=>'validateInviteesFromUserTable',
                    'provider'=>'table',
                    'message'=>'Please enter valid invitees id, it should match from user table'
                ]
            ]);

        $validator
            ->boolean('is_recurring')
            ->notEmptyString('is_recurring');

        return $validator;
    }

    /**
     * Function to validate the start datetime of event should be greater 
     * than current datetime
     */
    public function validateStartDate($value,$context) {
        $start_date = new \DateTime($value);
        $start_date->format('Y-m-d H:i');
        $current_date = new \DateTime();
        $current_date->format('Y-m-d H:i');
        if($current_date > $start_date) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Function to validate the end datetime of event should be greater 
     * than start datetime of event
     */
    public function validateEndDate($value,$context) {
        $end_date = new \DateTime($value);
        $end_date->format('Y-m-d H:i');
        $start_date = new \DateTime($context["data"]["start_date_time"]);
        $start_date->format('Y-m-d H:i');
        if($start_date > $end_date) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Function to validate type of frequency whether it's :
     * once_off/weekly/monthly
     */
    public function validateFrequency($value) {
        $frequency_types = array("once_off", "weekly", "monthly");
        if (in_array(trim(strtolower($value), " "), $frequency_types)) {
            $entity["frequency"] = trim(strtolower($value), " ");
            return true;
        } else {
            return false;
        }
    }

    /**
     * Function to validate the given Invitees id 
     * from the Users table
     */
    public function validateInviteesFromUserTable($value) {
        $invitee_ids = explode(',', $value);
        if (count($invitee_ids) > 0) {
            $user_ids = TableRegistry::get('Users')->find()->select(['id'])
            ->where(function ($exp, $q) use ($invitee_ids) {
                return $exp->in('id', $invitee_ids);
            });
            $user_ids_encode = json_encode($user_ids);
            if (count($invitee_ids) != count(json_decode($user_ids_encode))) {
                return false;
            }
        }
        return true;
    }

    /**
     * This function calls before save the value in table
     * It set the parameter like is_recurring
     * 
     */
    public function beforeSave($event, $entity) { 
        $entity['invitees'] = $entity['invitees'] . ",";
        if (empty($entity["duration"])) {
            $entity["duration"] = 60;
        }
        $entity["frequency"] = trim(strtolower($entity["frequency"]), " ");
        if ($entity["frequency"] === "once_off") {
            $entity["is_recurring"] = false;
        } else {
            $entity["is_recurring"] = true;
        }
    }

}
