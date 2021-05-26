<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class ManagerRequestTest extends TestCase
{
    /**
     * 
     *
     * @return void
     */
    public function testManagersRequestStatusPending()
    {
        $response = $this->getJson(route('managers.request.status.pending'));

        $response->assertStatus(200);
    }

     /**
     * 
     *
     * @return void
     */
    public function testManagersRequestStatusApproved()
    {
        $response = $this->getJson(route('managers.request.status.approved'));

        $response->assertStatus(200);
    }

      /**
     * 
     *
     * @return void
     */
    public function testManagerRequestOverlapping()
    {
        $response = $this->getJson(route('managers.request.overlapping'));

        $response->assertStatus(200);
    }

      /**
     * 
     *
     * @return void
     */
    public function testManagersRequestList()
    {
        $response = $this->getJson(route('managers.request.all'));

        $response->assertStatus(200);
    }

    public function testManagerRequestShow()
    {
        $response = $this->getJson(route('managers.request.all'));

        $response->assertStatus(200);
    }

}
