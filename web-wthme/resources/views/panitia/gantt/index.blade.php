@extends('layouts.app')

@section('content')
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        /* Glassmorphism Global Utility */
        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 20px;
            box-shadow: 0 8px 32px 0 rgba(0, 47, 69, 0.1);
        }

        .gantt-container {
            display: flex;
            flex-direction: row;
            gap: 2rem;
        }

        .calendar-col {
            flex: 1.6;
        }

        .list-col {
            flex: 1;
        }

        /* Custom Scrollbar for list */
        .agenda-scroll {
            max-height: 800px;
            overflow-y: auto;
            padding-right: 5px;
        }

        .agenda-scroll::-webkit-scrollbar {
            width: 5px;
        }

        .agenda-scroll::-webkit-scrollbar-thumb {
            background: rgba(0, 47, 69, 0.2);
            border-radius: 10px;
        }

        @media (max-width: 1024px) {
            .gantt-container {
                flex-direction: column;
            }

            .header-section {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 1.5rem !important;
            }

            .filter-form {
                width: 100% !important;
            }
        }
    </style>

    <div
        style="min-height:calc(100vh - 64px); padding:2rem 1.5rem; background: linear-gradient(135deg, #e0decd 0%, #bdd1d3 100%); font-family: 'Inter', sans-serif;">
        <div style="max-width:1200px; margin:0 auto;">

            {{-- Back Link --}}
            <a href="{{ route('panitia.index') }}"
                style="color:#002f45; opacity:0.6; text-decoration:none; font-size:0.9rem; font-weight:600; display:inline-flex; align-items:center; margin-bottom:2rem; transition: 0.3s;">
                <span style="margin-right:8px;">←</span> Kembali ke Dashboard
            </a>

            <div class="header-section"
                style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2.5rem;">
                <div>
                    <h1
                        style="font-family:'Playfair Display',serif; color:#002f45; font-size:2.5rem; font-weight:800; margin:0; letter-spacing:-0.02em;">
                        Ganttchart <span style="color:#6b705c; font-style:italic;">WTHME 2025</span>
                    </h1>

                    @if ($countdownTarget)
                        <div id="countdownBox" class="glass-card"
                            style="margin-top: 15px; background: rgba(0, 47, 69, 0.9); color: #d2c296; padding: 12px 24px; display: inline-flex; align-items: center; gap: 15px; border: none;">
                            <div style="border-right: 1px solid rgba(210,194,150,0.3); padding-right: 15px;">
                                <div
                                    style="font-size: 0.65rem; font-weight: 900; text-transform: uppercase; letter-spacing: 1.5px; opacity: 0.8;">
                                    H-Day Countdown</div>
                                <div style="font-size: 0.85rem; font-weight: 700; color: #fff;">{{ $namaEventH }}</div>
                            </div>
                            <span id="timerText"
                                style="font-family: 'Courier New', monospace; font-weight: 900; font-size: 1.4rem; color: #fff;">00:00:00</span>
                        </div>
                    @else
                        <p style="color:#002f45; opacity:0.7; margin-top:8px; font-weight:500;">Manajemen timeline kegiatan
                            panitia secara terpadu.</p>
                    @endif
                </div>

                {{-- Filter Glass --}}
                <form action="" method="GET" class="filter-form glass-card"
                    style="display:flex; gap:15px; align-items:center; padding:12px 20px;">
                    <div style="display:flex; flex-direction:column;">
                        <label
                            style="font-size:0.65rem; font-weight:800; color:#002f45; text-transform:uppercase; margin-bottom:2px; opacity:0.6;">Bulan
                            Mulai</label>
                        <select name="start" onchange="this.form.submit()"
                            style="border:none; outline:none; font-weight:700; color:#002f45; background:transparent; cursor:pointer; font-size:1rem;">
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}"
                                    {{ request('start', date('n')) == $m ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div style="width:1px; background:rgba(0,47,69,0.1); height:30px;"></div>
                    <div style="display:flex; flex-direction:column;">
                        <label
                            style="font-size:0.65rem; font-weight:800; color:#002f45; text-transform:uppercase; margin-bottom:2px; opacity:0.6;">Tahun</label>
                        <input type="number" name="year" value="{{ $year }}" onchange="this.form.submit()"
                            style="border:none; outline:none; font-weight:700; width:65px; color:#002f45; background:transparent; font-size:1rem;">
                    </div>
                </form>
            </div>

            <div class="gantt-container">
                {{-- KOLOM KIRI: KALENDER --}}
                <div class="calendar-col">
                    <div style="display:flex; flex-direction:column; gap:2.5rem;">
                        @foreach ($months as $m)
                            @php
                                $displayYear = request('start') + $loop->index > 12 ? $year + 1 : $year;
                                $dt = \Carbon\Carbon::create($displayYear, $m, 1);
                                $daysInMonth = $dt->daysInMonth;
                                $firstDay = $dt->dayOfWeek;
                            @endphp
                            <div class="glass-card" style="overflow:hidden;">
                                <div
                                    style="background: rgba(0, 47, 69, 0.03); color:#002f45; padding:1.2rem; text-align:center; font-weight:800; font-size:1.1rem; border-bottom: 1px solid rgba(0,47,69,0.05); text-transform:uppercase; letter-spacing:1px;">
                                    {{ $dt->format('F Y') }}
                                </div>
                                <table style="width:100%; border-collapse:collapse; text-align:center;">
                                    <tr
                                        style="color:#002f45; font-weight:800; font-size:0.7rem; opacity:0.5; text-transform:uppercase;">
                                        <td style="padding:15px 10px;">Min</td>
                                        <td style="padding:15px 10px;">Sen</td>
                                        <td style="padding:15px 10px;">Sel</td>
                                        <td style="padding:15px 10px;">Rab</td>
                                        <td style="padding:15px 10px;">Kam</td>
                                        <td style="padding:15px 10px;">Jum</td>
                                        <td style="padding:15px 10px;">Sab</td>
                                    </tr>
                                    <tr>
                                        @for ($i = 0; $i < $firstDay; $i++)
                                            <td></td>
                                        @endfor
                                        @for ($day = 1; $day <= $daysInMonth; $day++)
                                            @php
                                                $curr = \Carbon\Carbon::create($displayYear, $m, $day)->format('Y-m-d');
                                                $event = $kegiatans
                                                    ->filter(
                                                        fn($k) => $curr >= $k->tanggal_mulai &&
                                                            $curr <= $k->tanggal_selesai,
                                                    )
                                                    ->first();

                                                $bg = 'transparent';
                                                $txt = '#002f45';
                                                $border = 'none';
                                                if ($event) {
                                                    if ($event->status == 'hijau') {
                                                        $bg = '#70ad47';
                                                    }
                                                    if ($event->status == 'kuning') {
                                                        $bg = '#f1c40f';
                                                    }
                                                    if ($event->status == 'merah') {
                                                        $bg = '#2e5496';
                                                        $txt = 'white';
                                                    }
                                                }
                                            @endphp
                                            <td style="padding:5px;">
                                                <div onclick="handleDateClick('{{ $curr }}', {{ $event ? json_encode($event) : 'null' }})"
                                                    style="height:50px; display:flex; align-items:center; justify-content:center; border-radius:12px; background:{{ $bg }}; color:{{ $txt }}; font-weight:800; cursor:pointer; transition: 0.2s; font-size:0.95rem; @if (!$event) border:1px solid rgba(0,47,69,0.05); @endif"
                                                    onmouseover="this.style.transform='scale(1.05)'"
                                                    onmouseout="this.style.transform='scale(1)'">
                                                    {{ $day }}
                                                </div>
                                            </td>
                                            @if (($day + $firstDay) % 7 == 0)
                                    </tr>
                                    <tr>
                        @endif
                        @endfor
                        </tr>
                        </table>
                    </div>
                    @endforeach

                    {{-- Legend --}}
                    <div class="glass-card"
                        style="padding:1.2rem; display:flex; gap:2rem; justify-content:center; flex-wrap:wrap; border:none;">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div style="width:14px; height:14px; background:#f1c40f; border-radius:4px;"></div><span
                                style="font-size:0.75rem; font-weight:700; color:#002f45;">Belum</span>
                        </div>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div style="width:14px; height:14px; background:#70ad47; border-radius:4px;"></div><span
                                style="font-size:0.75rem; font-weight:700; color:#002f45;">Sudah</span>
                        </div>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div style="width:14px; height:14px; background:#2e5496; border-radius:4px;"></div><span
                                style="font-size:0.75rem; font-weight:700; color:#002f45;">Hari H</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: LIST AGENDA --}}
            <div class="list-col">
                <div class="glass-card"
                    style="border:none; background: rgba(0, 47, 69, 0.9); border-radius: 20px 20px 0 0; padding:1.2rem; color:#d2c296; font-weight:900; text-align:center; letter-spacing:1px;">
                    DETAIL AGENDA
                </div>
                <div class="agenda-scroll">
                    @foreach ($months as $m)
                        @php
                            $displayYear = request('start') + $loop->index > 12 ? $year + 1 : $year;
                            $eventsMonth = $kegiatans->filter(
                                fn($q) => \Carbon\Carbon::parse($q->tanggal_mulai)->month == $m &&
                                    \Carbon\Carbon::parse($q->tanggal_mulai)->year == $displayYear,
                            );
                        @endphp
                        <div class="glass-card"
                            style="border-top:none; margin-bottom:1rem; border-radius: 0 0 20px 20px; overflow:hidden;">
                            <div
                                style="background:rgba(0,47,69,0.03); color:#002f45; padding:0.8rem; text-align:center; font-weight:800; border-bottom:1px solid rgba(0,47,69,0.05); font-size:0.8rem;">
                                {{ date('F Y', mktime(0, 0, 0, $m, 1, $displayYear)) }}</div>
                            <table style="width:100%; border-collapse:collapse;">
                                @forelse($eventsMonth as $item)
                                    <tr onclick="handleDateClick('{{ $item->tanggal_mulai }}', {{ json_encode($item) }})"
                                        style="cursor:pointer; transition:0.2s;"
                                        onmouseover="this.style.background='rgba(0,47,69,0.02)'"
                                        onmouseout="this.style.background='transparent'">
                                        <td
                                            style="padding:1.2rem; text-align:center; font-weight:900; width:60px; color:#2e5496; font-size:1.1rem;">
                                            {{ \Carbon\Carbon::parse($item->tanggal_mulai)->day }}</td>
                                        <td style="padding:1.2rem; font-weight:600; color:#002f45;">
                                            {{ $item->nama_kegiatan }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td
                                            style="padding:2.5rem; text-align:center; color:#002f45; opacity:0.4; font-size:0.85rem; font-style:italic;">
                                            Tidak ada agenda bulan ini</td>
                                    </tr>
                                @endforelse
                            </table>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    </div>

    {{-- MODAL GLASS --}}
    <div id="modalGantt"
        style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,47,69,0.4); z-index:9999; align-items:center; justify-content:center; padding:20px; backdrop-filter: blur(10px);">
        <div class="glass-card"
            style="background:white; padding:2.5rem; width:100%; max-width:400px; border:none; box-shadow: 0 20px 50px rgba(0,0,0,0.2);">
            <h3 id="modalTitle"
                style="font-family:'Playfair Display',serif; color:#002f45; font-size:1.8rem; margin-bottom:0.5rem; font-weight:800;">
                Update Timeline</h3>
            <p id="displayDate" style="color:#2e5496; font-weight:800; margin-bottom:2rem; font-size:1rem;"></p>

            <form id="ganttForm" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <input type="hidden" name="tanggal_mulai" id="hidden_mulai">
                <input type="hidden" name="tanggal_selesai" id="hidden_selesai">

                <div id="containerNama" style="margin-bottom:1.2rem;">
                    <label
                        style="display:block; font-size:0.7rem; font-weight:800; color:#002f45; margin-bottom:8px; opacity:0.6; text-transform:uppercase;">Nama
                        Kegiatan</label>
                    <input type="text" name="nama_kegiatan" id="in_nama"
                        style="width:100%; padding:1rem; border:1px solid rgba(0,47,69,0.1); border-radius:12px; font-weight:600; background:#f9f9f9; color:#002f45;">
                </div>

                <div style="margin-bottom:2rem;">
                    <label
                        style="display:block; font-size:0.7rem; font-weight:800; color:#002f45; margin-bottom:8px; opacity:0.6; text-transform:uppercase;">Status
                        Progress</label>
                    <select name="status" id="in_status"
                        style="width:100%; padding:1rem; border:1px solid rgba(0,47,69,0.1); border-radius:12px; font-weight:600; background:#f9f9f9; color:#002f45; cursor:pointer;">
                        <option value="kuning">🟡 Belum Terlaksana</option>
                        <option value="hijau">🟢 Sudah Terlaksana</option>
                        <option value="merah">🔵 Hari H Kegiatan</option>
                    </select>
                </div>

                <div style="display:flex; flex-direction:column; gap:10px;">
                    <button type="submit"
                        style="background:#002f45; color:#d2c296; border:none; padding:1.1rem; border-radius:12px; font-weight:800; cursor:pointer; letter-spacing:1px;">SIMPAN
                        PERUBAHAN</button>
                    <button type="button" id="btnHapus" onclick="submitDelete()"
                        style="display:none; background:transparent; color:#e74c3c; border:none; padding:0.5rem; font-weight:700; cursor:pointer; font-size:0.85rem;">Hapus
                        Agenda</button>
                    <button type="button" onclick="closeModal()"
                        style="background:transparent; color:#666; border:none; padding:0.5rem; font-weight:600; cursor:pointer; font-size:0.85rem;">Tutup</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        {{-- Script Countdown --}}
        @if ($countdownTarget)
            (function() {
                const targetDate = new Date("{{ $countdownTarget }} 00:00:00").getTime();
                const x = setInterval(function() {
                    const now = new Date().getTime();
                    const distance = targetDate - now;
                    if (distance < 0) {
                        clearInterval(x);
                        document.getElementById("timerText").innerHTML = "EVENT STARTED";
                        return;
                    }
                    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                    document.getElementById("timerText").innerHTML = days + "d " + hours + "h " + minutes +
                        "m " + seconds + "s";
                }, 1000);
            })();
        @endif

        function handleDateClick(date, event) {
            @if (!in_array(auth()->user()->role, ['korlap', 'admin']))
                return;
            @endif
            const modal = document.getElementById('modalGantt');
            modal.style.display = 'flex';
            document.getElementById('displayDate').innerText = "📅 " + date;
            document.getElementById('hidden_mulai').value = date;
            document.getElementById('hidden_selesai').value = date;

            if (event) {
                document.getElementById('modalTitle').innerText = "Update Status";
                document.getElementById('ganttForm').action = "/panitia/gantt/" + event.id;
                document.getElementById('formMethod').value = "PUT";
                document.getElementById('containerNama').style.display = "none";
                document.getElementById('in_status').value = event.status;
                document.getElementById('btnHapus').style.display = "block";
            } else {
                document.getElementById('modalTitle').innerText = "Tambah Agenda";
                document.getElementById('ganttForm').action = "{{ route('panitia.gantt.store') }}";
                document.getElementById('formMethod').value = "POST";
                document.getElementById('containerNama').style.display = "block";
                document.getElementById('in_nama').value = "";
                document.getElementById('in_status').value = "kuning";
                document.getElementById('btnHapus').style.display = "none";
            }
        }

        function submitDelete() {
            if (confirm('Hapus agenda ini secara permanen?')) {
                let f = document.createElement('form');
                f.method = 'POST';
                f.action = document.getElementById('ganttForm').action;
                f.innerHTML = '@csrf @method('DELETE')';
                document.body.appendChild(f);
                f.submit();
            }
        }

        function closeModal() {
            document.getElementById('modalGantt').style.display = 'none';
        }
        window.onclick = function(e) {
            if (e.target == document.getElementById('modalGantt')) closeModal();
        }
    </script>
@endsection
