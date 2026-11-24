<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Hamzah Style') }}</title>

        <!-- Google Fonts: Poppins -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body {
                font-family: 'Poppins', sans-serif;
                background-color: #FAF7F2;
                color: #26211D;
            }
            .font-serif-title {
                font-family: 'Poppins', sans-serif;
            }
        </style>
    </head>
    <body class="font-sans antialiased bg-[#FAF7F2] text-[#26211D] selection:bg-[#B58742] selection:text-white">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-8 sm:pt-0 pb-12 px-4">
            <div class="mb-6 text-center">
                <a href="{{ url('/') }}" class="inline-flex items-center group">
                    <img src="{{ asset('images/logo.png') }}" 
                         alt="Hamzah Style" 
                         class="h-9 sm:h-10 w-auto object-contain">
                </a>
            </div>

            <div class="w-full sm:max-w-md px-6 py-6 sm:px-8 sm:py-8 bg-white border border-[#ECE4D8] shadow-xl rounded-2xl">
                {{ $slot }}
            </div>

            <div class="mt-8 text-center text-xs text-stone-500">
                <a href="{{ url('/') }}" class="hover:text-stone-900 font-medium transition">← Kembali ke Beranda Toko</a>
            </div>
        </div>
    </body>
</html>
