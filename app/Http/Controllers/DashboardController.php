<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the dashboard data for the logged-in organizer
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Get all events created by this user
        $events = Event::where('user_id', $user->id)->get();

        // Add sold and remaining tickets for each event
        $events->map(function ($event) {
            $sold = Order::where('event_id', $event->id)->sum('quantity');
            $event->tickets_sold = $sold;
            $event->tickets_remaining = $event->total_tickets - $sold;
            return $event;
        });

        return response()->json([
            'user' => $user,
            'events' => $events,
        ], 200);
    }

    /**
     * Return all buyers for a specific event
     */
    public function buyers($id)
    {
        $event = Event::findOrFail($id);

        // Ensure the logged-in user is the owner of the event
        if ($event->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        // Get all buyers for this event
        $buyers = Order::where('event_id', $id)->get();

        return response()->json([
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'total_tickets' => $event->total_tickets,
                'tickets_sold' => $buyers->sum('quantity'),
                'tickets_remaining' => $event->total_tickets - $buyers->sum('quantity'),
            ],
            'buyers' => $buyers
        ], 200);
    }
}
