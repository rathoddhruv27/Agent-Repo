@extends('layouts.app')

@section('title', 'Agent - Aureon')

@section('content')
<div class="chat-wrapper" style="position: relative;">
    <!-- Premium Categorized Model Selector -->
    <div class="custom-model-dropdown" style="position: absolute; top: 16px; left: 24px; z-index: 10;">
        <button id="modelDropdownBtn" class="model-dropdown-btn">
            <span id="selectedModelText">✨ Auto (Smart Fallback)</span>
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
        </button>
        <div id="modelDropdownMenu" class="model-dropdown-menu d-none animate-in">
            <!-- Auto Option -->
            <div class="model-option active" data-value="auto">
                <div class="model-name">✨ Auto (Smart Fallback)</div>
                <div class="model-desc">Dynamically routes to the best available model</div>
            </div>

            @foreach($categorizedModels as $category)
            <div class="model-category-header" style="border-left: 3px solid {{ $category['color'] }};">
                <span class="category-icon">{{ $category['icon'] }}</span>
                <span class="category-name">{{ $category['category'] }}</span>
            </div>
            @foreach($category['models'] as $m)
            <div class="model-option locked" 
                 data-value="{{ $m['name'] }}:{{ $m['model'] }}" 
                 data-provider="{{ $m['name'] }}" 
                 data-model="{{ $m['model'] }}"
                 data-label="{{ $m['label'] }}"
                 style="opacity: 0.5; cursor: not-allowed; padding-left: 24px;" 
                 title="Locked: Auto mode enforced">
                <div class="d-flex justify-content-between align-items-center w-100">
                    <div>
                        <div class="model-name" style="font-size: 0.9rem;">{{ $m['label'] }}</div>
                        <div class="model-desc" style="font-size: 0.72rem;">{{ $m['desc'] }}</div>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        @if(!empty($m['coming_soon']))
                            <span style="font-size: 0.6rem; background: rgba(239,68,68,0.2); color: #f87171; padding: 2px 6px; border-radius: 4px; font-weight: 600;">SOON</span>
                        @endif
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    </div>
                </div>
            </div>
            @endforeach
            @endforeach
        </div>
        <input type="hidden" id="modelSelection" value="auto">
    </div>
    <div id="chatResponseArea" class="chat-column" style="padding-top: 60px;">
        @if($messages->isEmpty())
        <div class="welcome-container animate-in">
            <h1 class="welcome-title text-white">Good to see you, {{ explode(' ', Auth::user()->name)[0] }}</h1>
        </div>
        @endif

        @foreach($messages as $msg)
        <div class="message-round user-message animate-in">
            <div class="message-content user-content">
                <div class="message-label text-end fs-5"></div>
                @if($msg->image_path)
                    <img src="{{ Storage::url($msg->image_path) }}" style="max-width: 320px; max-height: 320px; border-radius: 14px; margin-bottom: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); cursor: zoom-in; border: 1px solid rgba(255,255,255,0.05);" onclick="openFullscreenImage(this.src)">
                @endif
                <div class="message-text user-bubble">{{ $msg->prompt }}</div>
            </div>
        </div>

        <div class="message-round ai-message animate-in">
            <div class="message-content">
                <div class="message-label d-flex align-items-center gap-2 mb-1">
                    <img src="{{ asset('robo.png') }}" alt="Aureon" width="24" height="24" style="border-radius: 4px;">
                    <span style="font-weight: 600; font-size: 0.95rem;">Aureon</span>
                </div>
                <div class="markdown-rendered message-text" data-raw-content="{{ $msg->response ?? '' }}"></div>
                
                <div class="message-meta mt-3 d-flex gap-3 align-items-center">
                    <button class="btn-icon copy-btn-individual" title="Copy" style="color: #b4b4b4; transition: color 0.2s; padding: 0;" onmouseover="this.style.color='white'" onmouseout="this.style.color='#b4b4b4'">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                    </button>
                    @if($msg->agent && $msg->model)
                        <span class="small-badge" style="display: flex; align-items: center; gap: 4px; color: #b4b4b4; opacity: 0.8;">
                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            {{ ucfirst($msg->agent) }} ({{ $msg->model }})
                        </span>
                    @endif
                </div>
            </div>
        </div>
        @endforeach

        <div id="loading" class="d-none message-round ai-message">
            <div class="message-content">
                <div class="message-label d-flex align-items-center gap-2 mb-1">
                    <img src="{{ asset('robo.png') }}" alt="Aureon" width="24" height="24" style="border-radius: 4px;">
                    <span style="font-weight: 600; font-size: 0.95rem;">Aureon</span>
                </div>
                <!-- Default text loading -->
                <div id="loadingText" class="d-flex gap-1 mt-2">
                    <div class="dot"></div>
                    <div class="dot" style="animation-delay: 0.2s"></div>
                    <div class="dot" style="animation-delay: 0.4s"></div>
                </div>
                <!-- Image generation skeleton loading -->
                <div id="loadingImage" class="d-none mt-2" style="background: #2a2a2a; border-radius: 12px; width: 300px; height: 300px; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden;">
                    <div style="position: absolute; top: 16px; left: 16px; color: #a3a3a3; font-size: 0.9rem; font-weight: 500; display: flex; align-items: center; gap: 8px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="animate-spin"><path d="M21 12a9 9 0 1 1-6.219-8.56"></path></svg>
                        Creating image...
                    </div>
                    <!-- Skeleton Grid Background -->
                    <div style="width: 100%; height: 100%; opacity: 0.1; background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 20px 20px;"></div>
                </div>
            </div>
        </div>


    </div>

    <div class="chat-footer-wrapper">
        <div class="chat-footer-container">
            @if($messages->isEmpty())
            @php
                $allSuggestions = [
                    // Laravel & PHP Core
                    ['prompt' => 'Write a Laravel controller for products', 'label' => 'Laravel Product Controller'],
                    ['prompt' => 'How do I optimize database queries in Laravel?', 'label' => 'Optimize Laravel Queries'],
                    ['prompt' => 'Explain Eloquent relationships with code examples', 'label' => 'Eloquent Relationships'],
                    ['prompt' => 'What are Laravel Service Providers and how do they work?', 'label' => 'Laravel Service Providers'],
                    ['prompt' => 'How do I secure a REST API in Laravel?', 'label' => 'Laravel API Security'],
                    ['prompt' => 'How do I set up cron jobs and task scheduling in Laravel?', 'label' => 'Laravel Cron Jobs'],
                    ['prompt' => 'Explain Laravel Middleware with custom examples', 'label' => 'Laravel Middleware'],
                    ['prompt' => 'How do I implement cursor pagination in Laravel?', 'label' => 'Cursor Pagination'],
                    ['prompt' => 'How to implement JWT authentication in Laravel?', 'label' => 'JWT Auth in Laravel'],
                    ['prompt' => 'How do I configure Redis queue worker in Laravel?', 'label' => 'Laravel Redis Queues'],
                    ['prompt' => 'What are the new features in PHP 8.3?', 'label' => 'PHP 8.3 Features'],
                    ['prompt' => 'What is a polymorphic relationship in Laravel?', 'label' => 'Polymorphic Relations'],
                    ['prompt' => 'How to write unit tests using PHPUnit in Laravel?', 'label' => 'PHPUnit Testing'],
                    ['prompt' => 'How do I create a custom Artisan command in Laravel?', 'label' => 'Custom Artisan Command'],
                    ['prompt' => 'How do I handle file uploads and S3 storage in Laravel?', 'label' => 'Laravel S3 Uploads'],
                    ['prompt' => 'Explain Laravel Event Listeners and Observers', 'label' => 'Laravel Observers'],
                    ['prompt' => 'How do I use Laravel Sanctum for API tokens?', 'label' => 'Laravel Sanctum Auth'],
                    ['prompt' => 'Explain Laravel Form Requests validation rules', 'label' => 'Laravel Form Validation'],
                    ['prompt' => 'How to cache API responses using Redis in Laravel?', 'label' => 'Laravel Redis Cache'],
                    ['prompt' => 'How to handle database transactions in Laravel?', 'label' => 'Database Transactions'],

                    // Frontend & UI/UX Design
                    ['prompt' => 'Generate a modern glassmorphism CSS button', 'label' => 'Glassmorphism CSS Button'],
                    ['prompt' => 'Create a beautiful pricing table in CSS with hover effects', 'label' => 'Pricing Table CSS'],
                    ['prompt' => 'Write a Vue 3 component for a responsive modal dialog', 'label' => 'Vue 3 Modal Component'],
                    ['prompt' => 'Explain React hooks like useState, useEffect, and useMemo', 'label' => 'React Hooks Guide'],
                    ['prompt' => 'Create an aesthetic dark mode toggle in Vanilla JS', 'label' => 'Dark Mode Toggle JS'],
                    ['prompt' => 'Generate a responsive CSS Grid dashboard layout', 'label' => 'CSS Grid Dashboard'],
                    ['prompt' => 'Create a sliding image carousel in Vanilla JS', 'label' => 'JS Image Carousel'],
                    ['prompt' => 'How to use Tailwind CSS v3 in a Laravel Blade template?', 'label' => 'Tailwind in Laravel'],
                    ['prompt' => 'Create an animated loading spinner in pure CSS', 'label' => 'CSS Animated Spinner'],
                    ['prompt' => 'Explain JavaScript Promises and Async/Await', 'label' => 'JS Async/Await'],
                    ['prompt' => 'Create a custom toast notification library in JavaScript', 'label' => 'JS Toast Library'],
                    ['prompt' => 'Write a custom hook in React for fetching API data', 'label' => 'React Custom Fetch Hook'],
                    ['prompt' => 'Create a drag and drop file uploader in Vue.js', 'label' => 'Vue Drag & Drop Upload'],
                    ['prompt' => 'Explain CSS Flexbox vs Grid with visual examples', 'label' => 'CSS Flexbox vs Grid'],
                    ['prompt' => 'Create an accordion component using Tailwind CSS and JS', 'label' => 'Tailwind Accordion'],
                    ['prompt' => 'How to build an infinite scroll feature in JavaScript?', 'label' => 'JS Infinite Scroll'],
                    ['prompt' => 'Create a customizable tooltip component in HTML/CSS', 'label' => 'Custom CSS Tooltip'],
                    ['prompt' => 'Explain virtual DOM in React and how it works', 'label' => 'Virtual DOM in React'],
                    ['prompt' => 'Build a responsive navigation sidebar with micro-animations', 'label' => 'Responsive Sidebar UI'],
                    ['prompt' => 'How to debounce user input in JavaScript for live search?', 'label' => 'JS Debounce Function'],

                    // Database & SQL
                    ['prompt' => 'How do I optimize MySQL slow queries with indexing?', 'label' => 'MySQL Query Indexing'],
                    ['prompt' => 'Write a complex SQL query to join four tables with GROUP BY', 'label' => 'Complex SQL Joins'],
                    ['prompt' => 'Explain database normalization up to 3NF', 'label' => 'Database Normalization'],
                    ['prompt' => 'How to prevent SQL injection in PHP raw queries?', 'label' => 'Prevent SQL Injection'],
                    ['prompt' => 'Explain MySQL vs PostgreSQL differences', 'label' => 'MySQL vs PostgreSQL'],
                    ['prompt' => 'Write a database migration for a multi-tenant application', 'label' => 'Multi-tenant Migration'],
                    ['prompt' => 'How to partition large database tables in MySQL?', 'label' => 'MySQL Table Partitioning'],
                    ['prompt' => 'Explain ACID properties in database management systems', 'label' => 'ACID Database Properties'],
                    ['prompt' => 'How do database triggers work in MySQL?', 'label' => 'MySQL Database Triggers'],
                    ['prompt' => 'Write a stored procedure in SQL to calculate monthly revenues', 'label' => 'SQL Stored Procedure'],
                    ['prompt' => 'How to handle N+1 query problems in Eloquent?', 'label' => 'Fix Eloquent N+1 Problem'],
                    ['prompt' => 'Explain full-text search indexing in PostgreSQL', 'label' => 'Postgres Full-Text Search'],
                    ['prompt' => 'How to back up and restore a MySQL database via CLI?', 'label' => 'MySQL CLI Backup'],
                    ['prompt' => 'Write a query to find duplicate records in a MySQL table', 'label' => 'Find Duplicate SQL Records'],
                    ['prompt' => 'Explain CTEs (Common Table Expressions) in SQL', 'label' => 'SQL Common Table Expressions'],

                    // DevOps, Security & Infrastructure
                    ['prompt' => 'What are the best practices for Dockerizing a Laravel app?', 'label' => 'Dockerize Laravel App'],
                    ['prompt' => 'How to configure a CI/CD pipeline in GitHub Actions for Laravel?', 'label' => 'GitHub Actions CI/CD'],
                    ['prompt' => 'What is CORS error and how do I fix it properly?', 'label' => 'Fix CORS Headers Error'],
                    ['prompt' => 'Generate a production-ready Nginx configuration for PHP-FPM', 'label' => 'Nginx Config PHP-FPM'],
                    ['prompt' => 'How to set up SSL certificates with Let\'s Encrypt and Certbot?', 'label' => 'Let\'s Encrypt SSL Setup'],
                    ['prompt' => 'How to prevent CSRF attacks in web applications?', 'label' => 'Prevent CSRF Attacks'],
                    ['prompt' => 'How to set up rate limiting for web endpoints in Laravel?', 'label' => 'API Rate Limiting'],
                    ['prompt' => 'Explain OAuth2 authorization code flow with PKCE', 'label' => 'OAuth2 PKCE Flow'],
                    ['prompt' => 'How to configure Redis cluster for session storage?', 'label' => 'Redis Session Storage'],
                    ['prompt' => 'Write a Docker Compose file for PHP, MySQL, and Nginx', 'label' => 'Docker Compose Stack'],
                    ['prompt' => 'How to secure environment variables in cloud deployments?', 'label' => 'Cloud Secrets Management'],
                    ['prompt' => 'Explain Kubernetes pods, services, and deployments', 'label' => 'Kubernetes Fundamentals'],
                    ['prompt' => 'How to monitor Laravel application performance using Prometheus?', 'label' => 'Laravel Monitoring'],
                    ['prompt' => 'Generate a standard .gitignore file for Laravel projects', 'label' => 'Laravel .gitignore'],
                    ['prompt' => 'How to set up SSH key authentication on Linux servers?', 'label' => 'Linux SSH Key Setup'],

                    // Python & Data Science
                    ['prompt' => 'Write a Python script to scrape a website using BeautifulSoup', 'label' => 'Python Web Scraper'],
                    ['prompt' => 'How to parse and filter JSON data using Python Pandas?', 'label' => 'Pandas Data Filtering'],
                    ['prompt' => 'Create a REST API with FastAPI and Pydantic in Python', 'label' => 'FastAPI REST Server'],
                    ['prompt' => 'Explain Python decorators with clear code examples', 'label' => 'Python Decorators'],
                    ['prompt' => 'How to build a simple machine learning model with scikit-learn?', 'label' => 'Scikit-Learn ML Model'],
                    ['prompt' => 'Write a Python script to send automated emails via SMTP', 'label' => 'Python SMTP Email'],
                    ['prompt' => 'Explain Python generators and yield statement', 'label' => 'Python Generators'],
                    ['prompt' => 'How to clean messy dataset using Pandas dataframe?', 'label' => 'Pandas Data Cleaning'],
                    ['prompt' => 'Write an asynchronous Python web server with asyncio', 'label' => 'Python Asyncio Server'],
                    ['prompt' => 'How to convert CSV to JSON format using Python script?', 'label' => 'Python CSV to JSON'],
                    ['prompt' => 'Explain list comprehensions vs map/filter in Python', 'label' => 'Python List Comprehension'],
                    ['prompt' => 'Write a Python script to resize images using PIL/Pillow', 'label' => 'Python Image Resizer'],
                    ['prompt' => 'How to connect Python to MySQL database using mysql-connector?', 'label' => 'Python MySQL Connector'],
                    ['prompt' => 'Explain multi-threading vs multi-processing in Python', 'label' => 'Python Multiprocessing'],
                    ['prompt' => 'Create a command line interface CLI tool using Click in Python', 'label' => 'Python CLI Tool'],

                    // Architecture & System Design
                    ['prompt' => 'Explain SOLID principles in object-oriented programming', 'label' => 'SOLID Architecture Principles'],
                    ['prompt' => 'Explain the MVC (Model-View-Controller) architecture pattern', 'label' => 'MVC Architecture Pattern'],
                    ['prompt' => 'Explain Dependency Injection and Inversion of Control', 'label' => 'Dependency Injection'],
                    ['prompt' => 'How to design a scalable microservices architecture?', 'label' => 'Microservices Architecture'],
                    ['prompt' => 'Explain Event-Driven Architecture with message queues', 'label' => 'Event-Driven Architecture'],
                    ['prompt' => 'What is Repository Pattern and when should I use it?', 'label' => 'Repository Pattern'],
                    ['prompt' => 'Explain Cache-Aside strategy vs Read-Through caching', 'label' => 'Caching Strategies'],
                    ['prompt' => 'How to handle database sharding in high-traffic applications?', 'label' => 'Database Sharding'],
                    ['prompt' => 'Explain Domain-Driven Design (DDD) concepts', 'label' => 'Domain-Driven Design'],
                    ['prompt' => 'What is CQRS (Command Query Responsibility Segregation)?', 'label' => 'CQRS Pattern'],

                    // AI & Prompt Engineering
                    ['prompt' => 'How to implement Retrieval-Augmented Generation (RAG) in Python?', 'label' => 'RAG System Implementation'],
                    ['prompt' => 'Explain vector embeddings and cosine similarity search', 'label' => 'Vector Embeddings & Search'],
                    ['prompt' => 'Write a system prompt for an expert code reviewer AI agent', 'label' => 'AI System Prompt Design'],
                    ['prompt' => 'How to fine-tune an open-source LLM like Llama 3?', 'label' => 'LLM Fine-Tuning Guide'],
                    ['prompt' => 'What is function calling in LLMs and how does it work?', 'label' => 'LLM Function Calling'],
                    ['prompt' => 'Explain the Transformer architecture in neural networks', 'label' => 'Transformer Neural Network'],
                    ['prompt' => 'How to connect Pinecone vector database with LangChain?', 'label' => 'LangChain & Pinecone'],
                    ['prompt' => 'Write a Python script to generate text embeddings using OpenAI API', 'label' => 'Generate AI Embeddings'],
                    ['prompt' => 'Explain agentic workflows and multi-agent coordination', 'label' => 'Agentic Workflows'],
                    ['prompt' => 'How to measure and reduce LLM hallucination in production?', 'label' => 'Reduce AI Hallucinations'],

                    // Web Basics & Utilities
                    ['prompt' => 'Write a regular expression to validate strong passwords', 'label' => 'Password Validation Regex'],
                    ['prompt' => 'Explain the difference between GET, POST, PUT, PATCH, and DELETE', 'label' => 'HTTP Verbs Explained'],
                    ['prompt' => 'Generate a basic semantic HTML5 boilerplate with meta tags', 'label' => 'HTML5 Semantic Boilerplate'],
                    ['prompt' => 'What is the difference between require and include in PHP?', 'label' => 'Require vs Include PHP'],
                    ['prompt' => 'Generate a secure random password using PHP random_bytes', 'label' => 'PHP Secure Password Gen'],
                    ['prompt' => 'Explain WebSocket vs HTTP Long-Polling for real-time apps', 'label' => 'WebSocket vs Long-Polling']
                ];
                
                shuffle($allSuggestions);
                $suggestions = array_slice($allSuggestions, 0, 5);
            @endphp
            <div class="suggestions-container" id="suggestionsContainer">
                @foreach($suggestions as $suggestion)
                    <button type="button" class="suggestion-btn" onclick="sendSuggestion('{{ addslashes($suggestion['prompt']) }}')">{{ $suggestion['label'] }}</button>
                @endforeach
            </div>
            @endif
            <form id="agentForm" class="input-pill-container" style="flex-direction: column; border-radius: 26px; padding: 6px 12px; align-items: stretch; gap: 4px; background: #2f2f2f; border: none; transition: all 0.3s ease;">
                <input type="hidden" id="conversation_id" value="{{ $currentConversationId }}">
                <input type="file" id="imageInput" accept="image/*" class="d-none" onchange="previewImage(event)">
                
                <div id="imagePreviewContainer" class="d-none" style="position: relative; width: max-content; margin-left: 12px; margin-top: 10px; margin-bottom: 2px;">
                    <img id="imagePreview" src="" alt="Preview" style="max-height: 80px; max-width: 180px; width: auto; height: auto; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1); object-fit: contain; box-shadow: 0 4px 6px rgba(0,0,0,0.1); cursor: pointer;" onclick="openFullscreenImage(this.src)">
                    <button type="button" class="btn-remove-image" onclick="removeImage()" style="position: absolute; top: -8px; right: -8px; background: #3f3f46; color: white; border: 1px solid #52525b; border-radius: 50%; width: 22px; height: 22px; font-size: 14px; cursor: pointer; display: flex; align-items: center; justify-content: center; padding: 0; box-shadow: 0 2px 4px rgba(0,0,0,0.2); transition: background 0.2s; z-index: 2;" title="Remove image">&times;</button>
                </div>

                <div style="display: flex; align-items: flex-end; gap: 10px; width: 100%;">
                    <button type="button" class="btn-pill-icon" onclick="document.getElementById('imageInput').click()" title="Attach image" style="background: none; border: none; color: #b4b4b5; cursor: pointer; padding: 6px; display: flex; align-items: center; justify-content: center; margin-bottom: 3px; border-radius: 50%; transition: background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.1)'" onmouseout="this.style.background='none'">
                        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14"></path></svg>
                    </button>
                    
                    <textarea id="prompt" name="prompt" rows="1" placeholder="Ask anything..." class="pill-input" style="padding-top: 10px; padding-bottom: 10px; flex-grow: 1; align-self: center; background: transparent; border: none; color: white; outline: none; font-size: 1rem; resize: none; max-height: 150px;"></textarea>
                    
                    <div class="pill-actions" style="margin-bottom: 6px;">
                        <button type="submit" id="submitBtn" class="btn-pill-send" disabled style="background: #ffffff; color: #000000; border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border: none; cursor: pointer; transition: opacity 0.2s; margin-right: 4px;">
                            <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"></path></svg>
                        </button>
                        <button type="button" id="stopBtn" class="btn-pill-send d-none" onclick="stopGeneration()" title="Stop generating" style="background: #ffffff; color: #000000; border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border: none; cursor: pointer; margin-right: 4px;">
                            <svg width="12" height="12" fill="currentColor" viewBox="0 0 16 16"><rect width="10" height="10" x="3" y="3" rx="2" ry="2"/></svg>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Fullscreen Image Modal -->
<div id="imageFullscreenModal" class="d-none" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.85); z-index: 9999; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(5px);">
    <button type="button" onclick="closeFullscreenImage()" style="position: absolute; top: 20px; right: 30px; background: rgba(255,255,255,0.1); border: none; color: white; border-radius: 50%; width: 40px; height: 40px; font-size: 24px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">&times;</button>
    <img id="fullscreenImageTarget" src="" style="max-width: 90%; max-height: 90vh; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.5);">
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
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    .animate-spin {
        animation: spin 1s linear infinite;
    }
    .w-fit-content { width: fit-content; }
    .hover-opacity-100:hover { opacity: 1 !important; }
    
    .chat-wrapper {
        flex: 1;
        display: flex;
        flex-direction: column;
        height: 100vh;
        height: 100dvh;
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
        background: #2f2f2f;
        border-radius: 24px;
        padding: 10px 20px;
        max-width: 80%;
        width: fit-content;
        text-align: left;
        white-space: pre-wrap;
        word-break: break-word;
        color: #ececec;
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
    }
    
    /* Custom Premium Dropdown */
    .model-dropdown-btn {
        background: transparent;
        color: #ececec;
        border: none;
        font-size: 1.15rem;
        font-weight: 600;
        padding: 8px 16px;
        border-radius: 12px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: background 0.2s;
    }
    .model-dropdown-btn:hover {
        background: rgba(255,255,255,0.08);
    }
    .model-dropdown-menu {
        position: absolute;
        top: 100%;
        left: 0;
        margin-top: 8px;
        background: #1e1e1e;
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 16px;
        padding: 8px;
        width: 360px;
        max-height: 70vh;
        overflow-y: auto;
        box-shadow: 0 10px 40px rgba(0,0,0,0.6);
        display: flex;
        flex-direction: column;
        gap: 2px;
        z-index: 100;
        scrollbar-width: thin;
        scrollbar-color: rgba(255,255,255,0.1) transparent;
    }
    .model-dropdown-menu::-webkit-scrollbar { width: 4px; }
    .model-dropdown-menu::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
    .model-category-header {
        padding: 8px 12px;
        margin-top: 6px;
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.72rem;
        font-weight: 700;
        color: #a1a1aa;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-radius: 6px;
        background: rgba(255,255,255,0.02);
    }
    .model-category-header .category-icon { font-size: 0.85rem; }
    .model-category-header .category-name { flex: 1; }
    .model-option {
        padding: 8px 14px;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.2s;
        border: 1px solid transparent;
    }
    .model-option:hover {
        background: rgba(255,255,255,0.05);
    }
    .model-option.active {
        background: rgba(37, 99, 235, 0.15);
        border: 1px solid rgba(37, 99, 235, 0.4);
    }
    .model-option .model-name {
        font-weight: 600;
        font-size: 1rem;
        color: #fff;
    }
    .model-option .model-desc {
        font-size: 0.8rem;
        color: #a1a1aa;
        margin-top: 2px;
    }
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
    
    .suggestions-container {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 12px;
        justify-content: center;
    }
    .suggestion-btn {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        color: #e2e8f0;
        font-size: 0.85rem;
        padding: 8px 12px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .suggestion-btn:hover {
        background: rgba(255, 255, 255, 0.1);
        transform: translateY(-1px);
    }

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
        .custom-model-dropdown {
            top: 10px !important;
            left: 48px !important;
        }
        .model-dropdown-btn {
            font-size: 0.95rem;
            padding: 6px 12px;
        }
        .message-round {
            padding: 16px 14px;
        }
        .chat-column {
            padding: 70px 0 140px 0;
        }
        .welcome-title {
            font-size: 2rem;
        }
    }

    @media (max-width: 768px) {
        .custom-model-dropdown {
            top: 10px !important;
            left: 44px !important;
        }
        .model-dropdown-menu {
            width: calc(100vw - 32px) !important;
            max-width: 340px !important;
            left: -32px !important;
        }
        .message-round {
            padding: 14px 10px;
        }
        .chat-column {
            padding: 65px 0 130px 0;
        }
        .user-bubble {
            max-width: 92%;
            padding: 10px 16px;
            font-size: 0.95rem;
        }
        .welcome-title {
            font-size: 1.7rem;
        }
        .chat-footer-wrapper {
            padding: 8px 10px;
            padding-bottom: max(10px, env(safe-area-inset-bottom, 16px));
        }
        .input-pill-container {
            padding: 6px 10px;
            background: #252525;
            border-radius: 24px;
        }
        .pill-input {
            width: 100%;
            padding: 6px 4px;
            max-height: 120px;
            font-size: 0.95rem;
        }
        .markdown-rendered pre {
            max-width: 100%;
            overflow-x: auto;
            font-size: 0.85rem;
        }
        .markdown-rendered table {
            display: block;
            overflow-x: auto;
            max-width: 100%;
        }
    }

    @media (max-width: 480px) {
        .custom-model-dropdown {
            top: 10px !important;
            left: 40px !important;
        }
        .model-dropdown-btn {
            font-size: 0.85rem;
            padding: 4px 8px;
        }
        .model-dropdown-menu {
            width: calc(100vw - 20px) !important;
            max-width: 320px !important;
            left: -28px !important;
        }
        .message-round {
            padding: 10px 8px;
        }
        .chat-column {
            padding: 60px 0 120px 0;
        }
        .user-bubble {
            max-width: 95%;
            font-size: 0.9rem;
        }
        .welcome-title {
            font-size: 1.4rem;
        }
        .chat-footer-wrapper {
            padding: 6px 8px;
            padding-bottom: max(8px, env(safe-area-inset-bottom, 12px));
        }
        .message-label {
            font-size: 0.8rem;
        }
        .message-text {
            font-size: 0.92rem;
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

    // Render initial server-side responses identically to real-time ones
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.markdown-rendered').forEach(el => {
            const raw = el.getAttribute('data-raw-content');
            if (raw) {
                el.innerHTML = marked.parse(raw);
            }
        });
        
        if (window.enhanceCodeBlocks) {
            window.enhanceCodeBlocks();
        }
        setTimeout(scrollToBottom, 100);
    });

    let currentAbortController = null;
    let currentPromptUuid = null;

    function stopGeneration() {
        if (currentAbortController) {
            currentAbortController.abort();
            currentAbortController = null;
            
            if (currentPromptUuid) {
                fetch('/agent/stop', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ prompt_uuid: currentPromptUuid })
                }).catch(e => console.log(e));
                currentPromptUuid = null;
            }
        }
    }

    function scrollToBottom() {
        const container = document.getElementById('chatResponseArea');
        container.scrollTop = container.scrollHeight;
    }

    // Only inserts the AI response — user bubble is added immediately on submit
    function appendAiResponse(rawContent, agent = null, model = null) {
        const container = document.getElementById('chatResponseArea');
        const content = Array.isArray(rawContent) ? rawContent.join('\n\n') : rawContent;
        
        let metaHtml = `
                    <div class="message-meta mt-3 d-flex gap-3 align-items-center">
                        <button class="btn-icon copy-btn-individual" title="Copy" style="color: #b4b4b4; transition: color 0.2s; padding: 0;" onmouseover="this.style.color='white'" onmouseout="this.style.color='#b4b4b4'">
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                        </button>`;
        
        if (agent && model) {
            const capitalizedAgent = agent.charAt(0).toUpperCase() + agent.slice(1);
            metaHtml += `
                        <span class="small-badge" style="display: flex; align-items: center; gap: 4px; color: #b4b4b4; opacity: 0.8;">
                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            ${capitalizedAgent} (${model})
                        </span>`;
        }
        
        metaHtml += `</div>`;

        const html = `
            <div class="message-round ai-message animate-in">
                <div class="message-content">
                    <div class="message-label d-flex align-items-center gap-2 mb-1">
                        <img src="{{ asset('robo.png') }}" alt="Aureon" width="24" height="24" style="border-radius: 4px;">
                        <span style="font-weight: 600; font-size: 0.95rem;">Aureon</span>
                    </div>
                    <div class="markdown-rendered message-text">${marked.parse(content)}</div>
                    ${metaHtml}
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

    function addUserBubble(prompt, currentImageBase64 = null) {
        const container = document.getElementById('chatResponseArea');
        
        const welcome = document.querySelector('.welcome-container');
        if (welcome) welcome.remove();

        const safePrompt = prompt.replace(/</g, '&lt;').replace(/>/g, '&gt;');
        let userHtml = `
            <div class="message-round user-message animate-in">
                <div class="message-content user-content">
                    <div class="message-label text-end fs-5"></div>`;
                    
        if (currentImageBase64) {
            userHtml += `<img src="${currentImageBase64}" style="max-width: 320px; max-height: 320px; border-radius: 14px; margin-bottom: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); cursor: zoom-in; border: 1px solid rgba(255,255,255,0.05);" onclick="openFullscreenImage(this.src)">`;
        } else if (window.uploadedImageUrl) {
            userHtml += `<img src="${window.uploadedImageUrl}" style="max-width: 320px; max-height: 320px; border-radius: 14px; margin-bottom: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); cursor: zoom-in; border: 1px solid rgba(255,255,255,0.05);" onclick="openFullscreenImage(this.src)">`;
        }
        
        userHtml += `<div class="message-text user-bubble">${safePrompt}</div>
                </div>
            </div>`;
        
        container.insertAdjacentHTML('beforeend', userHtml);
        scrollToBottom();
    }

    function sendSuggestion(text) {
        const promptInput = document.getElementById('prompt');
        promptInput.value = text;
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = false;
        
        const suggestions = document.getElementById('suggestionsContainer');
        if (suggestions) suggestions.style.display = 'none';

        document.getElementById('agentForm').dispatchEvent(new Event('submit', {cancelable: true, bubbles: true}));
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
        const stopBtn = document.getElementById('stopBtn');

        if (!prompt && !window.uploadedImageBase64) return;

        let currentImageBase64 = window.uploadedImageBase64 || null;
        let currentImageMime = window.uploadedImageMime || null;

        // Reset image input immediately
        if (typeof removeImage === 'function') {
            removeImage();
        }

        // Setup abort controller for this request
        currentAbortController = new AbortController();
        currentPromptUuid = crypto.randomUUID();
        const signal = currentAbortController.signal;

        // Show user bubble immediately — visible during loading
        addUserBubble(prompt, currentImageBase64);
        
        const suggestions = document.getElementById('suggestionsContainer');
        if (suggestions) suggestions.style.display = 'none';

        // Detect if user is asking for an image
        const isImageRequest = /generate.*image|create.*image|picture of|image of|draw.*image|show.*image|make.*image/i.test(prompt);
        if (isImageRequest) {
            document.getElementById('loadingText').classList.add('d-none');
            document.getElementById('loadingImage').classList.remove('d-none');
        } else {
            document.getElementById('loadingText').classList.remove('d-none');
            document.getElementById('loadingImage').classList.add('d-none');
        }

        // Move loading indicator to the bottom so it appears after user's new message
        document.getElementById('chatResponseArea').appendChild(loading);
        loading.classList.remove('d-none');
        submitBtn.disabled = true;

        // Clear input immediately
        promptInput.value = '';
        promptInput.style.height = window.innerWidth <= 768 ? '40px' : '44px';
        submitBtn.disabled = true;
        submitBtn.classList.add('d-none');
        stopBtn.classList.remove('d-none');
        
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
                    conversation_id: conversation_id,
                    prompt_uuid: currentPromptUuid,
                    image: window.uploadedImageUrl ? { url: window.uploadedImageUrl } : (currentImageBase64 ? { base64: currentImageBase64, mime: currentImageMime } : null),
                    model_selection: document.getElementById('modelSelection').value
                }),
                signal: signal
            });

            const result = await response.json();

            loading.classList.add('d-none');
            submitBtn.classList.remove('d-none');
            stopBtn.classList.add('d-none');
            submitBtn.disabled = false;
            currentAbortController = null;

            if (result.status) {
                appendAiResponse(result.data.content, result.agent, result.model);
                
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
            submitBtn.classList.remove('d-none');
            stopBtn.classList.add('d-none');
            submitBtn.disabled = false;
            currentAbortController = null;
            
            if (error.name === 'AbortError') {
                appendAiResponse("Generation stopped.");
            } else {
                handleAiError('Connection error. Please try again.', promptInput, prompt);
            }
        }
    });

    // Auto-resize textarea and toggle send button
    document.getElementById('prompt').addEventListener('input', function() {
        const submitBtn = document.getElementById('submitBtn');
        if(this.value.trim().length > 0 || window.uploadedImageBase64) {
            submitBtn.disabled = false;
        } else {
            submitBtn.disabled = true;
        }
        
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

    // Let sidebar links navigate naturally
    document.querySelectorAll('.nav-sub-link').forEach(link => {
        link.addEventListener('click', function(e) {
            // Normal navigation applies.
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

    // Image Upload Handling
    window.uploadedImageBase64 = null;
    window.uploadedImageMime = null;
    window.uploadedImageUrl = null;

    function previewImageUrl(url) {
        const img = document.getElementById('imagePreview');
        img.src = url;
        document.getElementById('imagePreviewContainer').classList.remove('d-none');
        
        window.uploadedImageUrl = url;
        window.uploadedImageBase64 = null;
        window.uploadedImageMime = null;
        
        const submitBtn = document.getElementById('submitBtn');
        if(submitBtn) submitBtn.disabled = false;
    }

    function previewImage(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('imagePreview');
                img.src = e.target.result;
                document.getElementById('imagePreviewContainer').classList.remove('d-none');
                
                window.uploadedImageBase64 = e.target.result;
                window.uploadedImageMime = file.type;
                document.getElementById('submitBtn').disabled = false;
            }
            reader.readAsDataURL(file);
        }
    }

    function removeImage() {
        const imageInput = document.getElementById('imageInput');
        if (imageInput) imageInput.value = '';
        
        const imagePreview = document.getElementById('imagePreview');
        if (imagePreview) imagePreview.src = '';
        
        const previewContainer = document.getElementById('imagePreviewContainer');
        if (previewContainer) previewContainer.classList.add('d-none');
        
        window.uploadedImageBase64 = null;
        window.uploadedImageMime = null;
        window.uploadedImageUrl = null;
        
        const promptText = document.getElementById('prompt').value;
        const submitBtn = document.getElementById('submitBtn');
        if(submitBtn) {
            submitBtn.disabled = promptText.trim().length === 0;
        }
    }

    // Fullscreen Image Logic
    function openFullscreenImage(src) {
        if (!src) return;
        const modal = document.getElementById('imageFullscreenModal');
        const img = document.getElementById('fullscreenImageTarget');
        img.src = src;
        modal.classList.remove('d-none');
    }

    function closeFullscreenImage() {
        const modal = document.getElementById('imageFullscreenModal');
        const img = document.getElementById('fullscreenImageTarget');
        modal.classList.add('d-none');
        img.src = '';
    }


    // Drag and Drop & Paste Support for Images
    const agentForm = document.getElementById('agentForm');
    
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        agentForm.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        agentForm.addEventListener(eventName, () => {
            agentForm.style.borderColor = '#007bff';
            agentForm.style.background = '#3a3a3a';
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        agentForm.addEventListener(eventName, () => {
            agentForm.style.borderColor = 'rgba(255,255,255,0.1)';
            agentForm.style.background = '#2f2f2f';
        }, false);
    });

    agentForm.addEventListener('drop', (e) => {
        if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
            const file = e.dataTransfer.files[0];
            if (file.type.startsWith('image/')) {
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                document.getElementById('imageInput').files = dataTransfer.files;
                previewImage({ target: { files: [file] } });
                return;
            }
        }
        
        // Handle dragging an image from another webpage (which comes as text/html with an <img> tag)
        const html = e.dataTransfer.getData('text/html');
        if (html) {
            const match = html.match(/<img[^>]+src="([^">]+)"/i);
            if (match && match[1]) {
                const url = match[1];
                // Replace any HTML entities
                const decodedUrl = url.replace(/&amp;/g, '&');
                previewImageUrl(decodedUrl);
                return;
            }
        }
    });

    document.addEventListener('paste', (e) => {
        if (e.clipboardData && e.clipboardData.files && e.clipboardData.files.length > 0) {
            const files = Array.from(e.clipboardData.files).filter(f => f.type.startsWith('image/'));
            if (files.length > 0) {
                const dataTransfer = new DataTransfer();
                files.forEach(f => dataTransfer.items.add(f));
                document.getElementById('imageInput').files = dataTransfer.files;
                previewImage({ target: { files: dataTransfer.files } });
            }
        }
    });

    // Custom Premium Dropdown Logic
    const dropdownBtn = document.getElementById('modelDropdownBtn');
    const dropdownMenu = document.getElementById('modelDropdownMenu');
    const dropdownInput = document.getElementById('modelSelection');
    const dropdownText = document.getElementById('selectedModelText');
    
    if (dropdownBtn && dropdownMenu) {
        dropdownBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdownMenu.classList.toggle('d-none');
        });
        
        document.addEventListener('click', (e) => {
            if (!dropdownMenu.contains(e.target) && !dropdownBtn.contains(e.target)) {
                dropdownMenu.classList.add('d-none');
            }
        });
        
        document.querySelectorAll('.model-option').forEach(option => {
            option.addEventListener('click', (e) => {
                if (option.classList.contains('locked')) return;
                
                const value = option.getAttribute('data-value');
                const name = option.querySelector('.model-name').innerText;
                
                dropdownInput.value = value;
                dropdownText.innerText = name;
                
                document.querySelectorAll('.model-option').forEach(opt => opt.classList.remove('active'));
                option.classList.add('active');
                
                dropdownMenu.classList.add('d-none');
            });
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