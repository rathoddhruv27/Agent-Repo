@extends('layouts.app')

@section('title', 'Recent Prompts - VnnoAI')

@section('content')
<div class="container-fluid container-custom">
    <div class="page-header">
        <h2 class="fw-bold mb-0">My Prompts</h2>
        <a href="/" class="btn btn-premium d-flex align-items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2v10z"></path>
            </svg>
            Back to Chat
        </a>
    </div>

    @if($agents->isEmpty())
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" class="mb-3 text-muted">
                <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h4>No history found</h4>
            <p class="text-muted">Your questions will appear here once you start a conversation.</p>
        </div>
    @else
        <div class="history-container">
            @foreach($agents as $agent)
                <div class="history-card">
                    <div class="card-header-custom">
                        <div class="text-muted small fw-bold">
                            {{ $agent->created_at->format('M d, Y • H:i') }}
                        </div>
                    </div>
                    <div class="card-content">
                        <div class="prompt-section mb-0">
                            {{ $agent->prompt }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
