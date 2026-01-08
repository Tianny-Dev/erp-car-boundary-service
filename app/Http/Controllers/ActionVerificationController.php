<?php

namespace App\Http\Controllers;

use App\Models\ActionVerification;
use App\Notifications\Channels\MoviderSmsChannel;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notification;

class ActionVerificationController extends Controller
{
    public function sendActionCode(Request $request)
    {
        $user = $request->user();
        $action = $request->action;

        // Generate 6-digit code
        $code = rand(100000, 999999);

        // Save code
        ActionVerification::create([
            'user_id' => $user->id,
            'action' => $action,
            'code' => $code,
            'expires_at' => now()->addMinutes(5),
        ]);

        // Send SMS
        $user->notify(new class($code) extends Notification
        {
            public $code;

            public function __construct($code)
            {
                $this->code = $code;
            }

            public function via($notifiable)
            {
                return [MoviderSmsChannel::class];
            }

            public function toMovider($notifiable)
            {
                return "Your verification code is {$this->code}";
            }
        });

        return response()->json(['message' => '2FA code sent via SMS']);
    }
}
