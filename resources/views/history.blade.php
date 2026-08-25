@extends('layouts.app')

@section('title', 'History - Aureon')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Interaction History</h2>
        <a href="/" class="btn btn-premium d-flex align-items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2v10z"></path>
            </svg>
            Back to Chat
        </a>
    </div>

    @if($agents->isEmpty())
        <div class="text-center py-5 bg-white rounded-4 shadow-sm border">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" class="mb-3 text-muted">
                <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h4>No history found</h4>
            <p class="text-muted">Your AI interactions will appear here once you start a conversation.</p>
        </div>
    @else
        <div class="history-container">
            @foreach($agents as $agent)
                <div class="card mb-4 shadow-sm border-0 rounded-4 overflow-hidden">
                    <div class="card-header bg-light border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                        <div class="text-muted small fw-bold">
                            {{ $agent->created_at->format('M d, Y • H:i') }}
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <label class="form-label fw-bold text-muted small text-uppercase">Question</label>
                            <div class="p-3 bg-light rounded-3 border-start border-4 border-primary">
                                {{ $agent->prompt }}
                            </div>
                        </div>

                        <div>
                            <label class="form-label fw-bold text-muted small text-uppercase">AI Response</label>
                            <div class="p-3 bg-dark text-white rounded-3 position-relative">
                                <button class="btn btn-sm btn-outline-light border-0 position-absolute end-0 top-0 mt-2 me-2 opacity-50 js-copy-btn" title="Copy response">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                                    </svg>
                                </button>
                                <div class="markdown-rendered js-markdown-content" data-raw-content="{{ $agent->response }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection

@section('styles')
<style>
    .markdown-rendered p:last-child { margin-bottom: 0; }
    .markdown-rendered pre {
        background: #000000;
        padding: 1rem;
        border-radius: 8px;
        margin: 1rem 0;
        overflow-x: auto;
    }
    .markdown-rendered code {
        font-family: monospace;
        background: rgba(255, 255, 255, 0.1);
        padding: 0.2rem 0.4rem;
        border-radius: 4px;
    }
</style>
@endsection

@section('scripts')
<script>
    marked.setOptions({
        highlight: function(code, lang) {
            const language = hljs.getLanguage(lang) ? lang : 'plaintext';
            return hljs.highlight(code, { language }).value;
        },
        langPrefix: 'hljs language-',
        breaks: true,
        gfm: true
    });

    document.querySelectorAll('.js-markdown-content').forEach(container => {
        const raw = container.getAttribute('data-raw-content');
        if (raw) {
            container.innerHTML = marked.parse(raw);
        }
    });

    // Copy Functionality
    document.querySelectorAll('.js-copy-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const contentDiv = this.nextElementSibling;
            const text = contentDiv.innerText;
            
            navigator.clipboard.writeText(text).then(() => {
                const originalIcon = this.innerHTML;
                this.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="#4ade80" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                `;
                setTimeout(() => { this.innerHTML = originalIcon; }, 2000);
            });
        });
    });
</script>
@endsection
