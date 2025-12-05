<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Support\Str;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Mail\Message;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
class OrderController extends Controller
{
    // Get all orders
    public function index()
    {
        return Order::with('event')->get();
    }

    // Get single order
    public function show($id)
    {
        return Order::with('event')->findOrFail($id);
    }

    // Create new order (ticket purchase)
    // public function store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'event_id'      => 'required|exists:events,id',
    //         'buyer_name'    => 'required|string|max:255',
    //         'buyer_email'   => 'required|email',
    //         'buyer_address' => 'nullable|string',
    //         'quantity'      => 'required|integer|min:1',
    //     ]);

    //     $event = Event::findOrFail($validated['event_id']);

    //     // Check availability
    //     if ($event->sold_tickets + $validated['quantity'] > $event->total_tickets) {
    //         return response()->json(['error' => 'Not enough tickets available'], 400);
    //     }

    //     // Create order
    //     $order = Order::create([
    //         ...$validated,
    //         'status' => 'paid'
    //     ]);

    //     // Update sold tickets
    //     $event->sold_tickets += $validated['quantity'];
    //     $event->save();

    //     return $order;
    // }


    //     public function store(Request $request, $id)
    // {
    //     $event = Event::findOrFail($id);

    //     $request->validate([
    //         'name' => 'required',
    //         'email' => 'required|email',
    //         'address' => 'required',
    //         'quantity' => 'required|integer|min:1'
    //     ]);

    //     if ($event->sold_tickets + $request->quantity > $event->total_tickets) {
    //         return response()->json(['message' => 'Sold Out'], 400);
    //     }

    //     // Create order
    //     $order = Order::create([
    //         'event_id' => $event->id,
    //         'buyer_name' => $request->name,
    //         'buyer_email' => $request->email,
    //         'buyer_address' => $request->address,
    //         'quantity' => $request->quantity,
    //         'status' => 'paid',
    //     ]);

    //     // Generate tickets
    //     for ($i = 0; $i < $request->quantity; $i++) {
    //         Ticket::create([
    //             'order_id' => $order->id,
    //             'event_id' => $event->id,
    //             'ticket_code' => strtoupper(Str::random(10)),
    //             'status' => 'valid',
    //         ]);
    //     }

    //     // Update event sold tickets
    //     $event->increment('sold_tickets', $request->quantity);

    //     // Send email with ticket info (simplified)
    //     Mail::raw("Thank you for registering for {$event->title}. Your ticket code(s) are attached.", function ($message) use ($request) {
    //         $message->to($request->email)
    //                 ->subject('Your Event Ticket');
    //     });

    //     return response()->json(['message' => 'Order successful, ticket sent']);
    // }

   public function store(Request $request, $id)
{
    $event = Event::findOrFail($id);

    $request->validate([
        'name' => 'required',
        'email' => 'required|email',
        'address' => 'required',
        'quantity' => 'required|integer|min:1'
    ]);

    // Check availability
    if ($event->sold_tickets + $request->quantity > $event->total_tickets) {
        return response()->json(['message' => 'Sold Out'], 400);
    }

    // Create order
    $order = Order::create([
        'event_id' => $event->id,
        'buyer_name' => $request->name,
        'buyer_email' => $request->email,
        'buyer_address' => $request->address,
        'quantity' => $request->quantity,
        'status' => 'paid',
    ]);

    $ticketFiles = [];

    // Ensure the tickets directory exists
    if (!file_exists(storage_path('app/tickets'))) {
        mkdir(storage_path('app/tickets'), 0755, true);
    }

    for ($i = 0; $i < $request->quantity; $i++) {
        $ticket = Ticket::create([
            'order_id' => $order->id,
            'event_id' => $event->id,
            'ticket_code' => strtoupper(Str::random(10)),
            'status' => 'valid',
        ]);

        // CHANGED: Use .svg extension
        $qrPath = 'tickets/' . $ticket->ticket_code . '.svg';
        $qrFullPath = storage_path('app/' . $qrPath);

        // CHANGED: Generate SVG (No Imagick required)
        QrCode::format('svg')
            ->size(300)
            ->generate($ticket->ticket_code, $qrFullPath);

        $ticketFiles[] = $qrFullPath;
    }

    $event->increment('sold_tickets', $request->quantity);

    // Send email with attachments
    Mail::send([], [], function (Message $message) use ($request, $ticketFiles, $event) {
        $message->to($request->email)
            ->subject("Your Ticket(s) for {$event->title}")
            ->html("Thank you for registering for {$event->title}. Your ticket QR code(s) are attached below.");
            // ->setBody("Thank you for registering for {$event->title}. Your ticket QR code(s) are attached below.", 'text/html');

        foreach ($ticketFiles as $file) {
            $message->attach($file, [
                'as' => basename($file),
                'mime' => 'image/svg+xml', // Explicitly set mime type helps email clients
            ]);
        }
    });

    return response()->json(['message' => 'Order successful, ticket sent']);
}



    // Delete order (optional)
    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();

        return response()->json(['message' => 'Order deleted successfully']);
    }
}
