<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin | Mundial 2026</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-900 text-slate-200 min-h-screen">

    {{-- NAVBAR --}}
    <nav class="bg-slate-950 border-b border-slate-800 p-4 sticky top-0 z-50">
        <div class="max-w-6xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-user-shield text-indigo-500 text-2xl"></i>
                <h1 class="text-xl font-bold text-white tracking-wide">PANEL ADMINISTRADOR</h1>
            </div>

            {{-- MODIFICACIÓN AQUÍ: URL ESTÁTICA AL PUERTO 8001 --}}
            <a href="http://127.0.0.1:8001/" target="_blank" class="text-xs font-bold text-slate-400 hover:text-white border border-slate-700 px-3 py-1 rounded transition">
                <i class="fa-solid fa-external-link-alt mr-1"></i> Ir a Vista Cliente
            </a>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto px-4 py-8">

        {{-- MENSAJES DE ÉXITO --}}
        @if(session('success'))
            <div class="bg-green-500/10 border border-green-500/50 text-green-400 px-4 py-3 rounded-lg mb-6 flex items-center gap-2">
                <i class="fa-solid fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        {{-- FORMULARIO PARA CREAR PARTIDO --}}
        <div class="bg-slate-800 p-6 rounded-2xl shadow-xl border border-slate-700 mb-10">
            <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                <i class="fa-solid fa-plus-circle text-indigo-500"></i> Nuevo Encuentro
            </h2>

            <form action="{{ route('partidos.store') }}" method="POST" class="flex flex-col md:flex-row gap-4 items-end">
                @csrf
                <div class="flex-1 w-full">
                    <label class="block text-xs font-bold text-slate-400 mb-1 uppercase">Equipo Local</label>
                    <input type="text" name="equipo_local" placeholder="Ej: Perú" required
                        class="w-full bg-slate-900 border border-slate-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-indigo-500 transition">
                </div>

                <div class="hidden md:flex items-center justify-center pb-3 text-slate-500 font-black italic text-xl">VS</div>

                <div class="flex-1 w-full">
                    <label class="block text-xs font-bold text-slate-400 mb-1 uppercase">Equipo Visitante</label>
                    <input type="text" name="equipo_visitante" placeholder="Ej: Brasil" required
                        class="w-full bg-slate-900 border border-slate-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-indigo-500 transition">
                </div>

                <button type="submit" class="w-full md:w-auto bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 px-8 rounded-lg shadow-lg transition transform active:scale-95">
                    CREAR
                </button>
            </form>
        </div>

        {{-- LISTA DE PARTIDOS --}}
        <h3 class="text-slate-400 font-bold uppercase tracking-widest text-sm mb-6 border-b border-slate-800 pb-2">Gestión de Partidos</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($partidos as $partido)
                <div class="bg-slate-800 rounded-xl overflow-hidden border border-slate-700 shadow-lg hover:shadow-2xl transition hover:-translate-y-1 group">

                    {{-- ESTADO DEL PARTIDO --}}
                    <div class="px-4 py-2 text-xs font-bold uppercase tracking-widest text-center
                        @if($partido->estado == 'en_curso') bg-emerald-500/20 text-emerald-400 border-b border-emerald-500/20
                        @elseif($partido->estado == 'finalizado') bg-rose-500/20 text-rose-400 border-b border-rose-500/20
                        @else bg-slate-700 text-slate-400 border-b border-slate-600 @endif">

                        @if($partido->estado == 'en_curso')
                            <i class="fa-solid fa-circle text-[8px] mr-1 animate-pulse"></i> EN JUEGO
                        @elseif($partido->estado == 'entretiempo')
                            <i class="fa-solid fa-pause mr-1"></i> ENTRETIEMPO
                        @elseif($partido->estado == 'finalizado')
                            FINALIZADO
                        @else
                            PROGRAMADO
                        @endif
                    </div>

                    {{-- CONTENIDO --}}
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <div class="text-center w-1/3">
                                <div class="font-bold text-lg text-white leading-tight">{{ $partido->equipo_local }}</div>
                            </div>
                            <div class="text-center w-1/3 bg-slate-900 rounded-lg py-2 px-1 border border-slate-700 font-mono text-2xl font-bold text-white">
                                {{ $partido->goles_local }} - {{ $partido->goles_visitante }}
                            </div>
                            <div class="text-center w-1/3">
                                <div class="font-bold text-lg text-white leading-tight">{{ $partido->equipo_visitante }}</div>
                            </div>
                        </div>

                        {{-- BOTONES DE ACCIÓN --}}
                        <div class="grid grid-cols-2 gap-3">
                            {{-- Botón VER (Como cliente) --}}
                            <a href="http://127.0.0.1:8001/partido/{{ $partido->id }}" target="_blank" class="flex items-center justify-center gap-2 bg-slate-700 hover:bg-slate-600 text-slate-200 font-semibold py-2 rounded transition text-sm">
    <i class="fa-regular fa-eye"></i> Ver
</a>

                            {{-- Botón ARBITRAR (Panel de Control) --}}
                            <a href="{{ route('arbitro.panel', $partido->id) }}" class="flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-2 rounded transition text-sm shadow-lg shadow-indigo-900/50">
                                <i class="fa-solid fa-whistle"></i> Arbitrar
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($partidos->isEmpty())
            <div class="text-center py-20 opacity-50">
                <i class="fa-regular fa-folder-open text-6xl mb-4 text-slate-600"></i>
                <p class="text-slate-400 text-xl">No hay partidos creados.</p>
            </div>
        @endif

    </div>
</body>
</html>
