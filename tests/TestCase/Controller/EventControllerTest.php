<?php
namespace App\Test\TestCase\Controller;

use App\Controller\EventController;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Cake\ORM\TableRegistry;

/**
 * App\Controller\EventController Test Case
 *
 * @uses \App\Controller\EventController
 */
class EventControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.Event', 'app.Users'
    ];

    /**
     * Test index method
     *
     * @return void
     */
    public function testIndex()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test view method
     *
     * @return void
     */
    public function testView()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test add method
     *
     * @return void
     */
    public function testAdd()
    {
        $events = TableRegistry::getTableLocator()->get('Event');
        $start_date = date('Y-m-d H:i',strtotime("+1 day"));
        $end_date = date('Y-m-d H:i',strtotime("+2 day"));
        $data = [
            'event_name' => 'New event',
            'frequency' => 'once_off',
            'start_date_time' => $start_date ,
            'end_date_time' => $end_date,
            'duration' => 30,
            'invitees' => [1]
        ];

        // It should create event if the provided data is valid
        $this->post('/Event/add', $data);
        $this->assertResponseSuccess();
        $query = $events->find()->where(['event_name' => $data['event_name']]);
        $this->assertEquals(1, $query->count());


        $invalid_data = [
            'event_name' => '',
            'frequency' => 'once_off',
            'start_date_time' => '2020-12-15 00:00',
            'end_date_time' => '12/15/2020 00:00',
            'duration' => 30,
            'invitees' => [1]
        ];

        // It should return validation errors if provided data is invalid
        $this->post('/Event/add', $invalid_data);
        $this->assertResponseSuccess();
        $query = $events->find()->where(['event_name' => $invalid_data['event_name']]);
        $this->assertEquals(0, $query->count());
        $this->assertResponseContains("This field cannot be left empty");
        $this->assertResponseContains("The start date time should be greater than current time");
        $this->assertResponseContains("The provided value is invalid");

    }

    /**
     * Test get method
     *
     * @return void
     */
    public function testGet()
    {
        // should return invitee events for valid params
        $this->get('/Event/get?from=2021-06-01 13:31&to=2021-08-28 13:31&invitees=1');
        $this->assertResponseSuccess();
        $this->assertResponseContains('Event Test');

        // should return error if invitee id is invalid
        $this->get('/Event/get?from=2022-01-10 06:00&to=2022-09-15 04:00&invitees=2');
        $this->assertResponseSuccess();
        $this->assertResponseContains("Invitees should be list of valid user ids or should be blank and the format should be like 1,2,3");
        
        // should ask for valid from and to date if not provided
        $this->get('/Event/get?from=&to=&invitees=1');
        $this->assertResponseSuccess();
        $this->assertResponseContains("From date is required");
        $this->assertResponseContains("To date is required");
    }
    /**
     * Test edit method
     *
     * @return void
     */
    public function testEdit()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test delete method
     *
     * @return void
     */
    public function testDelete()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
