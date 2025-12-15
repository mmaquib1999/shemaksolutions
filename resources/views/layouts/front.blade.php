<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
   
        <title>{{ config('app.name', 'Laravel') }}</title>
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        @vite('resources/js/home-auth.js')

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0)
            }

            50% {
                transform: translateY(-10px)
            }
        }

        @keyframes pulse-glow {

            0%,
            100% {
                box-shadow: 0 0 20px rgba(14, 165, 233, 0.3)
            }

            50% {
                box-shadow: 0 0 40px rgba(14, 165, 233, 0.6)
            }
        }

        @keyframes gradient-shift {
            0% {
                background-position: 0% 50%
            }

            50% {
                background-position: 100% 50%
            }

            100% {
                background-position: 0% 50%
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0
            }

            to {
                opacity: 1
            }
        }

        @keyframes scale-in {
            from {
                opacity: 0;
                transform: scale(0.9)
            }

            to {
                opacity: 1;
                transform: scale(1)
            }
        }

        @keyframes spin {
            from {
                transform: rotate(0deg)
            }

            to {
                transform: rotate(360deg)
            }
        }

        @keyframes fade-in-up {
            from {
                opacity: 0;
                transform: translateY(30px)
            }

            to {
                opacity: 1;
                transform: translateY(0)
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px)
            }

            to {
                opacity: 1;
                transform: translateY(0)
            }
        }

        .animate-float {
            animation: float 3s ease-in-out infinite
        }

        .animate-pulse-glow {
            animation: pulse-glow 2s ease-in-out infinite
        }

        .animate-fade-in {
            animation: fade-in-up 0.6s ease-out forwards
        }

        .animate-scale-in {
            animation: scale-in 0.5s ease-out forwards
        }

        .animate-slide-up {
            animation: slideUp 0.3s ease-out
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0f172a 0%, #020617 50%, #0f172a 100%);
            color: #fff;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif
        }

        .hidden {
            display: none !important
        }

        .hero-gradient {
            background: linear-gradient(135deg, #0f172a 0%, #020617 30%, #0c1929 60%, #0f172a 100%);
            position: relative;
            overflow: hidden
        }

        .glass-card {
            background: rgba(30, 41, 59, 0.4);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(71, 85, 105, 0.4);
            border-radius: 16px;
            transition: all 0.4s
        }

        .glass-card:hover {
            background: rgba(30, 41, 59, 0.6);
            border-color: rgba(34, 211, 238, 0.4);
            transform: translateY(-4px)
        }

        .cta-primary {
            background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%);
            border: none;
            color: #fff;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s
        }

        .cta-primary:hover {
            box-shadow: 0 10px 30px rgba(14, 165, 233, 0.5);
            transform: translateY(-2px)
        }

        .btn {
            background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%);
            border: none;
            color: #fff;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            transition: all 0.3s
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(14, 165, 233, 0.4)
        }

        .btn-secondary {
            background: rgba(51, 65, 85, 0.5);
            border: 1px solid rgba(71, 85, 105, 0.5);
            color: #fff;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 500;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s
        }

        .btn-secondary:hover {
            background: rgba(71, 85, 105, 0.6)
        }

        .input {
            width: 100%;
            padding: 12px 16px;
            border-radius: 10px;
            border: 1px solid rgba(71, 85, 105, 0.5);
            background: rgba(15, 23, 42, 0.8);
            color: #fff;
            outline: none;
            font-size: 14px;
            transition: all 0.2s
        }

        .input:focus {
            border-color: #0ea5e9;
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.15)
        }

        .input::placeholder {
            color: #64748b
        }

        .label {
            display: block;
            font-size: 13px;
            color: #94a3b8;
            margin-bottom: 8px;
            font-weight: 500
        }

        .badge {
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600
        }

        .card {
            background: rgba(30, 41, 59, 0.5);
            border: 1px solid rgba(71, 85, 105, 0.5);
            border-radius: 16px;
            padding: 24px;
            backdrop-filter: blur(12px);
            transition: all 0.3s
        }

        .card:hover {
            border-color: rgba(100, 116, 139, 0.6);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2)
        }

        .stat-counter {
            font-variant-numeric: tabular-nums;
            background: linear-gradient(135deg, #22d3ee, #0ea5e9);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent
        }

        .industry-tag {
            transition: all 0.3s ease;
            cursor: pointer
        }

        .industry-tag:hover {
            transform: scale(1.05);
            border-color: rgba(34, 211, 238, 0.5);
            background: rgba(34, 211, 238, 0.1)
        }

        .pricing-card-popular {
            position: relative;
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.15) 0%, rgba(6, 182, 212, 0.1) 100%);
            border: 2px solid rgba(34, 211, 238, 0.5);
            transform: scale(1.02)
        }

        .pricing-card-popular::before {
            content: '⭐ MOST POPULAR';
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #0ea5e9, #06b6d4);
            padding: 4px 16px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700
        }

        .nav-btn {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 10px;
            border: none;
            margin-bottom: 4px;
            cursor: pointer;
            text-align: left;
            background: transparent;
            color: #94a3b8;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s
        }

        .nav-btn:hover {
            background: rgba(51, 65, 85, 0.4);
            color: #cbd5e1
        }

        .nav-btn.active {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.2) 0%, rgba(6, 182, 212, 0.1) 100%);
            color: #22d3ee
        }

        .quick-action-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid rgba(71, 85, 105, 0.3);
            background: rgba(15, 23, 42, 0.5);
            color: #cbd5e1;
            font-size: 13px;
            cursor: pointer;
            text-align: left;
            transition: all 0.2s
        }

        .quick-action-btn:hover {
            background: rgba(30, 41, 59, 0.8);
            border-color: rgba(100, 116, 139, 0.5);
            transform: translateX(4px)
        }

        .industry-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            text-align: left;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s
        }

        .industry-btn:hover {
            transform: translateX(4px)
        }

        .roi-card {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(5, 150, 105, 0.05) 100%);
            border: 1px solid rgba(16, 185, 129, 0.3);
            position: relative;
            overflow: hidden
        }

        .leaderboard-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border-radius: 10px;
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid rgba(71, 85, 105, 0.3);
            transition: all 0.2s
        }

        .leaderboard-item:hover {
            border-color: rgba(34, 211, 238, 0.5);
            transform: translateX(4px)
        }

        .leaderboard-item.gold {
            border-left: 3px solid #fbbf24
        }

        .leaderboard-item.silver {
            border-left: 3px solid #94a3b8
        }

        .leaderboard-item.bronze {
            border-left: 3px solid #cd7f32
        }

        .achievement-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600
        }

        .achievement-badge.loto {
            background: rgba(251, 191, 36, 0.2);
            color: #fbbf24
        }

        .achievement-badge.ppe {
            background: rgba(139, 92, 246, 0.2);
            color: #8b5cf6
        }

        .achievement-badge.fire {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444
        }

        .achievement-badge.electrical {
            background: rgba(59, 130, 246, 0.2);
            color: #3b82f6
        }

        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            z-index: 100;
            animation: fadeIn 0.2s ease-out
        }

        .hamburger-btn {
            display: none;
            background: none;
            border: none;
            color: #fff;
            cursor: pointer;
            padding: 8px
        }

        .mobile-drawer {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 280px;
            background: rgba(15, 23, 42, 0.98);
            border-right: 1px solid rgba(71, 85, 105, 0.3);
            z-index: 200;
            transform: translateX(-100%);
            transition: transform 0.3s ease;
            backdrop-filter: blur(12px);
            display: flex;
            flex-direction: column
        }

        .mobile-drawer.open {
            transform: translateX(0)
        }

        .mobile-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.6);
            z-index: 199;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s
        }

        .mobile-overlay.open {
            opacity: 1;
            pointer-events: auto
        }

        ::-webkit-scrollbar {
            width: 6px;
            height: 6px
        }

        ::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.5)
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(71, 85, 105, 0.5);
            border-radius: 3px
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(100, 116, 139, 0.6)
        }

        @media(max-width:900px) {

            .landing-grid-2,
            .landing-grid-3 {
                grid-template-columns: 1fr !important
            }

            .landing-grid-4 {
                grid-template-columns: repeat(2, 1fr) !important
            }

            .pricing-grid {
                grid-template-columns: 1fr !important;
                max-width: 400px !important;
                margin: 0 auto !important
            }

            .dashboard-stats {
                grid-template-columns: repeat(2, 1fr) !important
            }

            .query-layout {
                flex-direction: column !important
            }

            .query-sidebar {
                width: 100% !important
            }
        }

        @media(max-width:768px) {
            #dashboard-page aside {
                display: none
            }

            #dashboard-page main {
                width: 100%
            }

            .hamburger-btn {
                display: flex !important
            }
        }

        @media(max-width:480px) {

            .landing-grid-4,
            .dashboard-stats {
                grid-template-columns: 1fr !important
            }
        }

        .error-text {
    color: #ef4444;
    font-size: 12px;
    margin-top: 6px;
    display: block;
}

.input.error {
    border-color: #ef4444;
}


        
    </style>
</head>

<body>
     @yield('content')
    </body>
</html>
