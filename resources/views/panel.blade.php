<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Árbitro | {{ $partido->equipo_local }} vs {{ $partido->equipo_visitante }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-800 text-white min-h-screen p-6">

    {{-- 1. CÁLCULO DE TIEMPO SEGURO (PHP) --}}
    @php
        $segundos = 0;
        if($partido->estado == 'en_curso' && $partido->hora_inicio) {
            // Usamos timestamps para evitar errores de zona horaria
            $inicio = \Carbon\Carbon::parse($partido->hora_inicio)->timestamp;
            $ahora = now()->timestamp;
            $segundos = $ahora - $inicio;

            // Si sale negativo por error de config del servidor, lo forzamos a 0
            if($segundos < 0) $segundos = 0;
        }
    @endphp

    <div class="max-w-4xl mx-auto">
        {{-- HEADER DEL PANEL --}}
        <div class="bg-gray-900 p-6 rounded-2xl shadow-2xl border border-gray-700 mb-8 text-center relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500"></div>

            <div class="flex justify-between items-center mb-4">
                <a href="{{ route('inicio') }}" class="text-gray-500 hover:text-white text-sm"><i class="fa-solid fa-arrow-left"></i> Salir</a>
                <h1 class="text-2xl font-black uppercase tracking-widest text-gray-400">Control de Partido</h1>
                <div class="w-10"></div>
            </div>

            <div class="flex justify-center items-center gap-8">
                {{-- CRONÓMETRO --}}
                <div class="text-5xl font-mono font-bold text-yellow-400 bg-black px-6 py-2 rounded-lg border border-gray-600 shadow-[0_0_15px_rgba(250,204,21,0.2)]" id="cronometro">
                    00:00
                </div>

                {{-- ESTADO --}}
                @php
                    $claseEstado = match($partido->estado) {
                        'en_curso' => 'bg-green-600 animate-pulse shadow-green-500/50',
                        'entretiempo' => 'bg-yellow-600 shadow-yellow-500/50',
                        'finalizado' => 'bg-red-600 shadow-red-500/50',
                        default => 'bg-gray-700'
                    };
                    $textoEstado = match($partido->estado) {
                        'en_curso' => 'EN JUEGO',
                        'entretiempo' => 'ENTRETIEMPO',
                        'finalizado' => 'FINALIZADO',
                        default => 'PROGRAMADO'
                    };
                @endphp
                <div class="text-xl font-bold px-4 py-2 rounded shadow-lg {{ $claseEstado }}" id="estadoPartido">
                    {{ $textoEstado }}
                </div>
            </div>

            {{-- BOTONES DE CONTROL DE TIEMPO --}}
            <div class="flex justify-center gap-4 mt-8">
                <button onclick="controlPartido('INICIO')" class="bg-green-600 hover:bg-green-500 text-white font-bold py-3 px-6 rounded shadow-lg transform active:scale-95 transition border-b-4 border-green-800 active:border-0 active:translate-y-1">
                    ▶ INICIAR
                </button>
                <button onclick="controlPartido('ENTRETIEMPO')" class="bg-yellow-600 hover:bg-yellow-500 text-white font-bold py-3 px-6 rounded shadow-lg transform active:scale-95 transition border-b-4 border-yellow-800 active:border-0 active:translate-y-1">
                    ⏸ ENTRETIEMPO
                </button>
                <button onclick="controlPartido('FIN')" class="bg-red-600 hover:bg-red-500 text-white font-bold py-3 px-6 rounded shadow-lg transform active:scale-95 transition border-b-4 border-red-800 active:border-0 active:translate-y-1">
                    ⏹ FINALIZAR
                </button>
            </div>

            {{-- ZONA DE PELIGRO (RESET) --}}
            <div class="mt-8 pt-6 border-t border-gray-700 flex justify-center">
                <form action="{{ route('partido.reiniciar', $partido->id) }}" method="POST" onsubmit="return confirm('⚠️ ¡ATENCIÓN ARBITRO! ⚠️\n\nEstás a punto de REINICIAR el partido.\n\n- Se borrarán todos los goles.\n- Se borrarán las tarjetas.\n- El cronómetro volverá a 00:00.\n\n¿Estás seguro de continuar?');">
                    @csrf
                    <button type="submit" class="group flex items-center gap-2 text-red-500 hover:text-white border border-red-900 hover:bg-red-600 px-4 py-2 rounded transition-all duration-300 text-sm font-bold tracking-widest uppercase hover:shadow-[0_0_15px_rgba(220,38,38,0.5)]">
                        <i class="fa-solid fa-triangle-exclamation group-hover:animate-bounce"></i>
                        Reiniciar Partido a Cero
                        <i class="fa-solid fa-triangle-exclamation group-hover:animate-bounce"></i>
                    </button>
                </form>
            </div>
        </div>

        {{-- PANELES DE EQUIPOS --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            {{-- EQUIPO LOCAL --}}
            <div class="bg-blue-900/20 p-6 rounded-2xl border border-blue-500/30 shadow-xl">
                <h2 class="text-2xl font-bold text-center text-blue-400 mb-6 flex justify-between items-center px-2">
                    <span class="truncate w-2/3 text-left">{{ $partido->equipo_local }}</span>
                    <span id="txtGolesLocal" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-4xl shadow-lg border border-blue-400">
                        {{ $partido->goles_local }}
                    </span>
                </h2>
                <div class="space-y-4">
                    <button onclick="eventoEquipo('GOL', '{{ $partido->equipo_local }}')" class="w-full bg-blue-600 hover:bg-blue-500 py-4 rounded-xl font-bold text-xl shadow-lg border-b-4 border-blue-800 active:border-0 active:translate-y-1 transition text-white">
                        ⚽ GOL LOCAL
                    </button>
                    <div class="grid grid-cols-2 gap-4">
                        <button onclick="eventoEquipo('TARJETA_AMARILLA', '{{ $partido->equipo_local }}')" class="bg-yellow-500 hover:bg-yellow-400 py-3 rounded-lg font-bold text-black border-b-4 border-yellow-700 active:border-0 active:translate-y-1">🟨 Amarilla</button>
                        <button onclick="eventoEquipo('TARJETA_ROJA', '{{ $partido->equipo_local }}')" class="bg-red-600 hover:bg-red-500 py-3 rounded-lg font-bold text-white border-b-4 border-red-800 active:border-0 active:translate-y-1">🟥 Roja</button>
                    </div>
                    <button onclick="eventoCambio('{{ $partido->equipo_local }}')" class="w-full bg-slate-700 hover:bg-slate-600 py-3 rounded-lg font-bold border border-slate-500">🔄 Cambio</button>
                </div>
            </div>

            {{-- EQUIPO VISITA --}}
            <div class="bg-indigo-900/20 p-6 rounded-2xl border border-indigo-500/30 shadow-xl">
                <h2 class="text-2xl font-bold text-center text-indigo-400 mb-6 flex justify-between items-center px-2">
                    <span class="truncate w-2/3 text-left">{{ $partido->equipo_visitante }}</span>
                    <span id="txtGolesVisita" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-4xl shadow-lg border border-indigo-400">
                        {{ $partido->goles_visitante }}
                    </span>
                </h2>
                <div class="space-y-4">
                    <button onclick="eventoEquipo('GOL', '{{ $partido->equipo_visitante }}')" class="w-full bg-indigo-600 hover:bg-indigo-500 py-4 rounded-xl font-bold text-xl shadow-lg border-b-4 border-indigo-800 active:border-0 active:translate-y-1 transition text-white">
                        ⚽ GOL VISITA
                    </button>
                    <div class="grid grid-cols-2 gap-4">
                        <button onclick="eventoEquipo('TARJETA_AMARILLA', '{{ $partido->equipo_visitante }}')" class="bg-yellow-500 hover:bg-yellow-400 py-3 rounded-lg font-bold text-black border-b-4 border-yellow-700 active:border-0 active:translate-y-1">🟨 Amarilla</button>
                        <button onclick="eventoEquipo('TARJETA_ROJA', '{{ $partido->equipo_visitante }}')" class="bg-red-600 hover:bg-red-500 py-3 rounded-lg font-bold text-white border-b-4 border-red-800 active:border-0 active:translate-y-1">🟥 Roja</button>
                    </div>
                    <button onclick="eventoCambio('{{ $partido->equipo_visitante }}')" class="w-full bg-slate-700 hover:bg-slate-600 py-3 rounded-lg font-bold border border-slate-500">🔄 Cambio</button>
                </div>
            </div>
        </div>
    </div>

    {{-- SCRIPTS OPTIMIZADOS --}}
    <script>
        // VARIABLES GLOBALES
        let contLocal = {{ $partido->goles_local }};
        let contVisita = {{ $partido->goles_visitante }};

        // Carga segura del tiempo desde PHP (Entero)
        let segundosIniciales = parseInt("{{ $segundos }}");
        let tiempoCarga = Date.now();
        let intervalo = null;
        let estadoActual = "{{ $partido->estado }}";

        // Iniciar reloj si ya está corriendo
        if(estadoActual === 'en_curso') {
            iniciarCronometro();
            // Actualizar vista inicial inmediatamente
            actualizarRelojVisual(segundosIniciales);
        } else {
            // Si está pausado o no iniciado, mostrar 00:00
            actualizarRelojVisual(0);
        }

        // --- FUNCIONES DEL RELOJ ---
        function iniciarCronometro() {
            if (intervalo) return;
            actualizarEstadoVisual('en_curso');

            intervalo = setInterval(() => {
                // Cálculo robusto: Tiempo PHP + Tiempo Transcurrido JS
                const ahora = Date.now();
                const diferencia = Math.floor((ahora - tiempoCarga) / 1000);
                let totalSegundos = segundosIniciales + diferencia;

                actualizarRelojVisual(totalSegundos);
            }, 1000);
        }

        function actualizarRelojVisual(segundos) {
            // Protección contra negativos y decimales
            if(segundos < 0) segundos = 0;
            segundos = Math.floor(segundos);

            const min = Math.floor(segundos / 60).toString().padStart(2, '0');
            const sec = (segundos % 60).toString().padStart(2, '0');

            const el = document.getElementById('cronometro');
            if(el) el.innerText = `${min}:${sec}`;
        }

        function pausarCronometro() {
            if(intervalo) {
                clearInterval(intervalo);
                intervalo = null;
            }
        }

        function getMinutoActual() {
            const texto = document.getElementById('cronometro').innerText;
            const partes = texto.split(':');
            // Retornamos el minuto actual + 1 para registros de eventos (ej: minuto 0:30 es el 1')
            return parseInt(partes[0]) + 1;
        }

        // --- API Y CONTROL ---
        function controlPartido(tipo) {
            if(tipo === 'INICIO') {
                // Si iniciamos, reseteamos la marca de tiempo de JS para contar desde AHORA
                tiempoCarga = Date.now();
                // Si el partido estaba en 0, iniciamos desde 0.
                // Si quisieras reanudar un entretiempo, aquí habría lógica extra,
                // pero para este caso asumimos continuidad simple.
                iniciarCronometro();
            } else {
                pausarCronometro();
                if(tipo === 'ENTRETIEMPO') actualizarEstadoVisual('entretiempo');
                if(tipo === 'FIN') actualizarEstadoVisual('finalizado');
            }

            enviarApi({
                partido_id: {{ $partido->id }},
                tipo: tipo,
                minuto: getMinutoActual()
            });
        }

        function actualizarEstadoVisual(estado) {
            const div = document.getElementById('estadoPartido');
            if(estado === 'en_curso') {
                div.innerText = "EN JUEGO";
                div.className = "text-xl font-bold px-4 py-2 rounded bg-green-600 animate-pulse shadow-green-500/50";
            } else if (estado === 'entretiempo') {
                div.innerText = "ENTRETIEMPO";
                div.className = "text-xl font-bold px-4 py-2 rounded bg-yellow-600 shadow-yellow-500/50";
            } else {
                div.innerText = "FINALIZADO";
                div.className = "text-xl font-bold px-4 py-2 rounded bg-red-600 shadow-red-500/50";
            }
        }

        async function enviarApi(payload) {
            try {
                const response = await fetch("{{ route('evento.store') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(payload)
                });
                if(response.ok) {
                    const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
                    Toast.fire({ icon: 'success', title: 'Registrado' });
                }
            } catch (error) {
                console.error(error);
            }
        }

        // --- EVENTOS DE JUEGO ---
        async function eventoEquipo(tipo, equipo) {
            const { value: jugador } = await Swal.fire({
                title: tipo.replace('_', ' ') + ' - ' + equipo,
                input: 'text',
                inputPlaceholder: 'Nombre del Jugador o Nro',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Registrar'
            });

            if (jugador) {
                // Actualización Optimista del Marcador
                if(tipo === 'GOL') {
                    if(equipo === "{{ $partido->equipo_local }}") {
                        contLocal++;
                        document.getElementById('txtGolesLocal').innerText = contLocal;
                    } else {
                        contVisita++;
                        document.getElementById('txtGolesVisita').innerText = contVisita;
                    }
                }

                enviarApi({
                    partido_id: {{ $partido->id }},
                    tipo: tipo,
                    minuto: getMinutoActual(),
                    equipo: equipo,
                    jugador: jugador
                });
            }
        }

        async function eventoCambio(equipo) {
            const { value: formValues } = await Swal.fire({
                title: 'Cambio en ' + equipo,
                html: '<input id="swal-out" class="swal2-input" placeholder="Sale (Jugador/Nro)">' +
                      '<input id="swal-in" class="swal2-input" placeholder="Entra (Jugador/Nro)">',
                focusConfirm: false,
                confirmButtonText: 'Realizar Cambio',
                preConfirm: () => [document.getElementById('swal-out').value, document.getElementById('swal-in').value]
            });

            if (formValues && formValues[0] && formValues[1]) {
                enviarApi({
                    partido_id: {{ $partido->id }},
                    tipo: 'CAMBIO',
                    minuto: getMinutoActual(),
                    equipo: equipo,
                    jugador: `SALE: ${formValues[0]} -> ENTRA: ${formValues[1]}`
                });
            }
        }
    </script>
</body>
</html>
