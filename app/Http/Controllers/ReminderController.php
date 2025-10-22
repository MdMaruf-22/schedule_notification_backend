<?php

namespace App\Http\Controllers;

use App\Models\Reminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ReminderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return response()->json(Reminder::latest()->get());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'remind_at' => 'required|date',
            'fcm_token' => 'nullable|string',
        ]);

        $reminder = Reminder::create($validated);
        return response()->json($reminder, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Reminder $reminder)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reminder $reminder)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Reminder $reminder)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
        $reminder = Reminder::findOrFail($id);
        $reminder->delete();
        return response()->json(['message' => 'Reminder deleted successfully.']);
    }

    public function sendFcmTest(Request $request)
    {
        $validated = $request->validate([
            'fcm_token' => 'required|string',
            'title' => 'required|string',
            'body' => 'required|string',
        ]);

        $fcmKey = env('FCM_SERVER_KEY');

        $response = Http::withHeaders(
            [
                'Authorization' => 'key=' . $fcmKey,
                'Content-Type' => 'application/json',
            ]
        )->post('https://fcm.googleapis.com/fcm/send', [
            'to' => $validated['fcm_token'],
            'notification' => [
                'title' => $validated['title'],
                'body' => $validated['body'],
            ],
        ]);

        return response()->json($response->json(), $response->status());
    }
}
