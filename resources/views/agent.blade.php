@extends('layouts.app')

@section('title', 'Agent - Aureon')

@section('content')
<div class="chat-wrapper">
    <div id="chatResponseArea" class="chat-column">
        @if($messages->isEmpty())
        <div class="welcome-container animate-in">
            <h1 class="welcome-title text-white">Good to see you, {{ explode(' ', Auth::user()->name)[0] }}</h1>
        </div>
        @endif

        @foreach($messages as $msg)
        <div class="message-round user-message animate-in">
            <div class="message-content user-content">
                <div class="message-label text-end fs-5"></div>
                <div class="message-text user-bubble">{{ $msg->prompt }}</div>
            </div>
        </div>

        <div class="message-round ai-message animate-in">
            <div class="message-content">
                <div class="message-label fs-5"></div>
                <div class="markdown-rendered message-text" data-raw-content="{{ $msg->response ?? '' }}">
                    {!! Str::markdown($msg->response ?? '') !!}
                </div>
                
                <div class="message-meta mt-3 d-flex gap-3 op-50">
                    <button class="btn-icon copy-btn-individual" title="Copy">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                    </button>
                </div>
            </div>
        </div>
        @endforeach

        <div id="loading" class="d-none message-round ai-message">
            <div class="message-content">
                <div class="message-label">Aureon</div>
                <div class="d-flex gap-1 mt-2">
                    <div class="dot"></div>
                    <div class="dot" style="animation-delay: 0.2s"></div>
                    <div class="dot" style="animation-delay: 0.4s"></div>
                </div>
            </div>
        </div>


    </div>

    <div class="chat-footer-wrapper">
        <div class="chat-footer-container">
            <form id="agentForm" class="input-pill-container">
                <input type="hidden" id="conversation_id" value="{{ $currentConversationId }}">
                <textarea id="prompt" name="prompt" rows="1" placeholder="Ask anything..." class="pill-input"></textarea>
                <div class="pill-actions">
                    <!-- <button type="button" class="btn-pill-icon"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"></path><path d="M19 10v2a7 7 0 0 1-14 0v-2"></path><line x1="12" y1="19" x2="12" y2="23"></line><line x1="8" y1="23" x2="16" y2="23"></line></svg></button> -->
                    <button type="submit" id="submitBtn" class="btn-pill-send" disabled>
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"></path></svg>
                    </button>
                </div>
            </form>
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
    .ai-message  { background: transparent; }

    /* Right-align user messages */
    .user-content {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
    }
    .user-bubble {
        background: linear-gradient(135deg, #3a3a3a, #252525);
        border-radius: 18px 18px 4px 18px;
        padding: 12px 18px;
        max-width: 80%;
        width: fit-content;
        text-align: left;
        border: 1px solid rgba(255, 255, 255, 0.08);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.25), inset 0 1px 0 rgba(255, 255, 255, 0.07);
        text-shadow: 0 1px 1px rgba(0,0,0,0.2);
    }

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
        background: linear-gradient(transparent, rgba(15,15,15,0.85) 30%, rgba(15,15,15,1) 100%);
        display: flex;
        justify-content: center;
        z-index: 10;
        pointer-events: none;
    }

    .chat-footer-container {
        width: 100%;
        max-width: 800px;
        display: flex;
        flex-direction: column;
        align-items: center;
        pointer-events: auto;
    }

    .input-pill-container {
        width: 100%;
        background: rgba(45, 45, 45, 0.7);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border-radius: 28px;
        padding: 8px 12px;
        display: flex;
        align-items: center;
        gap: 8px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-top: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.4), inset 0 1px 1px rgba(255, 255, 255, 0.1);
        transition: box-shadow 0.3s ease, transform 0.3s ease;
    }

    .input-pill-container:focus-within {
        box-shadow: 0 16px 36px rgba(0, 0, 0, 0.5), inset 0 1px 1px rgba(255, 255, 255, 0.15), 0 0 0 1px rgba(255,255,255,0.1);
        transform: translateY(-2px);
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
        max-height: 200px;
        height: 44px; /* default height */
        line-height: 24px;
        overflow-y: auto;
    }

    .pill-input::-webkit-scrollbar { width: 4px; }
    .pill-input::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); border-radius: 10px; }

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
        background: linear-gradient(135deg, #ffffff 0%, #c4c4c4 100%);
        border: none;
        color: #111;
        border-radius: 50%;
        box-shadow: 0 4px 10px rgba(255,255,255,0.15), inset 0 1px 0 rgba(255,255,255,1);
        transition: all 0.2s cubic-bezier(0.25, 0.8, 0.25, 1);
        cursor: pointer;
    }
    .btn-pill-send:hover:not(:disabled) { 
        transform: translateY(-2px);
        box-shadow: 0 6px 14px rgba(255,255,255,0.25), inset 0 1px 0 rgba(255,255,255,1);
    }
    .btn-pill-send:active:not(:disabled) {
        transform: translateY(2px);
        box-shadow: 0 1px 2px rgba(255,255,255,0.1), inset 0 1px 3px rgba(0,0,0,0.3);
    }
    .btn-pill-send:disabled {
        opacity: 0.3;
        cursor: default;
        box-shadow: none;
    }

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

    /* Welcome Message */
    .welcome-container {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding-top: 20vh;
        text-align: center;
    }
    .welcome-title {
        font-size: 2.6rem;
        font-weight: 600;
        letter-spacing: -0.02em;
        background: linear-gradient(180deg, #ffffff 0%, #a3a3a3 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        text-shadow: 0px 4px 25px rgba(255, 255, 255, 0.2);
    }

    /* Responsive Styles */
    @media (max-width: 992px) {
        .message-round {
            padding: 20px 15px;
        }
        .chat-column {
            padding: 30px 0 160px 0;
        }
        .welcome-title {
            font-size: 2rem;
        }
    }

    @media (max-width: 768px) {
        .message-round {
            padding: 16px 12px;
        }
        .chat-column {
            padding: 20px 0 140px 0;
        }
        .user-bubble {
            max-width: 90%;
        }
        .welcome-title {
            font-size: 1.8rem;
        }
        .chat-footer-wrapper {
            padding: 12px;
        }
        .input-pill-container {
            padding: 6px 10px;
            background: #2f2f2f;
            border-radius: 24px;
            flex-direction: row;
            align-items: center;
        }
        .pill-input {
            width: 100%;
            padding: 8px 4px;
            height: 40px;
        }
        .pill-actions {
            display: flex;
            align-items: center;
            margin-top: 0;
        }
    }

    @media (max-width: 480px) {
        .message-round {
            padding: 12px 10px;
        }
        .chat-column {
            padding: 15px 0 130px 0;
        }
        .user-bubble {
            max-width: 95%;
        }
        .welcome-title {
            font-size: 1.5rem;
        }
        .chat-footer-wrapper {
            padding: 10px;
        }
        .message-label {
            font-size: 0.8rem;
        }
        .message-text {
            font-size: 0.95rem;
        }
        .pill-input {
            font-size: 0.95rem;
        }
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

    // Render initial server-side response if present
    const responseContent = document.getElementById('responseContent');
    if (responseContent) {
        const rawInitial = responseContent.getAttribute('data-raw-content');
        if (rawInitial) {
            responseContent.innerHTML = marked.parse(rawInitial);
            setTimeout(scrollToBottom, 100);
        }
    }

    function scrollToBottom() {
        const container = document.getElementById('chatResponseArea');
        container.scrollTop = container.scrollHeight;
    }

    // Only inserts the AI response — user bubble is added immediately on submit
    function appendAiResponse(rawContent) {
        const container = document.getElementById('chatResponseArea');
        const content = Array.isArray(rawContent) ? rawContent.join('\n\n') : rawContent;
        const html = `
            <div class="message-round ai-message animate-in">
                <div class="message-content">
                    <div class="message-label">Aureon</div>
                    <div class="markdown-rendered message-text">${marked.parse(content)}</div>
                    <div class="message-meta mt-3 d-flex gap-3 op-50">
                        <button class="btn-icon copy-btn-individual" title="Copy">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                        </button>
                    </div>
                </div>
            </div>`;
        container.insertAdjacentHTML('beforeend', html);
        if (window.enhanceCodeBlocks) {
            window.enhanceCodeBlocks(container);
        }
        setTimeout(scrollToBottom, 50);
    }

    function handleAiError(message, promptInput, originalPrompt) {
        window.showToast(message, 'error', 3000);
        
        // Remove the failed user bubble from the UI
        const bubbles = document.querySelectorAll('.user-message');
        if (bubbles.length > 0) {
            bubbles[bubbles.length - 1].remove();
        }
        
        // Restore the prompt text so the user doesn't lose it
        if (promptInput) {
            promptInput.value = originalPrompt;
            promptInput.style.height = 'auto';
            promptInput.style.height = (promptInput.scrollHeight) + 'px';
        }
    }

    function addUserBubble(prompt) {
        const container = document.getElementById('chatResponseArea');
        
        const welcome = document.querySelector('.welcome-container');
        if (welcome) welcome.remove();

        const safePrompt = prompt.replace(/</g, '&lt;').replace(/>/g, '&gt;');
        container.insertAdjacentHTML('beforeend', `
            <div class="message-round user-message animate-in">
                <div class="message-content user-content">
                    <div class="message-label text-end fs-5"></div>
                    <div class="message-text user-bubble">${safePrompt}</div>
                </div>
            </div>`);
        scrollToBottom();
    }

    function addToSidebar(id, prompt) {
        const navGroup = document.querySelector('.sidebar-nav-container .nav-group');
        if (!navGroup) return;

        // Remove active class from any existing links
        navGroup.querySelectorAll('.nav-sub-link').forEach(l => l.classList.remove('active'));

        const label = prompt.length > 20 ? prompt.substring(0, 20) + '...' : prompt;
        const safeTitle = prompt.replace(/"/g, '&quot;');
        const safeJsPrompt = prompt.replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/"/g, '&quot;');

        const wrapper = document.createElement('div');
        wrapper.className = 'history-item-wrapper';
        wrapper.setAttribute('data-id', id);
        wrapper.innerHTML = `
            <a class="nav-sub-link active" id="history-link-${id}" href="/history/${id}" title="${safeTitle}">
                <span class="text-truncate">${label}</span>
            </a>
            
            <div class="inline-rename-container" id="inline-rename-${id}" style="display: none; width: 100%; padding: 4px 8px; margin: 2px 4px;">
                <input type="text" class="form-control form-control-sm bg-dark text-white border-secondary" id="inline-rename-input-${id}" value="${safeTitle}" style="font-size: 0.85rem;" autocomplete="off" onkeydown="handleInlineRenameKeydown(event, '${id}')" onblur="cancelInlineRename('${id}')">
            </div>

            <button type="button" class="history-options-btn" id="history-btn-${id}" onclick="showHistoryMenu(event, '${id}', '${safeJsPrompt}')">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z"/></svg>
            </button>`;
        
        // Insert after the "Recents" title
        const title = navGroup.querySelector('.nav-group-title');
        title ? title.insertAdjacentElement('afterend', wrapper) : navGroup.prepend(wrapper);
    }

    document.getElementById('agentForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const promptInput = document.getElementById('prompt');
        const prompt = promptInput.value.trim();
        const loading = document.getElementById('loading');
        const submitBtn = document.getElementById('submitBtn');

        if (!prompt) return;

        // Show user bubble immediately — visible during loading
        addUserBubble(prompt);

        // Move loading indicator to the bottom so it appears after user's new message
        document.getElementById('chatResponseArea').appendChild(loading);
        loading.classList.remove('d-none');
        submitBtn.disabled = true;

        // Clear input immediately
        promptInput.value = '';
        promptInput.style.height = window.innerWidth <= 768 ? '40px' : '44px';
        submitBtn.disabled = true; // Disable button after send
        
        scrollToBottom();

        try {
            const conversation_id = document.getElementById('conversation_id').value;
            const response = await fetch('/agent/ask', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ 
                    prompt: prompt,
                    conversation_id: conversation_id
                })
            });

            const result = await response.json();

            loading.classList.add('d-none');
            submitBtn.disabled = false;

            if (result.status) {
                appendAiResponse(result.data.content);
                
                // Update URL and conversation state
                if (result.conversation_id) {
                    const conversationInput = document.getElementById('conversation_id');
                    const isNewThread = !conversationInput.value || conversationInput.value === 'new';

                    conversationInput.value = result.conversation_id;
                    window.history.pushState({}, '', '/history/' + result.id);
                    
                    // ONLY add to sidebar if it's the very first message
                    if (isNewThread) {
                        addToSidebar(result.id, prompt); 
                    }
                }
                promptInput.focus();
            } else {
                handleAiError(result.message || 'Error generating response.', promptInput, prompt);
            }

        } catch (error) {
            loading.classList.add('d-none');
            submitBtn.disabled = false;
            handleAiError('Connection error. Please try again.', promptInput, prompt);
        }
    });

    // Auto-resize textarea and toggle send button
    document.getElementById('prompt').addEventListener('input', function() {
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = this.value.trim() === '';
        
        this.style.height = window.innerWidth <= 768 ? '40px' : '44px';
        this.style.height = (this.scrollHeight) + 'px';
    });

    // Press Enter to submit
    document.getElementById('prompt').addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            this.form.dispatchEvent(new Event('submit', {cancelable: true, bubbles: true}));
        }
    });

    // Sidebar AJAX loading
    document.querySelectorAll('.nav-sub-link').forEach(link => {
        link.addEventListener('click', async function(e) {
            if (window.location.pathname !== '/agent') return; 
            
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
                window.showToast('Copied to clipboard!', 'success');
            });
        }
    });

    document.getElementById('copyBtn')?.addEventListener('click', function() {
        const text = document.getElementById('responseContent').innerText;
        navigator.clipboard.writeText(text).then(() => {
            const originalIcon = this.innerHTML;
            this.innerHTML = '<svg width="14" height="14" fill="none" stroke="#4ade80" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>';
            setTimeout(() => { this.innerHTML = originalIcon; }, 2000);
            window.showToast('Copied to clipboard!', 'success');
        });
    });

    // Scroll to bottom on load/refresh
    window.addEventListener('load', () => {
        setTimeout(scrollToBottom, 100);
    });

    // Global Toast System
    window.showToast = function(message, type = 'success') {
        const container = document.getElementById('toastContainer');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = `aureon-toast ${type}`;
        
        const iconHtml = type === 'success' 
            ? `<svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>`
            : `<svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>`;

        toast.innerHTML = `
            <div class="aureon-toast-content">
                <div class="aureon-toast-icon">${iconHtml}</div>
                <span>${message}</span>
            </div>
            <button class="aureon-toast-close">&times;</button>
        `;

        container.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('show');
        }, 10);

        const timer = setTimeout(() => {
            dismissToast(toast);
        }, 4000);

        toast.querySelector('.aureon-toast-close').addEventListener('click', () => {
            clearTimeout(timer);
            dismissToast(toast);
        });
    };

    function dismissToast(toast) {
        toast.classList.remove('show');
        toast.addEventListener('transitionend', () => {
            toast.remove();
        });
    }

    // Show session toasts if any exist
    @if(session('success'))
        window.showToast("{{ session('success') }}", 'success');
    @endif
    @if(session('error'))
        window.showToast("{{ session('error') }}", 'error');
    @endif
    @if(session('status'))
        window.showToast("{{ session('status') }}", 'success');
    @endif
</script>
@endsection