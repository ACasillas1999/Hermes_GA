<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Hermes GA')</title>
    <link rel="icon" type="image/png" href="/logo.png">
    <script>
        (function () {
            try {
                const stored = localStorage.getItem('theme');
                const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                const theme = stored || (prefersDark ? 'dark' : 'light');
                document.documentElement.setAttribute('data-theme', theme);
            } catch (e) {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>
    <style>
        :root {
            color-scheme: light;
            --bg: #f4f6fb;
            --panel: #ffffff;
            --card: #ffffff;
            --line: #e2e8f0;
            --text: #0f172a;
            --muted: #64748b;
            --accent: #FF8C00;
            --accent-strong: #FF7000;
            --accent-blue: #0066B3;
            --shadow: 0 16px 32px rgba(15, 23, 42, 0.08);
        }

        html[data-theme="dark"] {
            color-scheme: dark;
            --bg: #0f172a;
            --panel: #0b1220;
            --card: #111a2e;
            --line: #1f2a44;
            --text: #e2e8f0;
            --muted: #94a3b8;
            --accent: #FFA500;
            --accent-strong: #FF8C00;
            --accent-blue: #0088DD;
            --shadow: 0 20px 45px rgba(15, 23, 42, 0.4);
        }

        * { box-sizing: border-box; }

        /* Celestial Animations */
        @keyframes twinkle {
            0%, 100% { opacity: 0.3; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.2); }
        }

        @keyframes twinkle-slow {
            0%, 100% { opacity: 0.2; }
            50% { opacity: 0.8; }
        }

        @keyframes nebula-drift {
            0% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(30px, -20px) scale(1.1); }
            100% { transform: translate(0, 0) scale(1); }
        }

        @keyframes lightning-strike {
            0%, 90%, 100% { opacity: 0; }
            91% { opacity: 0.1; }
            92% { opacity: 0.8; }
            93% { opacity: 0.2; }
            94% { opacity: 1; }
            95% { opacity: 0; }
        }

        @keyframes screen-flash {
            0%, 90%, 100% { opacity: 0; }
            92%, 94% { opacity: 0.03; }
        }

        @keyframes float-particle {
            0%, 100% { transform: translate(0, 0); }
            25% { transform: translate(10px, -15px); }
            50% { transform: translate(-5px, -25px); }
            75% { transform: translate(-15px, -10px); }
        }

        @keyframes card-entrance {
            0% {
                opacity: 0;
                transform: scale(0.85) translateY(30px) rotateX(10deg);
            }
            100% {
                opacity: 1;
                transform: scale(1) translateY(0) rotateX(0deg);
            }
        }

        @keyframes glow-pulse {
            0%, 100% { box-shadow: 0 0 20px rgba(56, 189, 248, 0.3), 0 0 40px rgba(56, 189, 248, 0.1), var(--shadow); }
            50% { box-shadow: 0 0 30px rgba(56, 189, 248, 0.5), 0 0 60px rgba(56, 189, 248, 0.2), var(--shadow); }
        }

        @keyframes shimmer {
            0% { background-position: -200% center; }
            100% { background-position: 200% center; }
        }

        body {
            margin: 0;
            font-family: "Segoe UI", system-ui, sans-serif;
            font-size: 14px;
            background: linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%);
            color: var(--text);
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            opacity: 0;
            transform: translateY(6px);
            transition: opacity 180ms ease, transform 180ms ease;
            position: relative;
            overflow: hidden;
        }

        html[data-theme="dark"] body {
            background: radial-gradient(circle at top, #1e293b 0%, #0f172a 60%, #060a13 100%);
        }

        /* Starfield Background */
        body::before,
        body::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            pointer-events: none;
        }

        /* Layer 1: Small stars */
        body::before {
            background-image: 
                radial-gradient(2px 2px at 20% 30%, white, transparent),
                radial-gradient(2px 2px at 60% 70%, white, transparent),
                radial-gradient(1px 1px at 50% 50%, white, transparent),
                radial-gradient(1px 1px at 80% 10%, white, transparent),
                radial-gradient(2px 2px at 90% 60%, white, transparent),
                radial-gradient(1px 1px at 33% 80%, white, transparent),
                radial-gradient(1px 1px at 15% 60%, white, transparent);
            background-size: 200% 200%;
            background-position: 0% 0%;
            animation: twinkle 3s ease-in-out infinite;
            opacity: 0;
        }

        html[data-theme="dark"] body::before {
            opacity: 0.6;
        }

        /* Layer 2: Medium stars */
        body::after {
            background-image: 
                radial-gradient(3px 3px at 40% 20%, rgba(56, 189, 248, 0.8), transparent),
                radial-gradient(2px 2px at 70% 40%, rgba(168, 85, 247, 0.6), transparent),
                radial-gradient(2px 2px at 25% 70%, rgba(56, 189, 248, 0.7), transparent),
                radial-gradient(3px 3px at 85% 80%, rgba(168, 85, 247, 0.5), transparent);
            background-size: 250% 250%;
            background-position: 0% 0%;
            animation: twinkle-slow 5s ease-in-out infinite;
            opacity: 0;
        }

        html[data-theme="dark"] body::after {
            opacity: 0.4;
        }

        /* Nebula Effect */
        .celestial-bg {
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at 30% 40%, rgba(56, 189, 248, 0.15) 0%, transparent 50%),
                        radial-gradient(circle at 70% 60%, rgba(168, 85, 247, 0.1) 0%, transparent 50%);
            animation: nebula-drift 20s ease-in-out infinite;
            pointer-events: none;
            opacity: 0;
        }

        html[data-theme="dark"] .celestial-bg {
            opacity: 1;
        }

        /* Canvas Lightning */
        #Rayos {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            opacity: 0;
            z-index: 1;
        }

        html[data-theme="dark"] #Rayos {
            opacity: 1;
        }

        /* Hyperspace Intro */
        #hyperspace-intro {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: radial-gradient(#000, #111);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 1;
            transition: opacity 1s ease-out;
        }

        #hyperspace-intro.fade-out {
            opacity: 0;
            pointer-events: none;
        }

        #hyperspace-canvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
        }

        #logo-container {
            position: relative;
            z-index: 10000;
            text-align: center;
            opacity: 0;
            transform: scale(0.05) translateZ(0);
            animation: logo-hyperspace-zoom 4.5s cubic-bezier(0.25, 0.46, 0.45, 0.94) 0.5s forwards;
        }

        @keyframes logo-hyperspace-zoom {
            0% {
                opacity: 0;
                transform: scale(0.05) translateZ(0);
            }
            15% {
                opacity: 1;
                transform: scale(0.2) translateZ(0);
            }
            40% {
                opacity: 1;
                transform: scale(0.5) translateZ(0);
            }
            70% {
                opacity: 1;
                transform: scale(0.9) translateZ(0);
            }
            85% {
                opacity: 1;
                transform: scale(1.05) translateZ(0);
            }
            100% {
                opacity: 1;
                transform: scale(1) translateZ(0);
            }
        }

        #intro-logo {
            width: 200px;
            height: auto;
            filter: drop-shadow(0 0 30px rgba(56, 189, 248, 0.8))
                    drop-shadow(0 0 60px rgba(56, 189, 248, 0.4));
            animation: logo-glow 2s ease-in-out infinite;
        }

        @keyframes logo-glow {
            0%, 100% {
                filter: drop-shadow(0 0 30px rgba(56, 189, 248, 0.8))
                        drop-shadow(0 0 60px rgba(56, 189, 248, 0.4));
            }
            50% {
                filter: drop-shadow(0 0 40px rgba(56, 189, 248, 1))
                        drop-shadow(0 0 80px rgba(56, 189, 248, 0.6));
            }
        }

        #loading-text {
            margin-top: 30px;
            color: #FF8C00;
            font-size: 16px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            animation: text-pulse 1.5s ease-in-out infinite;
        }

        @keyframes text-pulse {
            0%, 100% {
                opacity: 0.6;
            }
            50% {
                opacity: 1;
            }
        }

        /* Portal Vortex Effect */
        #portal-vortex {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: 10000;
            background: rgba(0, 0, 0, 0);
            transition: background 0.5s ease-in;
        }

        #portal-vortex.active {
            background: rgba(0, 0, 0, 0.95);
        }

        #vortex-canvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
        }

        /* Floating Particles */
        .particles {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 600px;
            height: 600px;
            pointer-events: none;
            opacity: 0;
        }

        html[data-theme="dark"] .particles {
            opacity: 1;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: radial-gradient(circle, rgba(56, 189, 248, 0.8), transparent);
            border-radius: 50%;
            filter: blur(1px);
            animation: float-particle 6s ease-in-out infinite;
        }

        .particle:nth-child(1) { top: 20%; left: 30%; animation-delay: 0s; }
        .particle:nth-child(2) { top: 60%; left: 70%; animation-delay: 1s; }
        .particle:nth-child(3) { top: 40%; left: 80%; animation-delay: 2s; }
        .particle:nth-child(4) { top: 80%; left: 20%; animation-delay: 3s; }
        .particle:nth-child(5) { top: 30%; left: 50%; animation-delay: 1.5s; }
        .particle:nth-child(6) { top: 70%; left: 40%; animation-delay: 2.5s; }

        body.page-ready { opacity: 1; transform: translateY(0); }

        .auth-card {
            width: min(520px, 95vw);
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 20px;
            padding: 40px 32px;
            box-shadow: var(--shadow);
            position: relative;
            z-index: 10;
            animation: card-entrance 0.8s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        html[data-theme="dark"] .auth-card {
            animation: card-entrance 0.8s cubic-bezier(0.34, 1.56, 0.64, 1) forwards,
                       glow-pulse 4s ease-in-out infinite 0.8s;
        }

        .auth-logo {
            display: flex;
            justify-content: center;
            margin-bottom: 16px;
        }

        .auth-logo img {
            width: 240px;
            height: auto;
            filter: drop-shadow(0 4px 12px rgba(255, 140, 0, 0.3));
        }

        html[data-theme="dark"] .auth-logo img {
            filter: drop-shadow(0 4px 20px rgba(255, 140, 0, 0.5));
        }

        .auth-header {
            display: none;
        }

        .theme-toggle {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 999px;
            border: 1px solid var(--line);
            background: transparent;
            color: var(--text);
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s ease;
        }

        .theme-toggle:hover {
            border-color: var(--accent);
            box-shadow: 0 0 15px rgba(56, 189, 248, 0.3);
        }

        label {
            font-size: 12px;
            color: var(--muted);
            margin-bottom: 4px;
            display: block;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 8px 10px;
            border-radius: 8px;
            border: 1px solid var(--line);
            background: var(--panel);
            color: var(--text);
            font-size: 13px;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.1),
                        0 0 20px rgba(56, 189, 248, 0.2);
        }

        html[data-theme="dark"] input[type="text"]:focus,
        html[data-theme="dark"] input[type="email"]:focus,
        html[data-theme="dark"] input[type="password"]:focus {
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.2),
                        0 0 25px rgba(56, 189, 248, 0.4),
                        inset 0 0 10px rgba(56, 189, 248, 0.1);
        }

        .row { 
            display: grid; 
            gap: 8px; 
        }

        .auth-form {
            display: flex;
            flex-direction: column;
            gap: 16px;
            flex: 1;
        }

        .button-container {
            margin-top: auto;
            padding-top: 16px;
        }

        .button {
            border: none;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            color: #02131f;
            background: linear-gradient(135deg, var(--accent), var(--accent-strong));
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s ease;
        }

        .button:hover::before {
            left: 100%;
        }

        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(56, 189, 248, 0.4);
        }

        html[data-theme="dark"] .button:hover {
            box-shadow: 0 5px 25px rgba(56, 189, 248, 0.6),
                        0 0 30px rgba(56, 189, 248, 0.3);
        }

        .button-secondary {
            color: var(--text);
            background: transparent;
            border: 1px solid var(--line);
        }

        .alert {
            padding: 10px 12px;
            border-radius: 12px;
            margin-bottom: 12px;
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid rgba(239, 68, 68, 0.4);
            color: #fecaca;
        }
    </style>
</head>
<body>
<!-- Hyperspace Intro -->
<div id="hyperspace-intro">
    <canvas id="hyperspace-canvas"></canvas>
    <div id="logo-container">
        <img src="/logo.png" alt="Hermes GA" id="intro-logo">
        <div id="loading-text">Iniciando sistema...</div>
    </div>
</div>

<!-- Portal Vortex Effect (for login success) -->
<div id="portal-vortex" style="display: none;">
    <canvas id="vortex-canvas"></canvas>
</div>

<!-- Celestial Effects -->
<canvas id="Rayos"></canvas>
<div class="celestial-bg"></div>
<div class="particles">
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
</div>

<div class="auth-card">
    <div class="auth-logo">
        <img src="/logo.png" alt="Hermes GA">
    </div>

    <div class="auth-header">
        <div style="font-weight: 700;">@yield('title', 'Hermes GA')</div>
        <button class="theme-toggle" type="button" id="theme-toggle">
            <span id="theme-label">Tema oscuro</span>
        </button>
    </div>

    @if ($errors->any())
        <div class="alert">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    @yield('content')
</div>

<script>
    (function () {
        const body = document.body;
        requestAnimationFrame(() => {
            body.classList.add('page-ready');
        });

        const root = document.documentElement;
        const button = document.getElementById('theme-toggle');
        const label = document.getElementById('theme-label');

        function updateLabel(theme) {
            label.textContent = theme === 'dark' ? 'Tema oscuro' : 'Tema claro';
        }

        updateLabel(root.getAttribute('data-theme') || 'dark');
        button?.addEventListener('click', () => {
            const next = (root.getAttribute('data-theme') === 'dark') ? 'light' : 'dark';
            root.setAttribute('data-theme', next);
            try { localStorage.setItem('theme', next); } catch (e) {}
            updateLabel(next);
        });
    })();
</script>

<script>
    // Canvas Lightning Effect
    (function() {
        var canvasLightning = function(c, cw, ch){
            this.init = function(){
                this.loop();
            };    
            
            var _this = this;
            this.c = c;
            this.ctx = c.getContext('2d');
            this.cw = cw;
            this.ch = ch;
            this.mx = 0;
            this.my = 0;
            
            this.lightning = [];
            this.lightTimeCurrent = 0;
            this.lightTimeTotal = 100;
            
            this.rand = function(rMi, rMa){return ~~((Math.random()*(rMa-rMi+1))+rMi);};
            this.hitTest = function(x1, y1, w1, h1, x2, y2, w2, h2){return !(x1 + w1 < x2 || x2 + w2 < x1 || y1 + h1 < y2 || y2 + h2 < y1);};
            
            this.createL= function(x, y, canSpawn){					
                this.lightning.push({
                    x: x,
                    y: y,
                    xRange: this.rand(5, 30),
                    yRange: this.rand(5, 25),
                    path: [{
                        x: x,
                        y: y	
                    }],
                    pathLimit: this.rand(10, 35),
                    canSpawn: canSpawn,
                    hasFired: false
                });
            };
            
            this.updateL = function(){
                var i = this.lightning.length;
                while(i--){
                    var light = this.lightning[i];						
                    
                    light.path.push({
                        x: light.path[light.path.length-1].x + (this.rand(0, light.xRange)-(light.xRange/2)),
                        y: light.path[light.path.length-1].y + (this.rand(0, light.yRange))
                    });
                    
                    if(light.path.length > light.pathLimit){
                        this.lightning.splice(i, 1)
                    }
                    light.hasFired = true;
                };
            };
            
            this.renderL = function(){
                var i = this.lightning.length;
                while(i--){
                    var light = this.lightning[i];
                    
                    // Brand colors: Orange or Blue lightning
                    var brandColors = [
                        'hsla(30, 100%, 50%, ', // Orange
                        'hsla(206, 100%, 35%, ' // Blue
                    ];
                    var colorChoice = brandColors[this.rand(0, 1)];
                    this.ctx.strokeStyle = colorChoice + this.rand(10, 100)/100+')';
                    this.ctx.lineWidth = 1;
                    if(this.rand(0, 30) == 0){
                        this.ctx.lineWidth = 2;	
                    }
                    if(this.rand(0, 60) == 0){
                        this.ctx.lineWidth = 3;	
                    }
                    if(this.rand(0, 90) == 0){
                        this.ctx.lineWidth = 4;	
                    }
                    if(this.rand(0, 120) == 0){
                        this.ctx.lineWidth = 5;	
                    }
                    if(this.rand(0, 150) == 0){
                        this.ctx.lineWidth = 6;	
                    }	
                    
                    this.ctx.beginPath();
                    
                    var pathCount = light.path.length;
                    this.ctx.moveTo(light.x, light.y);
                    for(var pc = 0; pc < pathCount; pc++){	
                        this.ctx.lineTo(light.path[pc].x, light.path[pc].y);
                        
                        if(light.canSpawn){
                            if(this.rand(0, 100) == 0){
                                light.canSpawn = false;
                                this.createL(light.path[pc].x, light.path[pc].y, false);
                            }	
                        }
                    }
                    
                    // Subtle cyan flash instead of red
                    if(!light.hasFired){
                        this.ctx.fillStyle = 'rgba(56, 189, 248, '+this.rand(2, 8)/100+')';
                        this.ctx.fillRect(0, 0, this.cw, this.ch);	
                    }
                    
                    if(this.rand(0, 30) == 0){
                        this.ctx.fillStyle = 'rgba(56, 189, 248, '+this.rand(1, 3)/100+')';
                        this.ctx.fillRect(0, 0, this.cw, this.ch);	
                    }	
                    
                    this.ctx.stroke();
                };
            };
            
            this.lightningTimer = function(){
                this.lightTimeCurrent++;
                if(this.lightTimeCurrent >= this.lightTimeTotal){
                    var newX = this.rand(100, cw - 100);
                    var newY = this.rand(0, ch / 2); 
                    var createCount = this.rand(1, 3);
                    while(createCount--){							
                        this.createL(newX, newY, true);
                    }
                    this.lightTimeCurrent = 0;
                    this.lightTimeTotal = this.rand(30, 100);
                }
            }
            
            this.clearCanvas = function(){
                this.ctx.globalCompositeOperation = 'destination-out';
                this.ctx.fillStyle = 'rgba(15, 23, 42, '+this.rand(1, 30)/100+')';
                this.ctx.fillRect(0,0,this.cw,this.ch);
                this.ctx.globalCompositeOperation = 'source-over';
            };
            
            window.addEventListener('resize', function(){
                _this.cw = _this.c.width = window.innerWidth;
                _this.ch = _this.c.height = window.innerHeight;  
            });
            
            this.loop = function(){
                var loopIt = function(){
                    requestAnimationFrame(loopIt, _this.c);
                    _this.clearCanvas();
                    _this.updateL();
                    _this.lightningTimer();
                    _this.renderL();	
                };
                loopIt();					
            };
        };

        // Setup requestAnimationFrame polyfill
        var setupRAF = function(){
            var lastTime = 0;
            var vendors = ['ms', 'moz', 'webkit', 'o'];
            for(var x = 0; x < vendors.length && !window.requestAnimationFrame; ++x){
                window.requestAnimationFrame = window[vendors[x]+'RequestAnimationFrame'];
                window.cancelAnimationFrame = window[vendors[x]+'CancelAnimationFrame'] || window[vendors[x]+'CancelRequestAnimationFrame'];
            };
            
            if(!window.requestAnimationFrame){
                window.requestAnimationFrame = function(callback, element){
                    var currTime = new Date().getTime();
                    var timeToCall = Math.max(0, 16 - (currTime - lastTime));
                    var id = window.setTimeout(function() { callback(currTime + timeToCall); }, timeToCall);
                    lastTime = currTime + timeToCall;
                    return id;
                };
            };
            
            if (!window.cancelAnimationFrame){
                window.cancelAnimationFrame = function(id){
                    clearTimeout(id);
                };
            };
        };

        // Initialize canvas lightning when page loads
        window.addEventListener('load', function(){
            var c = document.getElementById('Rayos');
            if(c && c.getContext){
                var cw = c.width = window.innerWidth;
                var ch = c.height = window.innerHeight;	
                var cl = new canvasLightning(c, cw, ch);				
                
                setupRAF();
                cl.init();
            }
        });
    })();
</script>

<script>
    // Hyperspace Jump Animation
    (function() {
        const randomInRange = (max, min) =>
            Math.floor(Math.random() * (max - min + 1)) + min;
        
        const VELOCITY_INC = 1.01;
        const VELOCITY_INIT_INC = 1.025;
        const JUMP_VELOCITY_INC = 1.25;
        const JUMP_SIZE_INC = 1.15;
        const SIZE_INC = 1.01;
        const BASE_SIZE = 1;
        const RAD = Math.PI / 180;
        const WARP_COLORS = [
            [255, 140, 0],   // Orange #FF8C00
            [255, 149, 0],   // Orange #FF9500
            [255, 165, 0],   // Orange #FFA500
            [0, 102, 179],   // Blue #0066B3
            [0, 119, 204],   // Blue #0077CC
            [0, 136, 221],   // Blue #0088DD
            [255, 255, 255], // White
        ];

        class Star {
            STATE = {
                alpha: Math.random(),
                angle: randomInRange(0, 360) * RAD,
            };
            
            reset = () => {
                const angle = randomInRange(0, 360) * (Math.PI / 180);
                const vX = Math.cos(angle);
                const vY = Math.sin(angle);
                const travelled =
                    Math.random() > 0.5
                        ? Math.random() * Math.max(window.innerWidth, window.innerHeight) + (Math.random() * (window.innerWidth * 0.24))
                        : Math.random() * (window.innerWidth * 0.25);
                this.STATE = {
                    ...this.STATE,
                    iX: undefined,
                    iY: undefined,
                    active: travelled ? true : false,
                    x: Math.floor(vX * travelled) + window.innerWidth / 2,
                    vX,
                    y: Math.floor(vY * travelled) + window.innerHeight / 2,
                    vY,
                    size: BASE_SIZE,
                };
            };
            
            constructor() {
                this.reset();
            }
        }

        const generateStarPool = size => new Array(size).fill().map(() => new Star());

        class JumpToHyperspace {
            STATE = {
                stars: generateStarPool(300),
                bgAlpha: 0,
                sizeInc: SIZE_INC,
                velocity: VELOCITY_INC,
                jumping: false,
            };
            
            canvas = document.getElementById('hyperspace-canvas');
            context = this.canvas ? this.canvas.getContext('2d') : null;
            
            constructor() {
                if (!this.canvas || !this.context) return;
                this.setup();
                this.render();
                // Auto-start the jump sequence
                setTimeout(() => this.initiate(), 500);
                setTimeout(() => this.jump(), 1200);
            }
            
            render = () => {
                if (!this.context) return;
                
                const {
                    STATE: {
                        bgAlpha,
                        velocity,
                        sizeInc,
                        initiating,
                        jumping,
                        stars,
                    },
                    context,
                    render
                } = this;
                
                context.clearRect(0, 0, window.innerWidth, window.innerHeight);
                
                if (bgAlpha > 0) {
                    context.fillStyle = `rgba(31, 58, 157, ${bgAlpha})`;
                    context.fillRect(0, 0, window.innerWidth, window.innerHeight);
                }
                
                const nonActive = stars.filter(s => !s.STATE.active);
                if (!initiating && nonActive.length > 0) {
                    nonActive[0].STATE.active = true;
                }
                
                for (const star of stars.filter(s => s.STATE.active)) {
                    const { active, x, y, iX, iY, iVX, iVY, size, vX, vY } = star.STATE;
                    
                    if (
                        ((iX || x) < 0 ||
                            (iX || x) > window.innerWidth ||
                            (iY || y) < 0 ||
                            (iY || y) > window.innerHeight) &&
                        active &&
                        !initiating
                    ) {
                        star.reset(true);
                    } else if (active) {
                        const newIX = initiating ? iX : iX + iVX;
                        const newIY = initiating ? iY : iY + iVY;
                        const newX = x + vX;
                        const newY = y + vY;
                        
                        const caught =
                            (vX < 0 && newIX < x) ||
                            (vX > 0 && newIX > x) ||
                            (vY < 0 && newIY < y) ||
                            (vY > 0 && newIY > y);
                        
                        star.STATE = {
                            ...star.STATE,
                            iX: caught ? undefined : newIX,
                            iY: caught ? undefined : newIY,
                            iVX: caught ? undefined : iVX * VELOCITY_INIT_INC,
                            iVY: caught ? undefined : iVY * VELOCITY_INIT_INC,
                            x: newX,
                            vX: star.STATE.vX * velocity,
                            y: newY,
                            vY: star.STATE.vY * velocity,
                            size: initiating ? size : size * (iX || iY ? SIZE_INC : sizeInc),
                        };
                        
                        let color = `rgba(255, 255, 255, ${star.STATE.alpha})`;
                        if (jumping) {
                            const [r, g, b] = WARP_COLORS[randomInRange(0, WARP_COLORS.length - 1)];
                            color = `rgba(${r}, ${g}, ${b}, ${star.STATE.alpha})`;
                        }
                        
                        context.strokeStyle = color;
                        context.lineWidth = size;
                        context.beginPath();
                        context.moveTo(star.STATE.iX || x, star.STATE.iY || y);
                        context.lineTo(star.STATE.x, star.STATE.y);
                        context.stroke();
                    }
                }
                
                requestAnimationFrame(render);
            };
            
            initiate = () => {
                if (this.STATE.jumping || this.STATE.initiating) return;
                this.STATE = {
                    ...this.STATE,
                    initiating: true,
                    velocity: VELOCITY_INIT_INC,
                    bgAlpha: 0.3,
                };
                
                for (const star of this.STATE.stars.filter(s => s.STATE.active)) {
                    star.STATE = {
                        ...star.STATE,
                        iX: star.STATE.x,
                        iY: star.STATE.y,
                        iVX: star.STATE.vX,
                        iVY: star.STATE.vY,
                    };
                }
            };
            
            jump = () => {
                this.STATE = {
                    ...this.STATE,
                    bgAlpha: 0.75,
                    jumping: true,
                    velocity: JUMP_VELOCITY_INC,
                    sizeInc: JUMP_SIZE_INC,
                };
                
                setTimeout(() => {
                    this.endIntro();
                }, 3500);
            };
            
            endIntro = () => {
                const intro = document.getElementById('hyperspace-intro');
                if (intro) {
                    intro.classList.add('fade-out');
                    setTimeout(() => {
                        intro.style.display = 'none';
                    }, 1000);
                }
            };
            
            setup = () => {
                this.context.lineCap = 'round';
                this.canvas.height = window.innerHeight;
                this.canvas.width = window.innerWidth;
            };
        }

        window.addEventListener('load', function() {
            new JumpToHyperspace();
        });
    })();

    // Portal Vortex Effect
    (function() {
        class PortalVortex {
            constructor() {
                this.overlay = document.getElementById('portal-vortex');
                this.canvas = document.getElementById('vortex-canvas');
                if (!this.canvas) return;
                
                this.ctx = this.canvas.getContext('2d');
                this.particles = [];
                this.isActive = false;
                this.startTime = 0;
                this.duration = 4000; // 4 seconds
                
                // Load logo image
                this.logo = new Image();
                this.logo.src = '/logo.png';
                this.logoScale = 0;
                
                this.setup();
            }
            
            setup() {
                this.canvas.width = window.innerWidth;
                this.canvas.height = window.innerHeight;
                this.centerX = window.innerWidth / 2;
                this.centerY = window.innerHeight / 2;
            }
            
            
            createParticles() {
                // Text characters to use
                const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@.-_';
                
                // Brand colors: Orange and Blue
                const colors = [
                    '#FF8C00', // Orange
                    '#FF9500',
                    '#FFA500',
                    '#0066B3', // Blue
                    '#0077CC',
                    '#0088DD',
                ];
                
                // Spawn particles from random edges
                const particlesToAdd = 20;
                
                for (let i = 0; i < particlesToAdd; i++) {
                    const side = Math.floor(Math.random() * 4);
                    let x, y;
                    
                    if (side === 0) {
                        x = -50;
                        y = Math.random() * window.innerHeight;
                    } else if (side === 1) {
                        x = Math.random() * window.innerWidth;
                        y = -50;
                    } else if (side === 2) {
                        x = window.innerWidth + 50;
                        y = Math.random() * window.innerHeight;
                    } else {
                        x = Math.random() * window.innerWidth;
                        y = window.innerHeight + 50;
                    }
                    
                    const dx = this.centerX - x;
                    const dy = this.centerY - y;
                    const distance = Math.sqrt(dx * dx + dy * dy);
                    const angle = Math.atan2(dy, dx);
                    
                    this.particles.push({
                        x: x,
                        y: y,
                        angle: angle,
                        distance: distance,
                        speed: 4 + Math.random() * 6,
                        size: 12 + Math.random() * 16,
                        text: chars[Math.floor(Math.random() * chars.length)],
                        color: colors[Math.floor(Math.random() * colors.length)],
                        alpha: 0.8 + Math.random() * 0.2,
                    });
                }
            }
            
            
            updateParticles() {
                // Remove particles that reached the center
                this.particles = this.particles.filter(p => p.distance > 20);
                
                for (let particle of this.particles) {
                    // Move particle towards center
                    particle.distance -= particle.speed;
                    particle.angle += 0.02; // Slight spiral
                    
                    particle.x = this.centerX + Math.cos(particle.angle) * particle.distance;
                    particle.y = this.centerY + Math.sin(particle.angle) * particle.distance;
                    
                    // Calculate alpha based on distance
                    const distanceAlpha = Math.min(1, particle.distance / 400);
                    const finalAlpha = particle.alpha * distanceAlpha;
                    
                    // Draw text particle
                    this.ctx.font = `bold ${particle.size}px Arial`;
                    
                    // Convert hex color to rgba
                    const hexToRgba = (hex, alpha) => {
                        const r = parseInt(hex.slice(1, 3), 16);
                        const g = parseInt(hex.slice(3, 5), 16);
                        const b = parseInt(hex.slice(5, 7), 16);
                        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
                    };
                    
                    this.ctx.fillStyle = hexToRgba(particle.color, finalAlpha);
                    this.ctx.textAlign = 'center';
                    this.ctx.textBaseline = 'middle';
                    
                    // Add glow
                    this.ctx.shadowBlur = 15;
                    this.ctx.shadowColor = hexToRgba(particle.color, finalAlpha);
                    
                    this.ctx.fillText(particle.text, particle.x, particle.y);
                }
                
                this.ctx.shadowBlur = 0;
            }
            
            
            animate() {
                if (!this.isActive) return;
                
                const elapsed = Date.now() - this.startTime;
                
                // Check if animation should end
                if (elapsed > this.duration) {
                    this.isActive = false;
                    return;
                }
                
                this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
                
                // Grow logo scale over time
                const progress = elapsed / this.duration;
                this.logoScale = Math.min(1, progress * 1.5); // Grow to full size
                
                // Draw logo in center
                if (this.logo.complete && this.logoScale > 0) {
                    const logoWidth = 200 * this.logoScale;
                    const logoHeight = (this.logo.height / this.logo.width) * logoWidth;
                    
                    this.ctx.save();
                    this.ctx.globalAlpha = this.logoScale;
                    
                    // Add glow to logo
                    this.ctx.shadowBlur = 30;
                    this.ctx.shadowColor = 'rgba(255, 140, 0, 0.6)'; // Orange glow
                    
                    this.ctx.drawImage(
                        this.logo,
                        this.centerX - logoWidth / 2,
                        this.centerY - logoHeight / 2,
                        logoWidth,
                        logoHeight
                    );
                    
                    this.ctx.restore();
                }
                
                // Continuously spawn new particles
                if (this.particles.length < 400) {
                    this.createParticles();
                }
                
                this.updateParticles();
                
                // Continue animation
                requestAnimationFrame(() => this.animate());
            }
            
            
            activate() {
                this.isActive = true;
                this.startTime = Date.now(); // Start the timer
                this.overlay.style.display = 'block';
                setTimeout(() => {
                    this.overlay.classList.add('active');
                }, 10);
                
                this.createParticles();
                this.animate();
                
                // Fade out auth card
                const card = document.querySelector('.auth-card');
                if (card) {
                    card.style.transition = 'opacity 2s ease-out, transform 2s ease-out';
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.8)';
                }
            }
        }
        
        
        // Create portal instance
        window.portalVortex = new PortalVortex();
        
        // Intercept form submission
        window.addEventListener('load', function() {
            const form = document.querySelector('form[action*="login"]');
            if (form) {
                let isSubmitting = false;
                
                form.addEventListener('submit', function(e) {
                    // Only trigger portal if form is valid and not already submitting
                    const email = form.querySelector('input[type="email"]');
                    const password = form.querySelector('input[type="password"]');
                    
                    if (email && password && email.value && password.value && !isSubmitting) {
                        // Prevent the default form submission
                        e.preventDefault();
                        isSubmitting = true;
                        
                        // Activate the portal animation
                        window.portalVortex.activate();
                        
                        // Wait for animation to complete (4 seconds), then submit the form
                        setTimeout(function() {
                            // Submit the form programmatically
                            form.submit();
                        }, 4000); // Match the animation duration
                    }
                });
            }
        });
    })();
</script>
</body>
</html>
