<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    // Get all tickets
    public function index()
    {
        return Ticket::with(['event', 'order'])->get();
    }

    // Get single ticket
    public function show($id)
    {
        return Ticket::with(['event', 'order'])->findOrFail($id);
    }

    // Create ticket
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id'    => 'required|exists:orders,id',
            'event_id'    => 'required|exists:events,id',
            'ticket_code' => 'required|string|unique:tickets',
        ]);

        return Ticket::create([
            ...$validated,
            'status' => 'valid'
        ]);
    }

    // Update ticket status (used/cancelled)
    public function update(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:valid,used,cancelled'
        ]);

        $ticket->update($validated);

        return $ticket;
    }
}
