<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class WorkersRequestTest extends TestCase
{
    public function testWorkersStore()
    {
        $response = $this->postJson(route('workers.store'), ['firstname' => "test", 'lastname' => "test"]);
        $worker = json_decode($response->getContent());
        $deleteResponse = $this->deleteJson(route('workers.destroy', ['id' => $worker->id]));

        $response->assertStatus(200);
    }

    public function testWorkersRequestStore()
    {
        $response = $this->postJson(route('workers.store'), ['firstname' => "test", 'lastname' => "test"]);
        $worker = json_decode($response->getContent());

        $responseStore = $this->postJson(route('request.store', ['worker_id' => $worker->id, 'vacation_start_date' => "2020-01-01", 'vacation_end_date' => "2020-01-02"]));

        $request = json_decode($responseStore->getContent());

        $responseDestroy = $this->deleteJson(route('request.destroy', ['worker_id' => $worker->id, 'request' => $request->id]));

        $deleteResponse = $this->deleteJson(route('workers.destroy', ['id' => $worker->id]));

        $response->assertStatus(200);
    }
    /**
     * 
     *
     * @return void
     */
    public function testWorkersRequestStatusPending()
    {
        $workerResponse = $this->postJson(route('workers.store'), ['firstname' => "test", 'lastname' => "test"]);
        $worker = json_decode($workerResponse->getContent());
        $response = $this->getJson(route('workers.request.status.pending', ['worker_id' => $worker->id]));

        $deleteResponse = $this->deleteJson(route('workers.destroy', ['id' => $worker->id]));

        $response->assertStatus(200);
    }

    /**
     * 
     *
     * @return void
     */
    public function testWorkersRequestStatusApproved()
    {
        $workerResponse = $this->postJson(route('workers.store'), ['firstname' => "test", 'lastname' => "test"]);
        $worker = json_decode($workerResponse->getContent());
        $response = $this->getJson(route('workers.request.status.approved', ['worker_id' => $worker->id]));

        $deleteResponse = $this->deleteJson(route('workers.destroy', ['id' => $worker->id]));

        $response->assertStatus(200);
    }

    /**
     * 
     *
     * @return void
     */
    public function testWorkersRequestStatusRejected()
    {
        $workerResponse = $this->postJson(route('workers.store'), ['firstname' => "test", 'lastname' => "test"]);
        $worker = json_decode($workerResponse->getContent());
        $response = $this->getJson(route('workers.request.status.rejected', ['worker_id' => $worker->id]));

        $deleteResponse = $this->deleteJson(route('workers.destroy', ['id' => $worker->id]));

        $response->assertStatus(200);
    }
}
