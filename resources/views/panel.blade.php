<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Árbitro | {{ $partido->equipo_local }} vs {{ $partido->equipo_visitante }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-800 text-white min-h-screen p-6">

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
                <div class="text-5xl font-mono font-bold text-yellow-400 bg-black px-6 py-2 rounded-lg border border-gray-600" id="cronometro">
                    00:00
                </div>

                {{-- ESTADO --}}
                @php
                    $claseEstado = match($partido->estado) {
                        'en_curso' => 'bg-green-600 animate-pulse',
                        'entretiempo' => 'bg-yellow-600',
                        'finalizado' => 'bg-red-600',
                        default => 'bg-gray-700'
                    };
                    $textoEstado = match($partido->estado) {
                        'en_curso' => 'EN JUEGO',
                        'entretiempo' => 'ENTRETIEMPO',
                        'finalizado' => 'FINALIZADO',
                        default => 'PROGRAMADO'
                    };
                @endphp
                <div class="text-xl font-bold px-4 py-2 rounded {{ $claseEstado }}" id="estadoPartido">
                    {{ $textoEstado }}
                </div>
            </div>

            {{-- BOTONES DE CONTROL DE TIEMPO --}}
            <div class="flex justify-center gap-4 mt-8">
                <button onclick="controlPartido('INICIO')" class="bg-green-600 hover:bg-green-500 text-white font-bold py-3 px-6 rounded shadow-lg transform active:scale-95 transition">
                    ▶ INICIAR
                </button>
                <button onclick="controlPartido('ENTRETIEMPO')" class="bg-yellow-600 hover:bg-yellow-500 text-white font-bold py-3 px-6 rounded shadow-lg transform active:scale-95 transition">
                    ⏸ ENTRETIEMPO
                </button>
                <button onclick="controlPartido('FIN')" class="bg-red-600 hover:bg-red-500 text-white font-bold py-3 px-6 rounded shadow-lg transform active:scale-95 transition">
                    ⏹ FINALIZAR
                </button>
            </div>
            <div class="mt-8 pt-6 border-t border-gray-700 flex justify-center">
            <form action="{{ route('partido.reiniciar', $partido->id) }}" method="POST" onsubmit="return confirm('⚠️ ¿ESTÁS SEGURO? \n\nEsto borrará todo el historial de eventos, reiniciará el cronómetro y pondrá el marcador 0-0.\n\nEsta acción no se puede deshacer.');">
                @csrf
                <button type="submit" class="group flex items-center gap-2 text-red-500 hover:text-white border border-red-900 hover:bg-red-600 px-4 py-2 rounded transition-all duration-300 text-sm font-bold tracking-widest uppercase">
                    <span class="group-hover:animate-ping">⚠️</span>
                    Reiniciar Partido y Borrar Historial
                    <span class="group-hover:animate-ping">⚠️</span>
                </button>
            </form>
        </div>


        </div>

        {{-- PANELES DE EQUIPOS --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

            {{-- EQUIPO LOCAL --}}
            <div class="bg-blue-900/30 p-6 rounded-2xl border border-blue-500/30">
                <h2 class="text-2xl font-bold text-center text-blue-400 mb-6 flex justify-between items-center px-2">
                    <span class="truncate w-2/3 text-left">{{ $partido->equipo_local }}</span>
                    <span id="txtGolesLocal" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-4xl shadow-inner border border-blue-400">
                        {{ $partido->goles_local }}
                    </span>
                </h2>
                <div class="space-y-4">
                    <button onclick="eventoEquipo('GOL', '{{ $partido->equipo_local }}')" class="w-full bg-green-600 hover:bg-green-500 py-4 rounded-xl font-bold text-xl shadow-lg border-b-4 border-green-800 active:border-0 active:translate-y-1 transition">
                        ⚽ GOL LOCAL
                    </button>
                    <div class="grid grid-cols-2 gap-4">
                        <button onclick="eventoEquipo('TARJETA_AMARILLA', '{{ $partido->equipo_local }}')" class="bg-yellow-500 hover:bg-yellow-400 py-3 rounded-lg font-bold text-black">🟨 Amarilla</button>
                        <button onclick="eventoEquipo('TARJETA_ROJA', '{{ $partido->equipo_local }}')" class="bg-red-600 hover:bg-red-500 py-3 rounded-lg font-bold">🟥 Roja</button>
                    </div>
                    <button onclick="eventoCambio('{{ $partido->equipo_local }}')" class="w-full bg-gray-700 hover:bg-gray-600 py-3 rounded-lg font-bold border border-gray-500">🔄 Cambio</button>
                </div>
            </div>

            {{-- EQUIPO VISITA --}}
            <div class="bg-indigo-900/30 p-6 rounded-2xl border border-indigo-500/30">
                <h2 class="text-2xl font-bold text-center text-indigo-400 mb-6 flex justify-between items-center px-2">
                    <span class="truncate w-2/3 text-left">{{ $partido->equipo_visitante }}</span>
                    <span id="txtGolesVisita" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-4xl shadow-inner border border-indigo-400">
                        {{ $partido->goles_visitante }}
                    </span>
                </h2>
                <div class="space-y-4">
                    <button onclick="eventoEquipo('GOL', '{{ $partido->equipo_visitante }}')" class="w-full bg-green-600 hover:bg-green-500 py-4 rounded-xl font-bold text-xl shadow-lg border-b-4 border-green-800 active:border-0 active:translate-y-1 transition">
                        ⚽ GOL VISITA
                    </button>
                    <div class="grid grid-cols-2 gap-4">
                        <button onclick="eventoEquipo('TARJETA_AMARILLA', '{{ $partido->equipo_visitante }}')" class="bg-yellow-500 hover:bg-yellow-400 py-3 rounded-lg font-bold text-black">🟨 Amarilla</button>
                        <button onclick="eventoEquipo('TARJETA_ROJA', '{{ $partido->equipo_visitante }}')" class="bg-red-600 hover:bg-red-500 py-3 rounded-lg font-bold">🟥 Roja</button>
                    </div>
                    <button onclick="eventoCambio('{{ $partido->equipo_visitante }}')" class="w-full bg-gray-700 hover:bg-gray-600 py-3 rounded-lg font-bold border border-gray-500">🔄 Cambio</button>
                </div>
            </div>

        </div>
    </div>

    {{-- SCRIPTS (Versión Corregida y Optimizada) --}}
    <script>
        // 1. VARIABLES INICIALES DESDE BLADE
        let contLocal = {{ $partido->goles_local }};
        let contVisita = {{ $partido->goles_visitante }};

        // RELOJ INTELIGENTE
        // Recibimos los segundos que ya pasaron desde el inicio (calculados por PHP/Carbon)
        let segundosIniciales = {{ $segundos ?? 0 }};
        let tiempoCarga = Date.now(); // Marca de tiempo del navegador
        let intervalo = null;
        let estadoActual = "{{ $partido->estado }}";

        // Iniciar automáticamente si está en curso
        if(estadoActual === 'en_curso') {
            iniciarCronometro();
        }

        // 2. FUNCIONES DEL RELOJ
        function iniciarCronometro() {
            if (intervalo) return;
            actualizarEstadoVisual('en_curso');

            intervalo = setInterval(() => {
                // Cálculo de deriva: (Ahora - TiempoCarga) + SegundosQueYaTraiaElServidor
                const ahora = Date.now();
                const diferencia = Math.floor((ahora - tiempoCarga) / 1000);
                const totalSegundos = segundosIniciales + diferencia;

                actualizarRelojVisual(totalSegundos);
            }, 1000);
        }

        function actualizarRelojVisual(segundos) {
            if(segundos < 0) segundos = 0; // Corregido 'seconds' a 'segundos'

            // Agregamos Math.floor en ambas líneas para asegurar que sean enteros
            const min = Math.floor(segundos / 60).toString().padStart(2, '0');
            const sec = Math.floor(segundos % 60).toString().padStart(2, '0');

            const el = document.getElementById('cronometro');
            if(el) el.innerText = `${min}:${sec}`;
        }

        function pausarCronometro() {
            clearInterval(intervalo);
            intervalo = null;
        }

        function getMinutoActual() {
            // Obtenemos el minuto visual + 1
            const texto = document.getElementById('cronometro').innerText;
            const partes = texto.split(':');
            return parseInt(partes[0]) + 1;
        }

        // 3. COMUNICACIÓN CON EL SERVIDOR
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
                    Toast.fire({ icon: 'success', title: 'Guardado' });
                } else {
                    throw new Error('Error Server');
                }
            } catch (error) {
                Swal.fire('Error', 'No se pudo guardar', 'error');
            }
        }

        // 4. BOTONES
        function controlPartido(tipo) {
            if(tipo === 'INICIO') {
                tiempoCarga = Date.now();
                segundosIniciales = 0; // Ojo: Si reanudas un partido pausado, esto debería ser diferente, pero para tu caso básico sirve.
                iniciarCronometro();
                actualizarEstadoVisual('en_curso');
            } else if(tipo === 'ENTRETIEMPO') {
                pausarCronometro();
                actualizarEstadoVisual('entretiempo');
            } else if(tipo === 'FIN') {
                pausarCronometro();
                actualizarEstadoVisual('finalizado');
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
                div.className = "text-xl font-bold px-4 py-2 rounded bg-green-600 animate-pulse";
            } else if (estado === 'entretiempo') {
                div.innerText = "ENTRETIEMPO";
                div.className = "text-xl font-bold px-4 py-2 rounded bg-yellow-600";
            } else {
                div.innerText = "FINALIZADO";
                div.className = "text-xl font-bold px-4 py-2 rounded bg-red-600";
            }
        }

        async function eventoEquipo(tipo, equipo) {
            const { value: jugador } = await Swal.fire({
                title: tipo + ' para ' + equipo,
                input: 'text',
                inputPlaceholder: 'Jugador...',
                showCancelButton: true
            });

            if (jugador) {
                // Optimista
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
                title: 'Cambio ' + equipo,
                html: '<input id="swal-out" class="swal2-input" placeholder="Sale"><input id="swal-in" class="swal2-input" placeholder="Entra">',
                focusConfirm: false,
                preConfirm: () => [document.getElementById('swal-out').value, document.getElementById('swal-in').value]
            });

            if (formValues) {
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
