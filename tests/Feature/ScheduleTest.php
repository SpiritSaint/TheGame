<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function testScheduleRun()
    {
        $this->artisan('schedule:run')
            ->assertExitCode(0);
    }
}
