<?php

// app/Http/Controllers/Client/NotificationController.php
namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Mostra tutte le notifiche dell'utente
     */
    public function index()
    {
        $notifications = Auth::user()
            ->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('client.notifications.index', compact('notifications'));
    }

    /**
     * Segna una notifica come letta
     */
    public function markAsRead($id)
    {
        $notification = Auth::user()
            ->notifications()
            ->findOrFail($id);

        $notification->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Segna tutte le notifiche come lette
     */
    public function markAllAsRead()
    {
        Auth::user()
            ->notifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Elimina una notifica
     */
    public function destroy($id)
    {
        $notification = Auth::user()
            ->notifications()
            ->findOrFail($id);

        $notification->delete();

        return back()->with('success', 'Notifica eliminata con successo.');
    }
}