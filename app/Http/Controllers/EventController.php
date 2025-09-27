<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    // Get all events
    public function index()
    {
        return Event::with('user')->get();
    }

    // Get single event
    // public function show($id)
    // {
    //     return Event::with('user')->findOrFail($id);
    // }

    public function store(Request $request)
    {
        $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'venue'         => 'required|string|max:255',
            'date'          => 'required|date',
            'category'      => 'required|string|max:100',
            'price'         => 'required|numeric|min:0',
            'total_tickets' => 'required|integer|min:1',
            'image'         => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            // Save in storage/app/public/events
            $path = $request->file('image')->store('events', 'public');
            $imagePath = 'storage/' . $path; // accessible link
        }

        $event = Event::create([
            'title'         => $request->title,
            'description'   => $request->description,
            'venue'         => $request->venue,
            'date'          => $request->date,
            'category'      => $request->category,
            'price'         => $request->price,
            'total_tickets' => $request->total_tickets,
            'sold_tickets'  => 0,
            'image'         => $imagePath,
            'user_id'       => Auth::id(),
        ]);

        return response()->json([
            'message' => 'Event created successfully!',
            'event'   => $event,
        ], 201);
    }

    
    // Create new event
    // public function store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'title'         => 'required|string|max:255',
    //         'description'   => 'nullable|string',
    //         'venue'         => 'required|string|max:255',
    //         'date'          => 'required|date',
    //         'category'      => 'required|string',
    //         'price'         => 'required|numeric',
    //         'total_tickets' => 'required|integer|min:1',
    //         'user_id'       => 'required|exists:users,id',
    //     ]);

    //     return Event::create($validated);
    // }

    // Update event
    public function update(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        $validated = $request->validate([
            'title'         => 'sometimes|string|max:255',
            'description'   => 'sometimes|string',
            'venue'         => 'sometimes|string|max:255',
            'date'          => 'sometimes|date',
            'category'      => 'sometimes|string',
            'price'         => 'sometimes|numeric',
            'total_tickets' => 'sometimes|integer|min:1',
        ]);

        $event->update($validated);

        return $event;
    }

    // Delete event
    public function destroy($id)
    {
        $event = Event::findOrFail($id);
        $event->delete();

        return response()->json(['message' => 'Event deleted successfully']);
    }
}
