<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FutbolPlay | Mundial 2026</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .hero-gradient { background: linear-gradient(to top, #0f1115 10%, rgba(15, 17, 21, 0.8) 50%, rgba(15, 17, 21, 0.4) 100%); }
    </style>
</head>
<body class="bg-[#0f1115] text-white min-h-screen">

    <nav class="absolute top-0 w-full z-50 px-6 py-4 flex justify-between items-center">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-play text-red-600 text-2xl"></i>
            <span class="text-xl font-bold tracking-tighter">FUTBOL<span class="text-red-600">PLAY</span></span>
        </div>
        <a href="{{ route('panel') }}" class="bg-white/10 hover:bg-white/20 px-4 py-2 rounded text-sm font-semibold transition backdrop-blur-sm">
            Modo Árbitro
        </a>
    </nav>

    @php
        $destacado = $partidos->first();
        $restoPartidos = $partidos->skip(1);
    @endphp

    @if($destacado)
        <div class="relative w-full h-[85vh] flex items-end pb-20">
            <img src="https://images.unsplash.com/photo-1551958219-acbc608c6377?q=80&w=2940&auto=format&fit=crop" class="absolute inset-0 w-full h-full object-cover z-0" alt="Fondo">
            <div class="absolute inset-0 hero-gradient z-10"></div>
            <div class="relative z-20 max-w-7xl mx-auto px-6 w-full">
                <span class="bg-red-600 text-white text-xs font-bold px-3 py-1 rounded uppercase tracking-wider mb-4 inline-block">Partido Destacado</span>
                <h1 class="text-5xl md:text-7xl font-black mb-2 leading-tight">
                    {{ $destacado->equipo_local }} <span class="text-gray-400 font-light text-4xl align-middle mx-2">vs</span> {{ $destacado->equipo_visitante }}
                </h1>
                <div class="flex items-center gap-4 mt-6">
                    <a href="{{ route('partido.show', $destacado->id) }}" class="bg-white text-black px-8 py-3 rounded font-bold hover:bg-gray-200 transition flex items-center gap-2">
                        <i class="fa-solid fa-play"></i> VER TRANSMISIÓN
                    </a>
                    <div class="bg-black/50 backdrop-blur px-4 py-3 rounded text-white font-mono font-bold border border-white/10">
                        {{ $destacado->goles_local }} - {{ $destacado->goles_visitante }}
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="h-screen flex items-center justify-center"><h1 class="text-3xl text-gray-500">No hay partidos hoy</h1></div>
    @endif

    <div class="max-w-7xl mx-auto px-6 py-10">
        <h3 class="text-xl font-bold mb-6 border-l-4 border-red-600 pl-4">Cartelera</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($restoPartidos as $partido)
                <a href="{{ route('partido.show', $partido->id) }}" class="bg-[#161a23] rounded-xl p-6 border border-white/5 hover:border-white/20 transition block">
                    <div class="flex justify-between items-center">
                        <span class="font-bold w-1/3 text-center">{{ $partido->equipo_local }}</span>
                        <span class="font-mono font-bold text-xl">{{ $partido->goles_local }} - {{ $partido->goles_visitante }}</span>
                        <span class="font-bold w-1/3 text-center">{{ $partido->equipo_visitante }}</span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</body>
</html>
