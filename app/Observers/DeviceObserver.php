<?php

namespace App\Observers;

use App\Models\Device;
use Illuminate\Support\Str;

class DeviceObserver
{
    /**
     * Handle the Device "creating" event.
     *
     * @param Device $device
     * @return void
     */
    public function creating(Device $device)
    {
        $device->api_token = bcrypt(Str::random(32));
    }
}
