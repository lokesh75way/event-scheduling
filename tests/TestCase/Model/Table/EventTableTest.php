<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\EventTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\EventTable Test Case
 */
class EventTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\EventTable
     */
    public $Event;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.Event',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::getTableLocator()->exists('Event') ? [] : ['className' => EventTable::class];
        $this->Event = TableRegistry::getTableLocator()->get('Event', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Event);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault()
    {
        $start_date = date('Y-m-d H:i',strtotime("+1 day"));
        $end_date = date('Y-m-d H:i',strtotime("+2 day"));
        $data = [
            'event_name' => 'Test event',
            'start_date_time' => $start_date,
            'end_date_time' => $end_date,
            'frequency' => 'once_off',
            'duration' => 30,
            'invitees' => [1]
        ];
        $user = $this->Event->newEntity($data);
        $this->assertEmpty($user->getErrors());
    }
}
