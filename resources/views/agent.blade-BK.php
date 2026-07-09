@extends('layouts.app')

@section('title', 'Agent - Alternative')

@section('content')
<div class="chat-wrapper">
    <div id="chatResponseArea" class="chat-column">
        @if(isset($lastInteraction))
        <div class="message-round user-message animate-in">
            <div class="message-content">
                <!-- <div class="message-label">You</div> -->
                <div class="message-text">{{ $lastInteraction->prompt }}</div>
            </div>
        </div>

        <div id="responseBox" class="message-round ai-message animate-in">
            <div class="message-content">
                <div class="message-label">Alternative</div>
                <div id="responseContent" class="markdown-rendered message-text" data-raw-content="{{ $lastInteraction->response ?? '' }}">
                    {!! Str::markdown($lastInteraction->response ?? '') !!}
                </div>
                
                <div id="metaContainer" class="message-meta mt-3 d-flex gap-3 op-50">
                    <span class="small-badge">{{ strtoupper($lastInteraction->agent ?? '') }}</span>
                    <span class="small-badge">{{ $lastInteraction->model ?? '' }}</span>
                    <span class="small-badge ms-auto">{{ $lastInteraction->time ?? '' }}</span>
                    <button class="btn-icon ms-2 copy-btn-individual" title="Copy">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                    </button>
                </div>
            </div>
        </div>
        @endif

        <div id="loading" class="d-none message-round ai-message">
            <div class="message-content">
                <div class="message-label">Alternative</div>
                <div class="d-flex gap-1 mt-2">
                    <div class="dot"></div>
                    <div class="dot" style="animation-delay: 0.2s"></div>
                    <div class="dot" style="animation-delay: 0.4s"></div>
                </div>
            </div>
        </div>

        <div id="errorBox" class="alert alert-danger mx-auto mt-4 d-none" style="max-width: 600px; border-radius: 12px; background: #450a0a; border: 1px solid #7f1d1d; color: #fecaca;"></div>
    </div>

    <div class="chat-footer-wrapper">
        <div class="chat-footer-container">
            <form id="agentForm" class="input-pill-container">
                <!-- <button type="button" class="btn-pill-icon"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg></button> -->
                <textarea id="prompt" name="prompt" rows="1" placeholder="Ask anything..." class="pill-input" style="height: 44px;"></textarea>
                <div class="pill-actions">
                    <!-- <button type="button" class="btn-pill-icon"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"></path><path d="M19 10v2a7 7 0 0 1-14 0v-2"></path><line x1="12" y1="19" x2="12" y2="23"></line><line x1="8" y1="23" x2="16" y2="23"></line></svg></button> -->
                    <button type="submit" id="submitBtn" class="btn-pill-send">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"></path></svg>
                    </button>
                </div>
            </form>
            <div id="promptError" class="text-danger small mt-2 text-center d-none"></div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .animate-in {
        animation: slideIn 0.3s ease-out;
    }
    @keyframes slideIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .w-fit-content { width: fit-content; }
    .hover-opacity-100:hover { opacity: 1 !important; }
    
    .chat-wrapper {
        flex: 1;
        display: flex;
        flex-direction: column;
        height: 100vh;
        overflow: hidden;
    }

    .chat-column {
        flex: 1;
        overflow-y: auto;
        padding: 40px 0 180px 0;
        scroll-behavior: smooth;
        display: flex;
        flex-direction: column;
        align-items: center;
        scrollbar-width: thin;
        scrollbar-color: rgba(255, 255, 255, 0.1) transparent;
    }

    .chat-column::-webkit-scrollbar { width: 4px; }
    .chat-column::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); border-radius: 10px; }

    .message-round {
        width: 100%;
        max-width: 800px;
        padding: 24px 20px;
        display: flex;
        justify-content: center;
    }

    .message-content {
        width: 100%;
        max-width: 720px;
    }

    .message-label {
        font-weight: 700;
        font-size: 0.9rem;
        margin-bottom: 8px;
        color: white;
    }

    .message-text {
        font-size: 1rem;
        line-height: 1.6;
        color: #ececec;
    }

    .user-message { background: transparent; }
    .ai-message { background: transparent; }

    .small-badge {
        font-size: 0.65rem;
        font-weight: 700;
        background: #2f2f2f;
        padding: 4px 8px;
        border-radius: 6px;
        color: #b4b4b4;
    }

    .op-50 { opacity: 0.6; }
    .btn-icon {
        background: transparent;
        border: none;
        color: #b4b4b4;
        cursor: pointer;
        padding: 0;
        transition: color 0.2s;
    }
    .btn-icon:hover { color: white; }

    .dot { width: 6px; height: 6px; background: #b4b4b4; border-radius: 50%; animation: pulse 1.5s infinite; }
    @keyframes pulse { 0%, 100% { opacity: 0.3; } 50% { opacity: 1; } }

    /* Floating Footer */
    .chat-footer-wrapper {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 20px;
        background: linear-gradient(transparent, var(--bg-dark) 40%);
        display: flex;
        justify-content: center;
        z-index: 10;
    }

    .chat-footer-container {
        width: 100%;
        max-width: 800px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .input-pill-container {
        width: 100%;
        background: var(--bg-card);
        border-radius: 28px;
        padding: 8px 12px;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: box-shadow 0.2s;
    }

    .input-pill-container:focus-within {
        box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.1);
    }

    .pill-input {
        flex: 1;
        background: transparent;
        border: none;
        color: white;
        padding: 10px 4px;
        font-size: 1rem;
        outline: none;
        resize: none;
    }

    .btn-pill-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: transparent;
        border: none;
        color: #b4b4b4;
        border-radius: 50%;
        transition: background 0.2s, color 0.2s;
    }
    .btn-pill-icon:hover { background: #353535; color: white; }

    .pill-actions { display: flex; align-items: center; gap: 4px; }

    .btn-pill-send {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: white;
        border: none;
        color: black;
        border-radius: 50%;
        transition: opacity 0.2s;
    }
    .btn-pill-send:hover { opacity: 0.8; }

    .thinking-chip {
        background: var(--sidebar-hover);
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 8px;
        color: white;
    }

    .spin { animation: spin 2s linear infinite; }
    @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

    .disclaimer-text {
        font-size: 0.75rem;
        color: #676767;
        margin-top: 12px;
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

    // scroll to bottom
    const responseContent = document.getElementById('responseContent');
    const rawInitial = responseContent.getAttribute('data-raw-content');
    if (rawInitial) {
        responseContent.innerHTML = marked.parse(rawInitial);
        setTimeout(scrollToBottom, 100);
    }

    function scrollToBottom() {
        const container = document.getElementById('chatResponseArea');
        container.scrollTop = container.scrollHeight;
    }

    function appendInteraction(prompt, response, agent, model, time) {
        const container = document.getElementById('chatResponseArea');
        const rawContent = Array.isArray(response) ? response.join('\n\n') : response;
        
        const html = `
            <div class="message-round user-message animate-in">
                <div class="message-content">
                    <div class="message-label">You</div>
                    <div class="message-text">${prompt}</div>
                </div>
            </div>
            <div class="message-round ai-message animate-in">
                <div class="message-content">
                    <div class="message-label">Alternative</div>
                    <div class="markdown-rendered message-text">${marked.parse(rawContent)}</div>
                    <div class="message-meta mt-3 d-flex gap-3 op-50">
                        <span class="small-badge">${agent.toUpperCase()}</span>
                        <span class="small-badge">${model}</span>
                        <span class="small-badge ms-auto">${time}</span>
                    </div>
                </div>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', html);
        setTimeout(scrollToBottom, 50);
    }

    document.getElementById('agentForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const promptInput = document.getElementById('prompt');
        const prompt = promptInput.value.trim();
        const promptError = document.getElementById('promptError');
        const statusIndicator = document.getElementById('statusIndicator');
        const loading = document.getElementById('loading');
        const errorBox = document.getElementById('errorBox');
        const submitBtn = document.getElementById('submitBtn');

        promptError.classList.add('d-none');
        errorBox.classList.add('d-none');
        
        if (!prompt) {
            promptError.textContent = 'Please enter a message';
            promptError.classList.remove('d-none');
            promptInput.focus();
            return;
        }

        loading.classList.remove('d-none');
        statusIndicator.classList.remove('d-none');
        submitBtn.disabled = true;
        scrollToBottom();

        try {
            const response = await fetch('/agent/ask', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ prompt: prompt })
            });

            const result = await response.json();

            loading.classList.add('d-none');
            statusIndicator.classList.add('d-none');
            submitBtn.disabled = false;

            if (result.status) {
                appendInteraction(prompt, result.data.content, result.agent, result.model, result.time);
                promptInput.value = '';
                promptInput.style.height = '44px';
                promptInput.focus();
            } else {
                errorBox.textContent = result.message || 'Error generating response.';
                errorBox.classList.remove('d-none');
            }

        } catch (error) {
            loading.classList.add('d-none');
            statusIndicator.classList.add('d-none');
            submitBtn.disabled = false;
            errorBox.textContent = 'Connection error. Please try again.';
            errorBox.classList.remove('d-none');
        }
    });

    // Auto-resize textarea
    document.getElementById('prompt').addEventListener('input', function() {
        this.style.height = '44px';
        this.style.height = (this.scrollHeight) + 'px';
    });

    // Sidebar AJAX loading
    document.querySelectorAll('.nav-sub-link').forEach(link => {
        link.addEventListener('click', async function(e) {
            if (window.location.pathname !== '/') return; 
            
            e.preventDefault();
            const url = this.getAttribute('href');
            
            try {
                const response = await fetch(url, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const result = await response.json();
                if (result.status) {
                    appendInteraction(result.data.prompt, result.data.response, result.data.agent, result.data.model, result.data.time);
                }
            } catch (err) {
                console.error('Failed to load history:', err);
            }
        });
    });

    // Delegated Copy Handler
    document.addEventListener('click', function(e) {
        if (e.target.closest('.copy-btn-individual')) {
            const btn = e.target.closest('.copy-btn-individual');
            const round = btn.closest('.message-round');
            const text = round.querySelector('.markdown-rendered').innerText;
            
            navigator.clipboard.writeText(text).then(() => {
                const originalIcon = btn.innerHTML;
                btn.innerHTML = '<svg width="14" height="14" fill="none" stroke="#4ade80" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>';
                setTimeout(() => { btn.innerHTML = originalIcon; }, 2000);
            });
        }
    });

    document.getElementById('copyBtn')?.addEventListener('click', function() {
        const text = document.getElementById('responseContent').innerText;
        navigator.clipboard.writeText(text).then(() => {
            const originalIcon = this.innerHTML;
            this.innerHTML = '<svg width="14" height="14" fill="none" stroke="#4ade80" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>';
            setTimeout(() => { this.innerHTML = originalIcon; }, 2000);
        });
    });
</script>
@endsection