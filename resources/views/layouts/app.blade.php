<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'AI Support Agent')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('robo.png') }}">
    
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
    
    <style>
        :root {
            --primary: #5436da;
            --primary-dark: #442ab6;
            --sidebar-bg: #171717;
            --bg-dark: #0d0d0d;
            --bg-card: #212121;
            --sidebar-hover: #2f2f2f;
            --text-main: #ececec;
            --text-muted: #b4b4b4;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-main);
            min-height: 100vh;
            margin: 0;
            display: flex;
            overflow: hidden; /* Lock the whole page scroll */
        }

        /* Sidebar Styling */
        .sidebar {
            width: 280px;
            background-color: var(--sidebar-bg);
            color: white;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            display: flex;
            flex-direction: column;
            z-index: 1000;
        }

        .sidebar-nav-container {
            flex: 1;
            overflow-y: auto;
            padding: 0 24px;
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.2) transparent;
        }

        .sidebar-nav-container::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar-nav-container::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
        }

        .sidebar-brand {
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: white;
        }

        .nav-link {
            color: var(--text-muted);
            padding: 10px 12px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.2s;
            text-decoration: none;
            margin-bottom: 2px;
            font-weight: 500;
            font-size: 0.85rem;
        }

        .nav-link:hover, .nav-link.active {
            background-color: var(--sidebar-hover);
            color: white;
        }

        .nav-link svg {
            width: 20px;
            height: 20px;
        }

        /* Sub-nav Styling */
        .nav-group {
            margin-bottom: 8px;
        }
        
        .nav-group-title {
            color: #64748b;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 700;
            padding: 12px 16px 8px 16px;
        }
        
        .nav-sub-link {
            color: var(--text-muted);
            padding: 8px 12px 8px 42px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.2s;
            text-decoration: none;
            margin-bottom: 2px;
            font-weight: 400;
            font-size: 0.85rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .nav-sub-link:hover, .nav-sub-link.active {
            background-color: var(--sidebar-hover);
            color: white;
        }

        .sidebar-footer {
            margin-top: auto;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding: 10px 14px;
            }

        .user-profile {
            padding: 12px;
            margin-bottom: 12px;
        }

        .main-content {
            margin-left: 280px;
            flex: 1;
            height: 100vh;
            display: flex;
            flex-direction: column;
            background-color: var(--bg-dark);
            position: relative;
        }

        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
        }

        /* Common Components */
        .premium-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0,0,0,0.05);
        }

        .btn-premium {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn-premium:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);    
            color: white;
        }

        /* History Actions UI */
        .history-item-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
        }

        .nav-sub-link {
            flex: 1;
            padding-right: 40px !important; /* Make room for the button */
        }

        .history-options-btn {
            position: absolute;
            right: 8px;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            border: none;
            background: transparent;
            color: var(--text-muted);
            opacity: 0;
            transition: all 0.2s;
            z-index: 5;
        }

        .history-item-wrapper:hover .history-options-btn {
            opacity: 1;
        }

        .history-options-btn:hover, .history-options-btn.active {
            background: var(--sidebar-hover);
            color: white;
        }

        /* Context Menu */
        .history-context-menu {
            position: fixed;
            background: #202123;
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            width: 180px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.5);
            z-index: 2000;
            padding: 6px;
            display: none;
        }

        .menu-item {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            border: none;
            background: transparent;
            color: #ececec;
            font-size: 0.85rem;
            text-align: left;
            transition: background 0.2s;
        }

        .menu-item:hover {
            background: #2f2f2f;
        }

        .menu-item.delete {
            color: #ef4444;
        }
        
        .menu-item.delete:hover {
            background: #450a0a;
        }

        .menu-item svg { width: 16px; height: 16px; opacity: 0.7; }
        .menu-item.delete svg { opacity: 1; }

        /* Custom Modal */
        .custom-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 3000;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.2s, visibility 0.2s;
        }

        .custom-modal-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        .custom-modal {
            background: #2f2f2f;
            border-radius: 12px;
            padding: 24px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            transform: translateY(10px);
            transition: transform 0.2s;
        }

        .custom-modal-overlay.show .custom-modal {
            transform: translateY(0);
        }

        .custom-modal .modal-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 12px;
            color: white;
            margin-top: 0;
        }

        .custom-modal .modal-text {
            font-size: 0.95rem;
            color: var(--text-muted);
            margin-bottom: 24px;
        }

        .custom-modal .modal-text strong {
            color: white;
            font-weight: 600;
        }

        .custom-modal .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .custom-modal .btn-cancel {
            background: transparent;
            border: none;
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
        }

        .custom-modal .btn-cancel:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .custom-modal .btn-delete {
            background: #ef4444;
            border: none;
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
        }

        .custom-modal .btn-delete:hover {
            background: #dc2626;
        }

        /* Rename Input */
        .rename-input {
            background: #1e1e1e;
            border: 1px solid var(--primary);
            color: white;
            border-radius: 4px;
            padding: 2px 6px;
            font-size: 0.85rem;
            width: calc(100% - 20px);
            outline: none;
            margin-right: 8px;
        }
    </style>

       <style>
        .container-custom {
            max-width: 900px;
            margin: auto;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }

        .history-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            margin-bottom: 24px;
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.05);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .history-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .card-header-custom {
             padding: 16px 24px;
             background: #f1f5f9;
             border-bottom: 1px solid rgba(0,0,0,0.05);
             display: flex;
             justify-content: space-between;
             align-items: center;
        }

        .card-content {
            padding: 24px;
        }

        .prompt-section {
            background: #f8fafc;
            padding: 16px;
            border-radius: 12px;
            border-left: 4px solid var(--primary-color);
            margin-bottom: 20px;
            font-weight: 500;
            color: #334155;
        }

        .response-section {
            background: #1e293b;
            color: #f8fafc;
            padding: 20px;
            border-radius: 12px;
            position: relative;
            line-height: 1.6;
        }

        .meta-info {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.05em;
            display: flex;
            gap: 12px;
        }

        .badge-provider {
            background: #e2e8f0;
            color: #475569;
            padding: 2px 8px;
            border-radius: 6px;
        }

        .copy-btn {
            position: absolute;
            top: 12px;
            right: 12px;
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: #94a3b8;
            padding: 6px;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .copy-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        /* Markdown Styles */
        .markdown-rendered p:last-child { margin-bottom: 0; }
        .markdown-rendered pre {
            background: #0f172a;
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

        .empty-state {
            text-align: center;
            padding: 60px;
            background: white;
            border-radius: 20px;
            color: var(--text-muted);
        }
    </style>
    
    @yield('styles')
</head>
<body>

    @auth
    <div class="sidebar">
        <a href="/" class="sidebar-brand">
            <img src="{{ asset('robo.png') }}" alt="Logo" width="40" height="40">
            <h4 class="mb-0">Alternative</h4>
        </a>

        <div class="sidebar-nav-container">
            <nav class="nav flex-column">
                <a class="nav-link {{ Request::is('/') ? 'active' : '' }}" href="/">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                        <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                        <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/>
                    </svg>
                    New Chat
                </a>
                
                <div class="nav-group">
                    <div class="nav-group-title">Recents</div>
                    @foreach($agents as $agent)
                        @php
                            $isActive = false;
                            if (isset($messages) && $messages->isNotEmpty()) {
                                if ($agent->conversation_id && $messages->first()->conversation_id == $agent->conversation_id) {
                                    $isActive = true;
                                } elseif (!$agent->conversation_id && $messages->first()->id == $agent->id) {
                                    $isActive = true;
                                }
                            }
                        @endphp
                        <div class="history-item-wrapper" data-id="{{ $agent->id }}">
                            <a class="nav-sub-link {{ $isActive ? 'active' : '' }}" href="/history/{{ $agent->id }}" title="{{ $agent->prompt }}">
                                <!-- <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 14px; height: 14px;"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> -->
                                <span class="text-truncate">{{ Str::limit($agent->prompt, 20) }}</span>
                            </a>
                            <button type="button" class="history-options-btn" onclick="showHistoryMenu(event, '{{ $agent->id }}', '{{ addslashes($agent->prompt) }}')">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z"/></svg>
                            </button>
                        </div>
                    @endforeach
                </div>
            </nav>
        </div>

        <div id="historyContextMenu" class="history-context-menu">
            <button class="menu-item" onclick="handleRename()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                Rename
            </button>
            <div style="height: 1px; background: rgba(255,255,255,0.05); margin: 4px 6px;"></div>
            <button class="menu-item delete" onclick="handleDelete()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"></path><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"></path><path d="M10 11v6"></path><path d="M14 11v6"></path></svg>
                Delete
            </button>
        </div>

        <!-- Hidden Forms for History Actions -->
        <form id="renameForm" method="POST" style="display:none;">
            @csrf
            @method('PATCH')
            <input type="hidden" name="title" id="renameInput">
        </form>

        <form id="deleteForm" method="POST" style="display:none;">
            @csrf
            @method('DELETE')
        </form>

        <div class="sidebar-footer">
            <div class="user-profile mb-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-weight: 600;">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div class="overflow-hidden">
                        <p class="mb-0 text-white text-truncate fw-bold small">{{ Auth::user()->name }}</p>
                        <p class="mb-0 text-truncate x-small text-white-50" style="font-size: 0.7rem;">{{ Auth::user()->email }}</p>
                    </div>
                </div>
            </div>
            
            <form action="/logout" method="POST">
                @csrf
                <button type="submit" class="nav-link w-100 border-0 bg-transparent text-start">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                    Logout
                </button>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="custom-modal-overlay">
        <div class="custom-modal">
            <h4 class="modal-title">Delete chat?</h4>
            <p class="modal-text">This will delete <strong id="deleteItemTitle"></strong>.</p>
            <div class="modal-actions">
                <button class="btn-cancel" onclick="closeDeleteModal()">Cancel</button>
                <button class="btn-delete" onclick="confirmDelete()">Delete</button>
            </div>
        </div>
    </div>
    @endauth

    <main class="main-content" style="{{ Auth::check() ? '' : 'margin-left: 0; width: 100%;' }}">
        @yield('content')
    </main>

@yield('scripts')

    <script>
        let activeHistoryId = null;
        let activeHistoryTitle = "";

        function showHistoryMenu(event, id, title) {
            event.preventDefault();
            event.stopPropagation();
            
            activeHistoryId = id;
            activeHistoryTitle = title;
            
            const menu = document.getElementById('historyContextMenu');
            const btn = event.currentTarget;
            const rect = btn.getBoundingClientRect();
            
            menu.style.display = 'block';
            menu.style.top = rect.bottom + 'px';
            menu.style.left = (rect.right - 180) + 'px';
            
            // Add global click listener to close menu
            setTimeout(() => {
                document.addEventListener('click', closeHistoryMenu);
            }, 10);
        }

        function closeHistoryMenu() {
            document.getElementById('historyContextMenu').style.display = 'none';
            document.removeEventListener('click', closeHistoryMenu);
        }

        function handleRename() {
            closeHistoryMenu();
            
            const wrapper = document.querySelector(`.history-item-wrapper[data-id="${activeHistoryId}"]`);
            if (!wrapper) return;
            
            const link = wrapper.querySelector('.nav-sub-link');
            const span = link.querySelector('.text-truncate');
            
            // Create input
            const input = document.createElement('input');
            input.type = 'text';
            input.className = 'rename-input';
            input.value = activeHistoryTitle;
            
            // Hide span, show input
            span.style.display = 'none';
            link.insertBefore(input, span);
            
            // Focus and select all
            input.focus();
            input.select();
            
            // Prevent link click when clicking input
            input.addEventListener('click', e => e.preventDefault());
            
            // Handle blur and enter
            const saveRename = () => {
                const newTitle = input.value.trim();
                if (newTitle && newTitle !== activeHistoryTitle) {
                    var form = document.getElementById('renameForm');
                    form.action = '/history/' + activeHistoryId;
                    document.getElementById('renameInput').value = newTitle;
                    form.submit();
                } else {
                    input.remove();
                    span.style.display = '';
                }
            };
            
            input.addEventListener('blur', saveRename);
            input.addEventListener('keydown', e => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    saveRename();
                } else if (e.key === 'Escape') {
                    input.remove();
                    span.style.display = '';
                }
            });
        }

        function handleDelete() {
            closeHistoryMenu();
            document.getElementById('deleteItemTitle').textContent = activeHistoryTitle;
            document.getElementById('deleteModal').classList.add('show');
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.remove('show');
        }
        
        function confirmDelete() {
            var form = document.getElementById('deleteForm');
            form.action = '/history/' + activeHistoryId;
            form.submit();
        }

        window.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.shiftKey && e.key.toLowerCase() === 'o') {
                e.preventDefault();
                window.location.href = '/';
            }
        });
    </script>
</body>
</html>
