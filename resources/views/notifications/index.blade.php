@extends('layouts.app')

@section('title', 'Notifications')
@section('page_title', 'My Notifications')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold m-0"><i class="bi bi-bell text-primary me-2"></i> All System Notifications</h5>
        @if(auth()->user()->unreadNotifications()->count() > 0)
            <form action="{{ route('notifications.read-all') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                    <i class="bi bi-check-all me-1"></i> Mark All as Read
                </button>
            </form>
        @endif
    </div>

    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            @forelse($notifications as $notif)
                <div class="list-group-item px-4 py-3 border-bottom d-flex justify-content-between align-items-center {{ is_null($notif->read_at) ? 'bg-light' : '' }}">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar-circle {{ is_null($notif->read_at) ? 'bg-primary text-white' : 'bg-light text-muted' }}">
                            <i class="bi bi-bell"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark text-sm">{{ $notif->title }}</div>
                            <div class="text-xs text-muted">{{ $notif->message }}</div>
                            <div class="text-xs text-muted mt-1">{{ $notif->created_at->diffForHumans() }}</div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        @if(is_null($notif->read_at))
                            <form action="{{ route('notifications.read', $notif->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-light border rounded-pill px-3">
                                    <i class="bi bi-check me-1"></i> Mark Read
                                </button>
                            </form>
                        @endif
                        @if($notif->link)
                            <a href="{{ $notif->link }}" class="btn btn-sm btn-primary rounded-pill px-3">
                                View
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-bell-slash fs-1 d-block mb-2 text-muted"></i>
                    No notifications in your inbox.
                </div>
            @endforelse
        </div>

        @if($notifications->hasPages())
            <div class="px-4 py-3 border-top d-flex justify-content-between align-items-center">
                <div class="text-muted text-xs">
                    Showing {{ $notifications->firstItem() }} to {{ $notifications->lastItem() }} of {{ $notifications->total() }} results
                </div>
                <div>
                    {{ $notifications->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
