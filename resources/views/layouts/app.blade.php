<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'AI Support Agent')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
            min-height: 100dvh;
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
            height: 100dvh;
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
            height: 100dvh;
            display: flex;
            flex-direction: column;
            background-color: var(--bg-dark);
            position: relative;
        }

        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
                width: 280px;
                max-width: 85vw;
            }
            .sidebar.show {
                transform: translateX(0);
                box-shadow: 10px 0 30px rgba(0,0,0,0.6);
            }
            .main-content {
                margin-left: 0;
            }
            .aureon-modal {
                max-width: min(92vw, 550px) !important;
                max-height: 90vh !important;
                margin: auto;
            }
            .profile-popover {
                left: 12px !important;
                bottom: 70px !important;
                max-width: calc(100vw - 24px) !important;
            }
            .toast-container {
                left: 12px !important;
                right: 12px !important;
                bottom: 12px !important;
            }
            .aureon-toast {
                min-width: 100% !important;
                max-width: 100% !important;
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
            background: #0d1117;
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

        /* Enhanced Code Blocks (ChatGPT Style) */
        .code-block-wrapper {
            background: #0d1117;
            border-radius: 8px;
            margin: 1.2rem 0;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .code-block-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #2d2d2d;
            padding: 8px 16px;
            color: #b4b4b4;
            font-size: 0.75rem;
            font-family: sans-serif;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        .code-block-header .code-block-lang {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .code-block-header .code-block-copy-btn {
            background: transparent;
            border: none;
            color: #b4b4b4;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: color 0.2s;
            font-size: 0.75rem;
            padding: 0;
        }
        .code-block-header .code-block-copy-btn:hover {
            color: #fff;
        }
        .code-block-wrapper .markdown-rendered pre,
        .code-block-wrapper pre {
            margin: 0 !important;
            border-radius: 0 !important;
            background: transparent !important;
            border: none !important;
        }
        .markdown-rendered pre code {
            background: transparent;
            padding: 0;
            border-radius: 0;
            color: inherit;
        }

        .empty-state {
            text-align: center;
            padding: 60px;
            background: white;
            border-radius: 20px;
            color: var(--text-muted);
        }

        /* Profile Popover Styling */
        .profile-popover {
            position: fixed;
            bottom: 74px;
            left: 14px;
            width: 260px;
            max-height: 80vh;
            overflow-y: auto;
            background-color: #212121;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 14px;
            z-index: 1050;
            padding: 6px;
            display: none; /* Controlled by JS */
            animation: popoverFadeIn 0.15s ease-out;
        }

        @media (max-width: 991.98px) {
            .profile-popover {
                bottom: 80px;
                left: 12px;
                width: calc(100% - 24px);
                max-width: 320px;
                z-index: 1060;
            }
        }

        @keyframes popoverFadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .popover-item {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 12px;
            border-radius: 8px;
            border: none;
            background: transparent;
            color: #ececec;
            font-size: 0.85rem;
            text-align: left;
            transition: background-color 0.15s, color 0.15s;
            text-decoration: none;
        }

        .popover-item:hover {
            background-color: #2f2f2f;
            color: white;
        }

        .popover-item-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .popover-item svg {
            width: 16px;
            height: 16px;
            color: #b4b4b4;
            transition: color 0.15s;
        }

        .popover-item:hover svg {
            color: white;
        }

        .popover-divider {
            height: 1px;
            background-color: rgba(255, 255, 255, 0.08);
            margin: 4px 6px;
        }

        /* Premium Upgrade Cards */
        .upgrade-card {
            border-radius: 12px;
            padding: 24px;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            position: relative;
            overflow: hidden;
        }

        .upgrade-card-free {
            background-color: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .upgrade-card-free:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }

        .upgrade-card-plus {
            background: linear-gradient(145deg, rgba(245, 158, 11, 0.15), rgba(245, 158, 11, 0.03));
            border: 1px solid rgba(245, 158, 11, 0.4);
            box-shadow: 0 10px 30px -10px rgba(245, 158, 11, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }

        .upgrade-card-plus:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 35px -10px rgba(245, 158, 11, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.2);
            border-color: rgba(245, 158, 11, 0.6);
        }

        /* 3D Premium Button */
        .btn-upgrade-premium {
            background: linear-gradient(135deg, #fbbf24 0%, #d97706 100%);
            border: none;
            color: #111;
            font-weight: 700;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(245, 158, 11, 0.3), inset 0 1px 1px rgba(255, 255, 255, 0.6);
            transition: all 0.2s;
        }

        .btn-upgrade-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(245, 158, 11, 0.4), inset 0 1px 1px rgba(255, 255, 255, 0.8);
            color: #000;
        }

        .btn-upgrade-premium:active {
            transform: translateY(1px);
            box-shadow: 0 2px 5px rgba(245, 158, 11, 0.3), inset 0 1px 3px rgba(0, 0, 0, 0.2);
        }

        /* Custom Premium Modals */
        .aureon-modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(1.5px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            animation: backdropFadeIn 0.2s ease-out;
        }

        @keyframes backdropFadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .aureon-modal {
            background-color: #171717; /* Match var(--sidebar-bg) */
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            width: 100%;
            max-width: 600px;
            box-shadow: 0 24px 48px -12px rgba(0, 0, 0, 0.5);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            animation: modalScaleIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes modalScaleIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        .btn-modal-cancel {
            background: transparent;
            border: 1px solid transparent;
            color: rgba(255, 255, 255, 0.5);
            border-radius: 50rem;
            padding: 0.375rem 1rem;
            font-size: 0.875rem;
            transition: color 0.2s, background-color 0.2s;
        }
        .btn-modal-cancel:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.1);
        }

        .aureon-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 24px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .aureon-modal-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
            margin: 0;
        }

        .aureon-modal-close {
            background: transparent;
            border: none;
            color: #b4b4b4;
            cursor: pointer;
            font-size: 1.5rem;
            line-height: 1;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.15s;
        }

        .aureon-modal-close:hover {
            color: white;
        }

        .aureon-modal-body {
            padding: 24px;
            overflow-y: auto;
            max-height: 70vh;
            color: #ececec;
        }

        .aureon-modal-footer {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
            padding: 16px 24px;
            border-top: none;
            background-color: #0d0d0d;
        }

        /* Tab layout inside Settings Modal */
        .settings-layout {
            display: flex;
            min-height: 380px;
        }

        .settings-sidebar {
            width: 180px;
            border-right: 1px solid rgba(255, 255, 255, 0.08);
            padding-right: 12px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .settings-tab-btn {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            border-radius: 8px;
            border: none;
            background: transparent;
            color: #b4b4b4;
            font-size: 0.85rem;
            font-weight: 500;
            text-align: left;
            transition: all 0.15s;
        }

        .settings-tab-btn:hover {
            background-color: rgba(255, 255, 255, 0.04);
            color: white;
        }

        .settings-tab-btn.active {
            background-color: rgba(255, 255, 255, 0.08);
            color: white;
        }

        .settings-content {
            flex: 1;
            padding-left: 24px;
        }

        .settings-pane {
            display: none;
        }

        .settings-pane.active {
            display: block;
        }

        /* Custom form inputs inside modals */
        .modal-input {
            width: 100%;
            background-color: #212121;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 10px 14px;
            color: white;
            font-size: 0.9rem;
            outline: none;
            transition: border-color 0.15s;
        }

        .modal-input:focus {
            border-color: rgba(255, 255, 255, 0.2);
        }

        .modal-textarea {
            resize: none;
            height: 100px;
        }

        .modal-label {
            display: block;
            font-size: 0.85rem;
            font-weight: 500;
            color: #b4b4b4;
            margin-bottom: 8px;
        }

        /* Avatar overlay and container styles */
        .avatar-container {
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .avatar-edit-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            opacity: 0;
            transition: opacity 0.2s;
            cursor: pointer;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .avatar-wrapper:hover .avatar-edit-overlay {
            opacity: 1;
        }

        /* Global Premium Toasts */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
            pointer-events: none;
        }

        .aureon-toast {
            min-width: 280px;
            max-width: 380px;
            background-color: #212121;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            padding: 14px 16px;
            color: #ececec;
            font-size: 0.85rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            pointer-events: auto;
            transform: translateX(120%);
            transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.35s ease;
            opacity: 0;
        }

        .aureon-toast.show {
            transform: translateX(0);
            opacity: 1;
        }

        .aureon-toast-content {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-grow: 1;
        }

        .aureon-toast-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            width: 20px;
            height: 20px;
            border-radius: 50%;
        }

        .aureon-toast.success .aureon-toast-icon {
            background-color: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .aureon-toast.error .aureon-toast-icon {
            background-color: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        .aureon-toast-close {
            background: transparent;
            border: none;
            color: #676767;
            cursor: pointer;
            padding: 0;
            font-size: 1.15rem;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.15s;
        }

        .aureon-toast-close:hover {
            color: white;
        }

        /* Complete elimination of all box shadows globally across the entire application */
        *,
        *::before,
        *::after,
        .modal,
        .modal-dialog,
        .modal-content,
        .aureon-modal,
        .aureon-modal-container,
        .profile-popover,
        .card,
        .sidebar,
        .model-dropdown-menu,
        .input-pill-container,
        .aureon-toast,
        .suggestion-btn,
        kbd,
        button,
        input,
        textarea {
            box-shadow: none !important;
            -webkit-box-shadow: none !important;
            filter: none !important;
            -webkit-filter: none !important;
        }
    </style>
    
    @yield('styles')
</head>
<body>
    <div id="toastContainer" class="toast-container"></div>

    @auth
    <div class="sidebar">
        <a href="/" class="sidebar-brand" title="Awareness is the greatest transformation">
            <img src="{{ asset('robo.png') }}" alt="Logo" width="40" height="40" style="border-radius: 50%; object-fit: cover;">
            <h4 class="mb-0">Aureon</h4>
        </a>

        <div class="sidebar-nav-container">
            <nav class="nav flex-column">
                <a class="nav-link {{ Request::is('/') ? 'active' : '' }}" href="/" title="Ctrl+Shift+O">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
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
                        
                        @php
                            $label = $agent->prompt;
                            if (strlen($label) > 20) {
                                $label = substr($label, 0, 20) . '...';
                            }
                            $safeTitle = e($agent->prompt);
                            $safeJsPrompt = addslashes($agent->prompt);
                            $uniqueId = $agent->conversation_id ?? $agent->id;
                        @endphp

                        <div class="history-item-wrapper" data-id="{{ $uniqueId }}">
                            <a class="nav-sub-link {{ $isActive ? 'active' : '' }}" id="history-link-{{ $uniqueId }}" href="/history/{{ $uniqueId }}" title="{{ $safeTitle }}">
                                <span class="text-truncate">{{ $label }}</span>
                            </a>
                            
                            <div class="inline-rename-container" id="inline-rename-{{ $uniqueId }}" style="display: none; width: 100%; padding: 4px 8px; margin: 2px 4px;">
                                <input type="text" class="form-control form-control-sm bg-dark text-white border-secondary" id="inline-rename-input-{{ $uniqueId }}" value="{{ $safeTitle }}" style="font-size: 0.85rem;" autocomplete="off" onkeydown="handleInlineRenameKeydown(event, '{{ $uniqueId }}')" onblur="cancelInlineRename('{{ $uniqueId }}')">
                            </div>

                            <button type="button" class="history-options-btn" id="history-btn-{{ $uniqueId }}" onclick="showHistoryMenu(event, '{{ $uniqueId }}', '{{ $safeJsPrompt }}')">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="1"></circle><circle cx="19" cy="12" r="1"></circle><circle cx="5" cy="12" r="1"></circle></svg>
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
            <div class="user-profile-trigger" id="profileTrigger" style="cursor: pointer; padding: 10px 12px; border-radius: 12px; transition: background-color 0.2s; display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                <div class="d-flex align-items-center gap-3 overflow-hidden" style="flex: 1;">
                    <div class="avatar-container" style="width: 36px; height: 36px; min-width: 36px; min-height: 36px; border-radius: 50% !important; overflow: hidden; flex-shrink: 0;">
                        <div class="bg-warning text-dark w-100 h-100 d-flex align-items-center justify-content-center fw-bold initials-avatar {{ Auth::user()->profile_image ? 'd-none' : '' }}" style="font-size: 0.85rem; background-color: #f59e0b !important; border-radius: 50% !important;">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}{{ count(explode(' ', Auth::user()->name)) > 1 ? strtoupper(substr(explode(' ', Auth::user()->name)[1], 0, 1)) : '' }}
                        </div>
                        <img src="{{ Auth::user()->profile_image ? asset(Auth::user()->profile_image) : '' }}" class="w-100 h-100 img-avatar {{ Auth::user()->profile_image ? '' : 'd-none' }}" style="object-fit: cover; border-radius: 50% !important;">
                    </div>
                    <div class="overflow-hidden text-start">
                        <p class="mb-0 text-white text-truncate fw-bold" style="font-size: 0.85rem; line-height: 1.2;">{{ Auth::user()->name }}</p>
                    </div>
                </div>
                <div class="text-white-50" style="flex-shrink: 0; display: flex; align-items: center;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Popover Menu -->
    <div id="profilePopover" class="profile-popover">
        <!-- Header (User Info) -->
        <div class="popover-item" style="cursor: default; pointer-events: none;">
            <div class="popover-item-left">
                <div class="avatar-container" style="width: 32px; height: 32px; min-width: 32px; min-height: 32px; border-radius: 50% !important; overflow: hidden; flex-shrink: 0;">
                    <div class="bg-warning text-dark w-100 h-100 d-flex align-items-center justify-content-center fw-bold initials-avatar {{ Auth::user()->profile_image ? 'd-none' : '' }}" style="font-size: 0.8rem; background-color: #f59e0b !important; border-radius: 50% !important;">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}{{ count(explode(' ', Auth::user()->name)) > 1 ? strtoupper(substr(explode(' ', Auth::user()->name)[1], 0, 1)) : '' }}
                    </div>
                    <img src="{{ Auth::user()->profile_image ? asset(Auth::user()->profile_image) : '' }}" class="w-100 h-100 img-avatar {{ Auth::user()->profile_image ? '' : 'd-none' }}" style="object-fit: cover; border-radius: 50% !important;">
                </div>
                <div class="overflow-hidden text-start">
                    <p class="mb-0 text-white fw-bold text-truncate" style="font-size: 0.8rem; line-height: 1.2; max-width: 140px;">{{ Auth::user()->name }}</p>
                </div>
            </div>
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </div>
        
        <div class="popover-divider"></div>
        
        <!-- Profile -->
        <button class="popover-item" id="popoverProfile">
            <div class="popover-item-left">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"/></svg>
                <span>My Profile</span>
            </div>
        </button>

        <!-- Settings / API Keys -->
        <button class="popover-item" id="popoverSettings">
            <div class="popover-item-left">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                <span>API Credentials & Settings</span>
            </div>
        </button>

        <!-- Custom Instructions -->
        <button class="popover-item" id="popoverPersonalization">
            <div class="popover-item-left">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16M8 3v6M16 9v6M10 15v6"/></svg>
                <span>Custom Instructions</span>
            </div>
        </button>

        <!-- Prompt Library -->
        <a href="/prompts" class="popover-item" id="popoverPrompts">
            <div class="popover-item-left">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                <span>Prompt Templates Library</span>
            </div>
        </a>

        <!-- Upgrade Plan -->
        <button class="popover-item" id="popoverUpgrade">
            <div class="popover-item-left">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 3l1.912 5.886h6.188l-5.006 3.638 1.912 5.886-5.006-3.638-5.006 3.638 1.912-5.886-5.006-3.638h6.188z"/></svg>
                <span>Upgrade Plan</span>
            </div>
        </button>
        
        <!-- Help -->
        <button class="popover-item" id="popoverHelp">
            <div class="popover-item-left">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3M12 17h.01"/></svg>
                <span>Help & Support</span>
            </div>
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </button>
        
        <div class="popover-divider"></div>
        
        <!-- Log out -->
        <form action="/logout" method="POST" id="logoutForm">
            @csrf
            <button type="submit" class="popover-item text-danger" style="outline: none;">
                <div class="popover-item-left">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                    <span>Log out</span>
                </div>
            </button>
        </form>
    </div>

    <!-- Custom Premium Modals Overlay Backdrop -->
    <div id="aureonModalBackdrop" class="aureon-modal-backdrop">
        <!-- Upgrade Plan Modal -->
        <div id="upgradeModal" class="aureon-modal" style="display: none; max-width: 550px;">
            <div class="aureon-modal-header">
                <h5 class="aureon-modal-title">Upgrade your plan</h5>
                <button class="aureon-modal-close" onclick="closeaureonModal()">&times;</button>
            </div>
            <div class="aureon-modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="upgrade-card upgrade-card-free">
                            <div>
                                <h6 class="text-white fw-bold">Free</h6>
                                <p class="text-muted small">USD $0/month</p>
                                <ul class="list-unstyled small text-white-50 mt-3 mb-0" style="padding-left: 0; display: flex; flex-direction: column; gap: 8px;">
                                    <li>✓ Access to standard model</li>
                                    <li>✓ Regular response speed</li>
                                    <li>✓ Standard support</li>
                                </ul>
                            </div>
                            <button class="btn btn-outline-secondary btn-sm w-100 mt-4" disabled style="background-color: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.1);">Current plan</button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="upgrade-card upgrade-card-plus">
                            <div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="text-warning fw-bold mb-0">Plus</h6>
                                    <span class="badge bg-warning text-dark" style="font-size: 0.6rem; padding: 3px 6px;">POPULAR</span>
                                </div>
                                <p class="text-muted small">USD $20/month</p>
                                <ul class="list-unstyled small text-white-50 mt-3 mb-0" style="padding-left: 0; display: flex; flex-direction: column; gap: 8px;">
                                    <li>✓ Access to premium models</li>
                                    <li>✓ 5x faster response speed</li>
                                    <li>✓ Priority support & early access</li>
                                    <li>✓ Custom instructions enabled</li>
                                </ul>
                            </div>
                            <button class="btn btn-upgrade-premium btn-sm w-100 mt-4 py-2" onclick="window.showToast('Thank you for subscribing!', 'success'); closeaureonModal();">Upgrade to Plus</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Personalization Modal -->
        <div id="personalizationModal" class="aureon-modal" style="display: none;">
            <div class="aureon-modal-header">
                <h5 class="aureon-modal-title">Personalization</h5>
                <button class="aureon-modal-close" onclick="closeaureonModal()">&times;</button>
            </div>
            <div class="aureon-modal-body">
                <div class="mb-4">
                    <label class="modal-label" for="customInstructionsAbout">What would you like Aureon to know about you to provide better responses?</label>
                    <textarea class="modal-input modal-textarea" id="customInstructionsAbout" placeholder="Where are you based, what do you do for work, what are your hobbies and interests...">{{ Auth::user()->custom_instructions_about }}</textarea>
                </div>
                <div class="mb-4">
                    <label class="modal-label" for="customInstructionsRespond">How would you like Aureon to respond?</label>
                    <textarea class="modal-input modal-textarea" id="customInstructionsRespond" placeholder="Formal or casual, long or short, opinionated or neutral...">{{ Auth::user()->custom_instructions_respond }}</textarea>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="small text-muted">Enable for new chats</span>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="enableInstructionsSwitch" {{ Auth::user()->custom_instructions_enabled ? 'checked' : '' }}>
                    </div>
                </div>
            </div>
            <div class="aureon-modal-footer">
                <button class="btn-modal-cancel" onclick="closeaureonModal()">Cancel</button>
                <button class="btn btn-primary btn-sm btn-premium px-4 rounded-pill" onclick="savePersonalization()">Save</button>
            </div>
        </div>

        <!-- Profile Modal -->
        <div id="profileModal" class="aureon-modal" style="display: none; max-width: 450px;">
            <div class="aureon-modal-header">
                <h5 class="aureon-modal-title">My Profile</h5>
                <button class="aureon-modal-close" onclick="closeaureonModal()">&times;</button>
            </div>
            <div class="aureon-modal-body">
                <div class="text-center mb-4">
                    <div class="avatar-wrapper position-relative mx-auto mb-3" style="width: 80px; height: 80px;">
                        <div class="avatar-container mx-auto" style="width: 80px; height: 80px; border: 2px solid rgba(255,255,255,0.15);">
                            <div class="bg-warning text-dark w-100 h-100 d-flex align-items-center justify-content-center fw-bold initials-avatar {{ Auth::user()->profile_image ? 'd-none' : '' }}" style="font-size: 2rem; background-color: #f59e0b !important;">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}{{ count(explode(' ', Auth::user()->name)) > 1 ? strtoupper(substr(explode(' ', Auth::user()->name)[1], 0, 1)) : '' }}
                            </div>
                            <img src="{{ Auth::user()->profile_image ? asset(Auth::user()->profile_image) : '' }}" class="w-100 h-100 img-avatar {{ Auth::user()->profile_image ? '' : 'd-none' }}" style="object-fit: cover; border-radius: 50%;">
                        </div>
                        <!-- Hover Edit Overlay -->
                        <div class="avatar-edit-overlay" onclick="document.getElementById('profileImageFileInput').click();" title="Click to upload profile image">
                            <svg width="20" height="20" fill="none" stroke="white" stroke-width="2.5" viewBox="0 0 24 24"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                        </div>
                    </div>
                    <input type="file" id="profileImageFileInput" accept="image/*" style="display: none;">
                    <h6 class="text-white fw-bold mb-0" id="profileNameHeader">{{ Auth::user()->name }}</h6>
                </div>
                
                <div class="mb-3">
                    <label class="modal-label" for="profileNameInput">Display Name</label>
                    <input type="text" class="modal-input" id="profileNameInput" value="{{ Auth::user()->name }}">
                </div>
                
                <div class="mb-3">
                    <label class="modal-label">Email Address</label>
                    <input type="email" class="modal-input" value="{{ Auth::user()->email }}" readonly disabled style="opacity: 0.6;">
                </div>
                
                <div class="d-flex justify-content-between align-items-center pt-3 mt-3">
                    <span class="small">Account Created On</span>
                    <span class="small text-white-50">{{ Auth::user()->created_at ? \Carbon\Carbon::parse(Auth::user()->created_at)->format('M d, Y') : 'N/A' }}</span>
                </div>
            </div>
            <div class="aureon-modal-footer">
                <button class="btn-modal-cancel" onclick="closeaureonModal()">Close</button>
                <button class="btn btn-primary btn-sm btn-premium px-4 rounded-pill" onclick="saveProfileName()">Save changes</button>
            </div>
        </div>

        <!-- Settings Modal -->
        <div id="settingsModal" class="aureon-modal" style="display: none; max-width: 700px;">
            <div class="aureon-modal-header">
                <h5 class="aureon-modal-title">Settings</h5>
                <button class="aureon-modal-close" onclick="closeaureonModal()">&times;</button>
            </div>
            <div class="aureon-modal-body" style="padding: 0;">
                <div class="settings-layout">
                    <!-- Settings Sidebar -->
                    <div class="settings-sidebar p-3">
                        <button class="settings-tab-btn active" onclick="switchSettingsTab(event, 'settingsGeneral')">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                            <span>General</span>
                        </button>
                        <button class="settings-tab-btn" onclick="switchSettingsTab(event, 'settingsData')">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 7v10c0 2.21 3.58 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.58 4 8 4s8-1.79 8-4M4 7c0-2.21 3.58-4 8-4s8 1.79 8 4M4 12c0 2.21 3.58 4 8 4s8-1.79 8-4"/></svg>
                            <span>Data Controls</span>
                        </button>
                        <button class="settings-tab-btn" onclick="switchSettingsTab(event, 'settingsSecurity')">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            <span>Security</span>
                        </button>
                        <button class="settings-tab-btn" onclick="switchSettingsTab(event, 'settingsApiKeys')">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
                            <span>API Credentials</span>
                        </button>
                    </div>
                    
                    <!-- Settings Panels -->
                    <div class="settings-content p-4" style="background-color: #171717;">
                        <!-- General Panel -->
                        <div id="settingsGeneral" class="settings-pane active">
                            <h6 class="text-white fw-bold mb-4">General Settings</h6>
                            
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <p class="mb-0 text-white" style="font-size: 0.9rem;">Theme</p>
                                    <p class="mb-0 text-muted small">Choose how Aureon looks on your device.</p>
                                </div>
                                <select class="modal-input w-fit-content" style="min-width: 120px;">
                                    <option value="dark" selected>Dark</option>
                                    <option value="light">Light</option>
                                    <option value="system">System</option>
                                </select>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <p class="mb-0 text-white" style="font-size: 0.9rem;">Locale (Language)</p>
                                    <p class="mb-0 text-muted small">Choose your preferred language.</p>
                                </div>
                                <select class="modal-input w-fit-content" style="min-width: 120px;">
                                    <option value="en" selected>English</option>
                                    <option value="es">Español</option>
                                    <option value="fr">Français</option>
                                    <option value="de">Deutsch</option>
                                </select>
                            </div>
                            
                            <div class="border-top border-secondary pt-3 mt-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <p class="mb-0 text-white" style="font-size: 0.9rem;">Archive all chats</p>
                                        <p class="mb-0 text-muted small">Archive all conversations in sidebar.</p>
                                    </div>
                                    <button class="btn btn-outline-light btn-sm px-3" style="font-size: 0.8rem; border-color: rgba(255,255,255,0.15);" onclick="window.showToast('Chats archived successfully!', 'success')">Archive all</button>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="mb-0 text-white" style="font-size: 0.9rem;">Delete all chats</p>
                                        <p class="mb-0 text-muted small">Permanently delete all conversations.</p>
                                    </div>
                                    <button class="btn btn-danger btn-sm px-3" style="font-size: 0.8rem; background-color: #dc2626;" onclick="if(confirm('Are you sure you want to delete all chats? This cannot be undone.')) window.showToast('All chats deleted.', 'success')">Delete all</button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Data Controls Panel -->
                        <div id="settingsData" class="settings-pane">
                            <h6 class="text-white fw-bold mb-4">Data Controls</h6>
                            
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <p class="mb-0 text-white" style="font-size: 0.9rem;">Chat history & training</p>
                                    <p class="mb-0 text-muted small" style="max-width: 320px;">Save new chats to this browser and allow them to improve our models.</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" checked>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <p class="mb-0 text-white" style="font-size: 0.9rem;">Export data</p>
                                    <p class="mb-0 text-muted small">Request a downloadable export of your data.</p>
                                </div>
                                <button class="btn btn-outline-light btn-sm px-3" style="font-size: 0.8rem; border-color: rgba(255,255,255,0.15);" onclick="window.showToast('Export started! You will receive an email link shortly.', 'success')">Export</button>
                            </div>
                            
                            <div class="border-top border-secondary pt-3 mt-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="mb-0 text-danger" style="font-size: 0.9rem; font-weight: 500;">Delete account</p>
                                        <p class="mb-0 text-muted small">Permanently delete your account and data.</p>
                                    </div>
                                    <button class="btn btn-danger btn-sm px-3" style="font-size: 0.8rem; background-color: #dc2626;" onclick="if(confirm('Are you sure you want to permanently delete your account? This is irreversible.')) window.showToast('Account deletion requested.', 'success')">Delete account</button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Security Panel -->
                        <div id="settingsSecurity" class="settings-pane">
                            <h6 class="text-white fw-bold mb-4">Security Settings</h6>
                            
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <p class="mb-0 text-white" style="font-size: 0.9rem;">Multi-factor authentication</p>
                                    <p class="mb-0 text-muted small">Add an extra layer of security to your account.</p>
                                </div>
                                <button class="btn btn-outline-light btn-sm px-3" style="font-size: 0.8rem; border-color: rgba(255,255,255,0.15);" onclick="window.showToast('MFA set-up flow initiated!', 'success')">Enable</button>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="mb-0 text-white" style="font-size: 0.9rem;">Log out of all devices</p>
                                    <p class="mb-0 text-muted small">Sign out from active sessions on other devices.</p>
                                </div>
                                <button class="btn btn-outline-light btn-sm px-3" style="font-size: 0.8rem; border-color: rgba(255,255,255,0.15);" onclick="window.showToast('Signed out from all other devices!', 'success')">Log out all</button>
                            </div>
                        </div>

                        <!-- API Credentials Panel -->
                        <div id="settingsApiKeys" class="settings-pane">
                            <h6 class="text-white fw-bold mb-1">Database AI Credentials</h6>
                            <p class="text-muted small mb-3">Manage API keys stored directly in your database. Keys saved here dynamically override server environment variables.</p>
                            
                            <div class="mb-3">
                                <label class="modal-label" style="font-size: 0.8rem;">Google Gemini API Key</label>
                                <input type="password" class="modal-input" id="dbGeminiKey" value="{{ Auth::user()->gemini_api_key ?: \App\Models\Setting::get('gemini_api_key') }}" placeholder="AQ.Ab8RN6...">
                            </div>
                            <div class="mb-3">
                                <label class="modal-label" style="font-size: 0.8rem;">OpenAI API Key</label>
                                <input type="password" class="modal-input" id="dbOpenAiKey" value="{{ Auth::user()->openai_api_key ?: \App\Models\Setting::get('openai_api_key') }}" placeholder="sk-proj-...">
                            </div>
                            <div class="mb-3">
                                <label class="modal-label" style="font-size: 0.8rem;">Groq API Key</label>
                                <input type="password" class="modal-input" id="dbGroqKey" value="{{ Auth::user()->groq_api_key ?: \App\Models\Setting::get('groq_api_key') }}" placeholder="gsk_...">
                            </div>
                            <div class="mb-3">
                                <label class="modal-label" style="font-size: 0.8rem;">DeepSeek API Key</label>
                                <input type="password" class="modal-input" id="dbDeepSeekKey" value="{{ Auth::user()->deepseek_api_key ?: \App\Models\Setting::get('deepseek_api_key') }}" placeholder="sk-82a2...">
                            </div>
                            <div class="mb-3">
                                <label class="modal-label" style="font-size: 0.8rem;">Anthropic API Key</label>
                                <input type="password" class="modal-input" id="dbAnthropicKey" value="{{ Auth::user()->anthropic_api_key ?: \App\Models\Setting::get('anthropic_api_key') }}" placeholder="sk-ant-...">
                            </div>
                            <div class="text-end mt-4">
                                <button type="button" class="btn btn-primary btn-sm btn-premium px-4 rounded-pill" onclick="saveDatabaseApiKeys()">Save Credentials to DB</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="aureon-modal-footer">
                <button class="btn btn-primary btn-sm btn-premium px-4" onclick="closeaureonModal()">Done</button>
            </div>
        </div>

        <!-- Help Modal -->
        <div id="helpModal" class="aureon-modal" style="display: none; max-width: 500px;">
            <div class="aureon-modal-header">
                <h5 class="aureon-modal-title">Help & Support</h5>
                <button class="aureon-modal-close" onclick="closeaureonModal()">&times;</button>
            </div>
            <div class="aureon-modal-body">
                <h6 class="text-white fw-bold mb-3">Frequently Asked Questions</h6>
                <div class="accordion accordion-flush mb-4" id="helpAccordion" style="--bs-accordion-bg: transparent; --bs-accordion-color: #ececec; --bs-accordion-btn-color: white; --bs-accordion-active-color: #f59e0b; --bs-accordion-active-bg: transparent; --bs-accordion-border-color: rgba(255,255,255,0.08);">
                    <div class="accordion-item border-0">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed px-0 py-2 fs-6 fw-semibold text-white" style="box-shadow: none; background: transparent;" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                What is Aureon?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                            <div class="accordion-body px-0 text-white-50 small">
                                Aureon is an advanced agentic coding and personal assistance chatbot framework built using Laravel and modern web design paradigms.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item border-0 d-none">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed px-0 py-2 fs-6 fw-semibold text-white" style="box-shadow: none; background: transparent;" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                How do I export my data?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                            <div class="accordion-body px-0 text-white-50 small">
                                Go to Settings -> Data Controls -> Export Data to request a full copy of your conversation logs and account data.
                            </div>
                        </div>
                    </div>
                </div>
                
                <h6 class="text-white fw-bold mb-2">Keyboard Shortcuts</h6>
                <div class="d-flex flex-column gap-2 mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small">New Chat Thread</span>
                        <kbd class="bg-secondary text-white font-monospace" style="font-size: 0.75rem; padding: 2px 6px; border-radius: 4px;">Ctrl + Shift + O</kbd>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small">Submit Prompt</span>
                        <kbd class="bg-secondary text-white font-monospace" style="font-size: 0.75rem; padding: 2px 6px; border-radius: 4px;">Enter</kbd>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small">Insert New Line</span>
                        <kbd class="bg-secondary text-white font-monospace" style="font-size: 0.75rem; padding: 2px 6px; border-radius: 4px;">Shift + Enter</kbd>
                    </div>
                </div>
            </div>
            <div class="aureon-modal-footer">
                <a href="mailto:support@aureon.ai" class="btn btn-outline-light btn-sm me-auto" style="border-color: rgba(255,255,255,0.15);">Email Support</a>
                <button class="btn btn-primary btn-sm btn-premium px-4" onclick="closeaureonModal()">Done</button>
            </div>
        </div>
        
        <div id="deleteModal" class="aureon-modal" style="display: none; max-width: 400px; border-radius: 12px; background-color: #212121; overflow: hidden;">
            <div class="aureon-modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="aureon-modal-title" style="font-size: 1.15rem; font-weight: 600;">Delete chat?</h5>
            </div>
            <div class="aureon-modal-body px-4 pt-3 pb-4">
                <p class="mb-2 text-white" style="font-size: 0.95rem;">This will delete <strong id="deleteModalChatName"></strong>.</p>
                <p class="text-white-50 small mb-0">This action cannot be undone.</p>
            </div>
            <div class="aureon-modal-footer pb-3 px-4 d-flex justify-content-end" style="background-color: transparent; border-top: none;">
                <button class="btn btn-sm px-3 rounded-pill border-0 text-white-50" style="background: transparent;" onclick="closeaureonModal()" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,0.5)'">Cancel</button>
                <button class="btn btn-danger btn-sm px-4 ms-2 rounded-pill" onclick="confirmDelete()" style="background-color: #f43f5e; border-color: #f43f5e; font-weight: 500;">Delete</button>
            </div>
        </div>

    </div>
    @endauth

    <main class="main-content" style="{{ Auth::check() ? '' : 'margin-left: 0; width: 100%;' }}">
        @auth
        <!-- Mobile Header -->
        <div class="mobile-header d-lg-none d-flex justify-content-between align-items-center p-3 position-absolute w-100" style="z-index: 50; top: 0; background: linear-gradient(rgba(13,13,13,0.9) 50%, transparent);">
            <button class="btn btn-icon d-lg-none" onclick="toggleSidebar()" style="color: var(--text-main); background: transparent; border: none; padding: 4px;">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
            </button>
            <h5 class="mb-0 text-white fw-bold" style="font-size: 1.1rem;">Aureon</h5>
            <a href="/" class="btn btn-icon" style="color: var(--text-main); background: transparent; border: none; padding: 4px;">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
            </a>
        </div>
        <!-- Mobile Sidebar Overlay -->
        <div id="sidebarOverlay" class="d-none d-lg-none" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(2px); z-index: 999;" onclick="toggleSidebar()"></div>
        @endauth

        @yield('content')
    </main>

@yield('scripts')

    <script>
        let activeHistoryId = null;
        let activeHistoryTitle = "";

        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            if(sidebar) sidebar.classList.toggle('show');
            if(overlay) {
                if(sidebar.classList.contains('show')) overlay.classList.remove('d-none');
                else overlay.classList.add('d-none');
            }
        }

        // Auto-close sidebar on mobile navigation
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.sidebar .nav-link, .sidebar .nav-sub-link').forEach(link => {
                link.addEventListener('click', () => {
                    if (window.innerWidth <= 992) {
                        const sidebar = document.querySelector('.sidebar');
                        const overlay = document.getElementById('sidebarOverlay');
                        if (sidebar) sidebar.classList.remove('show');
                        if (overlay) overlay.classList.add('d-none');
                    }
                });
            });
        });

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
            
            document.getElementById('history-link-' + activeHistoryId).style.display = 'none';
            document.getElementById('history-btn-' + activeHistoryId).style.display = 'none';
            
            var container = document.getElementById('inline-rename-' + activeHistoryId);
            var input = document.getElementById('inline-rename-input-' + activeHistoryId);
            
            container.style.display = 'block';
            input.focus();
            input.select();
        }

        function cancelInlineRename(id) {
            document.getElementById('history-link-' + id).style.display = '';
            document.getElementById('history-btn-' + id).style.display = '';
            document.getElementById('inline-rename-' + id).style.display = 'none';
        }

        function handleInlineRenameKeydown(event, id) {
            if (event.key === 'Enter') {
                event.preventDefault();
                var newTitle = document.getElementById('inline-rename-input-' + id).value.trim();
                var oldTitle = document.getElementById('history-link-' + id).getAttribute('title');
                
                if (newTitle && newTitle !== oldTitle) {
                    var form = document.getElementById('renameForm');
                    form.action = '/history/' + id;
                    document.getElementById('renameInput').value = newTitle;
                    form.submit();
                } else {
                    cancelInlineRename(id);
                }
            } else if (event.key === 'Escape') {
                cancelInlineRename(id);
            }
        }

        function handleDelete() {
            document.getElementById('deleteModalChatName').textContent = activeHistoryTitle;
            showaureonModal('deleteModal');
            closeHistoryMenu();
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

        // Profile Popover and Custom Modals interactive logic
        const profileTrigger = document.getElementById('profileTrigger');
        const profilePopover = document.getElementById('profilePopover');
        const aureonModalBackdrop = document.getElementById('aureonModalBackdrop');
        const aureonModals = ['upgradeModal', 'personalizationModal', 'profileModal', 'settingsModal', 'helpModal', 'deleteModal'];

        if (profileTrigger) {
            profileTrigger.addEventListener('click', function(e) {
                e.stopPropagation();
                const isVisible = profilePopover.style.display === 'block';
                profilePopover.style.display = isVisible ? 'none' : 'block';
            });
        }

        document.addEventListener('click', function(e) {
            if (profilePopover && !profilePopover.contains(e.target) && e.target !== profileTrigger && !profileTrigger.contains(e.target)) {
                profilePopover.style.display = 'none';
            }
        });

        function showaureonModal(modalId) {
            if (profilePopover) profilePopover.style.display = 'none';
            if (aureonModalBackdrop) {
                aureonModalBackdrop.style.display = 'flex';
                aureonModals.forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.style.display = (id === modalId) ? 'block' : 'none';
                });
            }
        }

        function closeaureonModal() {
            if (aureonModalBackdrop) {
                aureonModalBackdrop.style.display = 'none';
            }
        }

        if (aureonModalBackdrop) {
            aureonModalBackdrop.addEventListener('click', function(e) {
                if (e.target === aureonModalBackdrop) {
                    closeaureonModal();
                }
            });
        }

        document.getElementById('popoverUpgrade')?.addEventListener('click', () => showaureonModal('upgradeModal'));
        document.getElementById('popoverPersonalization')?.addEventListener('click', () => showaureonModal('personalizationModal'));
        document.getElementById('popoverProfile')?.addEventListener('click', () => showaureonModal('profileModal'));
        document.getElementById('popoverSettings')?.addEventListener('click', () => showaureonModal('settingsModal'));
        document.getElementById('popoverHelp')?.addEventListener('click', () => showaureonModal('helpModal'));

        function switchSettingsTab(event, paneId) {
            const tabs = document.querySelectorAll('.settings-tab-btn');
            const panes = document.querySelectorAll('.settings-pane');
            
            tabs.forEach(tab => tab.classList.remove('active'));
            panes.forEach(pane => pane.classList.remove('active'));
            
            event.currentTarget.classList.add('active');
            const targetPane = document.getElementById(paneId);
            if (targetPane) targetPane.classList.add('active');
        }

        function savePersonalization() {
            window.showToast('Personalization instructions saved successfully!', 'success');
            closeaureonModal();
        }

        function saveProfileName() {
            const newName = document.getElementById('profileNameInput').value.trim();
            if (newName) {
                // Update UI text dynamically
                const profileNameEls = document.querySelectorAll('#profileTrigger p.text-white, #profilePopover p.text-white, #profileNameHeader');
                profileNameEls.forEach(el => el.textContent = newName);
                
                // Update initial circles dynamically
                const initials = newName.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
                const initialCircleEls = document.querySelectorAll('.initials-avatar');
                initialCircleEls.forEach(el => el.textContent = initials);

                window.showToast('Profile details updated successfully!', 'success');
                closeaureonModal();
            }
        }

        // Profile Image upload & preview logic
        const profileImageFileInput = document.getElementById('profileImageFileInput');

        function updateAvatarImages(imageSrc) {
            const containers = document.querySelectorAll('.avatar-container');
            containers.forEach(container => {
                const initials = container.querySelector('.initials-avatar');
                const img = container.querySelector('.img-avatar');
                if (img) {
                    img.src = imageSrc;
                    img.classList.remove('d-none');
                }
                if (initials) {
                    initials.classList.add('d-none');
                }
            });
        }

        if (profileImageFileInput) {
            profileImageFileInput.addEventListener('change', async function(e) {
                const file = e.target.files[0];
                if (file) {
                    if (file.size > 2 * 1024 * 1024) {
                        window.showToast('Image is too large. Please select an image under 2MB.', 'error');
                        return;
                    }
                    
                    const formData = new FormData();
                    formData.append('profile_image', file);

                    try {
                        const response = await fetch('/profile/upload-image', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: formData
                        });

                        const result = await response.json();

                        if (result.status) {
                            updateAvatarImages(result.url);
                            window.showToast('Profile image updated successfully!', 'success');
                        } else {
                            window.showToast(result.message || 'Failed to upload profile image.', 'error');
                        }
                    } catch (error) {
                        console.error('Error uploading profile image:', error);
                        window.showToast('An error occurred during file upload. Please try again.', 'error');
                    }
                }
            });
        }

        // Global Toast System
        window.showToast = function(message, type = 'success', duration = 4000) {
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
            }, duration);

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

        // Enhanced Code Blocks
        window.enhanceCodeBlocks = function(container = document) {
            container.querySelectorAll('pre').forEach(pre => {
                if (pre.classList.contains('enhanced')) return;
                pre.classList.add('enhanced');
                
                const code = pre.querySelector('code');
                let language = 'text';
                if (code) {
                    const match = code.className.match(/language-(\w+)/);
                    if (match) language = match[1];
                }
                
                const wrapper = document.createElement('div');
                wrapper.className = 'code-block-wrapper';
                
                const header = document.createElement('div');
                header.className = 'code-block-header';
                
                header.innerHTML = `
                    <div class="code-block-lang">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 18l6-6-6-6M8 6l-6 6 6 6"></path></svg>
                        <span>${language}</span>
                    </div>
                    <button class="code-block-copy-btn" title="Copy code">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                    </button>
                `;
                
                pre.parentNode.insertBefore(wrapper, pre);
                wrapper.appendChild(header);
                wrapper.appendChild(pre);
                
                const copyBtn = header.querySelector('.code-block-copy-btn');
                copyBtn.addEventListener('click', () => {
                    const textToCopy = code ? code.innerText : pre.innerText;
                    navigator.clipboard.writeText(textToCopy).then(() => {
                        const originalHtml = copyBtn.innerHTML;
                        copyBtn.innerHTML = `<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>`;
                        setTimeout(() => {
                            copyBtn.innerHTML = originalHtml;
                        }, 2000);
                    });
                });
            });
        };
        
        document.addEventListener('DOMContentLoaded', () => {
            window.enhanceCodeBlocks();
        });

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
        // Save Database API Keys via AJAX
        window.saveDatabaseApiKeys = function() {
            const payload = {
                gemini_api_key: document.getElementById('dbGeminiKey')?.value || '',
                openai_api_key: document.getElementById('dbOpenAiKey')?.value || '',
                groq_api_key: document.getElementById('dbGroqKey')?.value || '',
                deepseek_api_key: document.getElementById('dbDeepSeekKey')?.value || '',
                anthropic_api_key: document.getElementById('dbAnthropicKey')?.value || '',
            };

            fetch('/profile/update-api-keys', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(payload)
            })
            .then(r => r.json())
            .then(data => {
                if(data.status) {
                    window.showToast(data.message, 'success');
                } else {
                    window.showToast(data.message || 'Failed to save API credentials.', 'error');
                }
            })
            .catch(err => {
                window.showToast('Error saving credentials to database.', 'error');
            });
        };
    </script>
</body>
</html>
