<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PersonalBroadcastRecipient;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $personalBroadcasts = collect();

        try {
            if (Schema::hasTable('personal_broadcasts') && Schema::hasTable('personal_broadcast_recipients')) {
                $personalBroadcasts = PersonalBroadcastRecipient::where('user_id', $user->id)
                    ->with('broadcast')
                    ->latest()
                    ->get()
                    ->groupBy('personal_broadcast_id')
                    ->filter(function ($group) {
                        return !$group->contains(function ($recipient) {
                            return !is_null($recipient->viewed_at);
                        });
                    })
                    ->map(function ($group) {
                        return $group->first();
                    })
                    ->values();
            }
        } catch (\Throwable $e) {
            report($e);
            $personalBroadcasts = collect();
        }

        return view('dashboard', compact('user', 'personalBroadcasts'));
    }
}