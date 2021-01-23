<?php
namespace App\Controller;

use Cake\ORM\TableRegistry;
use App\Controller\AppController;
use Cake\Validation\Validator;
use Cake\I18n\DateTime;
use Cake\I18n\Date;

/**
 * Event Controller
 *
 * @property \App\Model\Table\EventTable $Event
 *
 * @method \App\Model\Entity\Event[]|\Cake\DataSource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class EventController extends AppController
{
    public function initialize() {
        parent::initialize();
        $this->loadComponent('RequestHandler');
    }
    
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null
     */
    public function index()
    {
        // $event = $this->paginate($this->Event);
        $this->autoRender = false;
        $event = $this->Event->find('all');
        $response = array("status" => "success", "message" => "all Event get Successfully", "data" => $event);
        $this->response->type('json');
        $this->response->body(json_encode( $response ));
        return $this->response;
    }

    /**
     * View method
     *
     * @param string|null $id Event id.
     * @return \Cake\Http\Response|null
     * @throws \Cake\DataSource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {   
        $this->autoRender = false;
        if ($this->request->is('get')) {
            $event = $this->Event->get($id, [
                'contain' => [],
            ]);
            $response = array("status" => "success", "data" => $event);
        }
        $this->response->type('json');
        $this->response->body(json_encode( $response ));
        return $this->response;
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $this->autoRender = false;
        $event = $this->Event->newEntity();
        if ($this->request->is('post')) {
            $this->Event->getValidator();
            try {
                $post_params = $this->request->getData();
                $is_valid_invitees = false;
                
                if(isset($post_params['invitees'])) {
                    if (!empty($post_params['invitees']) && !is_array($post_params['invitees'])) {
                        $response = array("status" => "failed", "message" => "Invalid invitees format, it should be in array format like [1,2,3]");
                    } elseif(!empty($post_params['invitees']) && is_array($post_params['invitees'])){
                        $is_valid_invitees = true;
                        $post_params['invitees'] = implode(',', $post_params['invitees']);
                    } else {
                        $is_valid_invitees = true;
                        $post_params['invitees'] = "";
                    }
                } else{
                    $is_valid_invitees = true;
                    $post_params['invitees'] = "";
                }
                if ($is_valid_invitees) {
                    $event = $this->Event->patchEntity($event, $post_params);
                    if (empty($event->getErrors())) {
                        if ($this->Event->save($event)) {
                            $event['invitees'] =  explode(',',$event['invitees']);
                            array_pop($event['invitees']);
                            $event['invitees'] = array_map('intval', $event['invitees']);
                            $response = array("status" => "success", "message" => "Event created successfully", "data" => $event);
                        } else {
                            $response = array("status" => "failed", "message" => "Event not created", "error" => $event->getErrors());
                        }               
                    } else {
                        $response = array("status" => "failed", "error" => $event->getErrors());
                    }
                }
            } catch (\Exception $e) {
                $response = array("status" => "failed", "message" => "Invalid data format", "error" => $e);
            }
        }
        
        $this->response->type('json');
        $this->response->body(json_encode( $response ));
        return $this->response;
    }

    /**
     * Get method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function get() {
        $this->autoRender = false;
        if ($this->request->is('get')) {
            try {
                $response = array();
                $output_events = array();
                $final_event_instances = array();
                $get_params = $this->request->getQueryParams();
                $validation = $this->getDataValidate($get_params);
                if (!empty($validation)) {
                    $response = array("status" => "failed", "message" => $validation);
                } else {
                    $events_with_end_date = array();
                    $events_without_end_date = array();
                    $invitees_array = array();
                    if(!empty($get_params['invitees'])){
                        $invitees_array =  explode(",", $get_params['invitees']);
                    }

                    // Fetch all events based on from and to date
                    // and whose end dates are PRESENT
                    $events_with_end_date = $this->Event->find("all",
                        [
                            'conditions' => [
                                'Event.end_date_time !=' => "",
                                'Event.end_date_time >=' =>  $get_params['from'],
                                'Event.start_date_time <=' => $get_params['to']
                            ]
                        ]
                    );

                    // Fetch all events based on from and to date
                    // and whose end dates are NOT PRESENT
                    $events_without_end_date = $this->Event->find("all", [
                            'conditions' => [
                                'Event.end_date_time is' => null,
                                'Event.start_date_time <=' => $get_params['to']
                            ]
                        ]
                    );

                    // Merge results of @events_with_end_date & @events_without_end_date
                    $combined_array = array_merge(json_decode(json_encode($events_with_end_date), true),json_decode(json_encode($events_without_end_date), true));
                    
                    // Fetch all events based on given Invitee's id 
                    // and find the unique events
                    if (count($invitees_array) > 0) {
                        $result_invitees = array();
                        $unique_invitees = array();
                        $key_array = array();

                        // To fetch all events for all invitees
                        foreach ($invitees_array as $invitee) {
                            $to_match = '%' . $invitee . ',%';
                            $invitees_temp_array = $this->Event->find("all",
                                [
                                    'conditions' => [
                                        'Event.invitees LIKE' => $to_match
                                    ]
                                ]
                            );
                            $result_invitees =  array_merge($result_invitees,json_decode(json_encode($invitees_temp_array), true));                        
                        }
                        
                        // To find unique events for all invitees
                        foreach($result_invitees as $val) {
                            if (!in_array($val["id"], $key_array)) {
                                $key_array[] = $val["id"];
                                $unique_invitees[] = $val;
                            }
                        }

                        // To find common events for all invitees based on @combine_array
                        foreach($combined_array as $val) {
                            if (in_array($val["id"], $key_array)) {
                                $output_events[] = $val;
                            }
                        }
                    } else {
                        $output_events = $combined_array;
                    }

                    // Make final event instances array
                    foreach($output_events as $event_schedule) {
                        $event_schedule["invitees"] = explode(",", $event_schedule["invitees"]);
                        array_pop($event_schedule['invitees']);
                        $event_schedule['invitees'] = array_map('intval', $event_schedule['invitees']);
                        
                        if ($event_schedule["is_recurring"] == true) {
                            $from_date = date('Y-m-d H:i', strtotime($get_params['from']));
                            $to_date = date('Y-m-d H:i', strtotime($get_params['to']));
                            $start_date = date('Y-m-d H:i', strtotime($event_schedule['start_date_time']));
                            if (empty($event_schedule['end_date_time'])) {
                                $end_date = date('Y-m-d H:i', strtotime("+12 months", strtotime($event_schedule['start_date_time'])));
                            } else{
                                $end_date = date('Y-m-d H:i', strtotime($event_schedule['end_date_time']));
                            }                   

                            // Get recurring instances for frequency=weekly
                            if ($event_schedule["frequency"] == "weekly") {
                                $weekly_output_array = $this->getWeeklyRecurringEvents($start_date, $end_date, $event_schedule, $from_date, $to_date);
                                $final_event_instances = array_merge($weekly_output_array, $final_event_instances);
                            } else {
                                // Get recurring instances for frequency=monthly
                                $monthly_start_date = new \DateTime($event_schedule['start_date_time']);
                                $monthly_start_date->format('Y-m-d H:i');
                                if (empty($event_schedule['end_date_time'])) {
                                    $monthly_end_date = date('Y-m-d H:i', strtotime("+12 months", strtotime($event_schedule['start_date_time'])));
                                    $monthly_end_date = new \DateTime($monthly_end_date);
                                    $monthly_end_date->format('Y-m-d H:i');
                                } else{
                                    $monthly_end_date = new \DateTime($event_schedule['end_date_time']);
                                    $monthly_end_date->format('Y-m-d H:i');
                                }
                                $monthly_output_array = $this->getMonthlyRecurringEvents($monthly_start_date, $monthly_end_date, $event_schedule, $from_date, $to_date);
                                $final_event_instances = array_merge($monthly_output_array, $final_event_instances);
                            }
                        } else {
                            $event_schedule['start_date_time'] = date('Y-m-d H:i', strtotime($event_schedule['start_date_time']));
                            $event_schedule['end_date_time'] = date('Y-m-d H:i', strtotime("+" . $event_schedule['duration'] . " minutes", strtotime($event_schedule['start_date_time'])));
                            $final_event_instances[] = array( 
                                "event_id" => $event_schedule["id"],
                                "event_name" => $event_schedule["event_name"],
                                "start_date_time" => $event_schedule["start_date_time"],
                                "end_date_time" => $event_schedule["end_date_time"],
                                "invitees" => $event_schedule["invitees"]
                            );
                        }
                    }               
                    // Final response array
                    $response = array("status" => "success", "data" => $final_event_instances);
                }  
            } catch (\Exception $e) {
                $response = array("status" => "failed", "message" => "Invalid data format", "error" => $e);
            }
        }
        
        $this->response->type('json'); 
        $this->response->body(json_encode( $response ));
        return $this->response;
    }

    /**
     * Edit method
     *
     * @param string|null $id Event id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\DataSource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $event = $this->Event->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $event = $this->Event->patchEntity($event, $this->request->getData());
            if ($this->Event->save($event)) {
                $this->Flash->success(__('The event has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The event could not be saved. Please, try again.'));
        }
        $this->set(compact('event'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Event id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\DataSource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $event = $this->Event->get($id);
        if ($this->Event->delete($event)) {
            $this->Flash->success(__('The event has been deleted.'));
        } else {
            $this->Flash->error(__('The event could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Function to validate query params for get api
     *  
     */
    public function getDataValidate($data) {
        $validator = new Validator();
        $validator->notEmpty('from', 'From date is required')
        ->dateTime('from');
        $validator->notEmpty('to', 'To date is required')
        ->dateTime('to');
        $validator
        ->allowEmptyString('invitees')
        ->add('invitees', [
            'validateInviteesFormat' => [
                'rule' =>  function ($event) {
                    $invitees_list = explode(",", $event);
                    $user_ids = TableRegistry::get('Users')->find()->select(['id'])
                    ->where(function ($exp, $q) use ($invitees_list) {
                        return $exp->in('id', $invitees_list);
                    });
                    $encoded_user_ids = json_encode($user_ids);
                    if (count($invitees_list) != count(json_decode($encoded_user_ids))) {
                        return false;
                    }
                    return true;
                },
                'message' => 'Invitees should be list of valid user ids or should be blank and the format should be like 1,2,3',
            ]
        ]);
        $errors = $validator->errors($data);
        return $errors;
    }

    /**
     * Function to get all weekly recurring events
     * 
     */
    public function getWeeklyRecurringEvents($start, $end, $event_details, $from_date, $to_date, $format = 'Y-m-d h:i') { 
        $array = array(); 
        $interval = new \DateInterval('P7D'); 
        $real_end = new \DateTime($end); 
        $real_end->add($interval); 
        $period = new \DatePeriod(new \DateTime($start), $interval, $real_end); 
        foreach($period as $date) {                  
            $instance_start_date_time = $date->format($format);
            if( ($instance_start_date_time >= $from_date) && ($instance_start_date_time <= $to_date) && ($instance_start_date_time <= $end)) {
                $instance_end_date_time = date('Y-m-d H:i', strtotime("+" . $event_details['duration'] . " minutes", strtotime($instance_start_date_time)));
                $array[] = array( 
                    "event_id" => $event_details["id"],
                    "event_name" => $event_details["event_name"],
                    "start_date_time" => $instance_start_date_time,
                    "end_date_time" => $instance_end_date_time,
                    "invitees" => $event_details["invitees"]
                );
            }
        } 
        return $array; 
    } 

    /**
     * Function to get all monthly recurring events
     * 
     */
    public function getMonthlyRecurringEvents($begin, $end, $event_details, $from_date, $to_date) { 
        $response_dates = array();
        if ( ($begin->format('d') == 29 || $begin->format('d') == 30) && ($begin->format('n') == 1) ){
            $selected_date = $begin->format('d');
            for($i = 0; $i <= 12; $i++) {
                $special_case_response = $this->getMonthlyRecurringEventsForSpecialCase($begin, $selected_date, $event_details, $from_date, $to_date, $i, $end);
                if ($special_case_response[0] != null) {
                    $response_dates[] = $special_case_response[0];
                }  
            }
        } else {
            $last_day_interval = \DateInterval::createFromDateString('last day of next month');
            $month_interval = new \DateInterval('P1M');
            $last_days = new \DatePeriod(clone $begin, $last_day_interval, 12);
            $added_month_days = new \DatePeriod(clone $begin, $month_interval, 12);
            
            $last_days_array = array();
            foreach ($last_days as $last_day) {
                $last_days_array[] = $last_day;
            }

            $added_month_days_array = array();
            foreach ($added_month_days as $added_month_day) {
                $added_month_days_array[] = $added_month_day;
            }

            for ($i = 0; $i < 12; $i++) {
                if ($added_month_days_array[$i] > $last_days_array[$i]) {
                    $instance_start_date_time = $last_days_array[$i]->format('Y-m-d h:i');
                } else {
                    $instance_start_date_time = $added_month_days_array[$i]->format('Y-m-d h:i');
                }
                if( ($instance_start_date_time >= $from_date) && ($instance_start_date_time <= $to_date) && ($instance_start_date_time <= $end->format('Y-m-d h:i'))) {
                    $instance_end_date_time = date('Y-m-d H:i', strtotime("+" . $event_details['duration'] . " minutes", strtotime($instance_start_date_time)));
                    $response_dates[] = array( 
                        "event_id" => $event_details["id"],
                        "event_name" => $event_details["event_name"],
                        "start_date_time" => $instance_start_date_time,
                        "end_date_time" => $instance_end_date_time,
                        "invitees" => $event_details["invitees"]
                    );
                }
            }
        }
        return $response_dates;
    }

    /**
     * Function to get all monthly recurring events
     * based on the special case of dates and to handle the leap year scenario
     * 
     */
    public function getMonthlyRecurringEventsForSpecialCase($date, $selected_date, $event_details, $from_date, $to_date, $i, $end) {
        $days = cal_days_in_month(CAL_GREGORIAN, $date->format('n'), $date->format('Y'));
        if($date->format('n') == 1) {
            // Check if selected date is 29 or 30 Jan then set Feb end date accordingly
            if($selected_date == 29) {
                // Check if the given year is leap year or not
                if ($date->format('Y') % 4 == 0) {
                    $res_array[] = $this->setSpecialCaseDates($date, 31, $event_details, $from_date, $to_date, $i, $end);
                } else {
                    $res_array[] = $this->setSpecialCaseDates($date, 30, $event_details, $from_date, $to_date, $i, $end);
                }
            }
            else{
                
                // Check if the given year is leap year or not
                if ($date->format('Y') % 4 == 0) {
                    $res_array[] = $this->setSpecialCaseDates($date, 30, $event_details, $from_date, $to_date, $i, $end);
                } else {
                    $res_array[] = $this->setSpecialCaseDates($date, 29, $event_details, $from_date, $to_date, $i, $end);
                }
            }
        }
        else if($date->format('n') == 2) {
            // Check if selected date is 29 or 30 Jan then set March end date accordingly
            if($selected_date == 29) {
                $res_array[] = $this->setSpecialCaseDates($date, 29, $event_details, $from_date, $to_date, $i, $end);
            } else {
                $res_array[] = $this->setSpecialCaseDates($date, 30, $event_details, $from_date, $to_date, $i, $end);
            }
        } else {
            $res_array[] = $this->setSpecialCaseDates($date, $days, $event_details, $from_date, $to_date, $i, $end);
        }
        return $res_array;
    }

    /**
     * Function to return the event instances in case of
     * special dates and leap year
     * 
     */
    public function setSpecialCaseDates($date, $days, $event_details, $from_date, $to_date, $i, $end) {
        if($i != 0) {
            $date->add(new \DateInterval('P'.$days.'D'));
        }
        $instance_start_date_time = $date->format("Y-m-d h:i");
        if( ($instance_start_date_time >= $from_date) && ($instance_start_date_time <= $to_date) && ($instance_start_date_time <= $end->format('Y-m-d h:i'))) {
            $instance_end_date_time = date('Y-m-d h:i', strtotime("+" . $event_details['duration'] . " minutes", strtotime($instance_start_date_time)));
            return array( 
                "event_id" => $event_details["id"],
                "event_name" => $event_details["event_name"],
                "start_date_time" => $instance_start_date_time,
                "end_date_time" => $instance_end_date_time,
                "invitees" => $event_details["invitees"]
            );
        }        
    }

    
}
