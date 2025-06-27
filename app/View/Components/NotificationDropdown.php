<?php
namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Support\Facades\Auth;

class NotificationDropdown extends Component
{
    public $notifications;
    public $unreadCount;

    public function __construct()
    {
        $this->notifications = Auth::user()
            ->notifications()
            ->latest()
            ->limit(5)
            ->get();
            
        $this->unreadCount = Auth::user()
            ->notifications()
            ->unread()
            ->count();
    }

    public function render()
    {
        return view('components.notification-dropdown');
    }
}