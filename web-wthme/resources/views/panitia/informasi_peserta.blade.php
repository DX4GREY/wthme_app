@extends('layouts.app')

@section('content')
    <div
        style="min-height: 100vh; background: linear-gradient(135deg, #f8f9fa 0%, #e0decd 100%); padding: 4rem 1.5rem; font-family: 'Inter', sans-serif;">
        <div style="max-width: 900px; margin: 0 auto;">

            {{-- Header Section --}}
            <div style="margin-bottom: 2.5rem; animation: fadeInDown 0.8s ease-out;">
                <a href="{{ route('panitia.index') }}"
                    style="text-decoration: none; color: #002f45; font-weight: 700; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 8px; opacity: 0.7; transition: 0.3s;"
                    onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.7'">
                    ⬅ Kembali ke Dashboard
                </a>
                <h1
                    style="font-family:'Playfair Display',serif; color:#002f45; font-size:2.5rem; font-weight:800; margin:0; letter-spacing:-0.02em;">
                    Broadcast <span style="color:#6b705c; font-style:italic;">Peserta</span>
                </h1>
                <p style="color: #002f45; opacity: 0.6; font-size: 1.1rem;">Siarkan pengumuman atau tautan penting ke portal
                    peserta.</p>
            </div>

            {{-- Glassmorphism Form Card --}}
            <div
                style="background: rgba(255, 255, 255, 0.4); 
                    backdrop-filter: blur(15px); 
                    -webkit-backdrop-filter: blur(15px); 
                    padding: 2.5rem; 
                    border-radius: 2rem; 
                    border: 1px solid rgba(255, 255, 255, 0.6); 
                    box-shadow: 0 20px 40px rgba(0,0,0,0.05);
                    margin-bottom: 3.5rem;
                    animation: fadeInUp 0.8s ease-out;">

                <h4 style="margin-top: 0; color: #002f45; margin-bottom: 1.5rem; font-weight: 800; letter-spacing: -0.5px;">
                    Buat Broadcast Baru</h4>

                <form action="{{ route('panitia.info.peserta.store') }}" method="POST">
                    @csrf
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                        <div>
                            <label
                                style="display:block; font-size: 0.75rem; font-weight: 800; color: #002f45; margin-bottom: 0.6rem; opacity: 0.8;">JUDUL
                                INFORMASI</label>
                            <input type="text" name="judul" placeholder="Contoh: Pengingat Atribut" required
                                style="width: 100%; padding: 0.8rem 1rem; border-radius: 1rem; border: 1px solid rgba(255,255,255,0.8); background: rgba(255,255,255,0.5); outline: none; transition: 0.3s;"
                                onfocus="this.style.background='white'; this.style.borderColor='#002f45'">
                        </div>
                        <div>
                            <label
                                style="display:block; font-size: 0.75rem; font-weight: 800; color: #002f45; margin-bottom: 0.6rem; opacity: 0.8;">KATEGORI</label>
                            <select name="kategori" required
                                style="width: 100%; padding: 0.8rem 1rem; border-radius: 1rem; border: 1px solid rgba(255,255,255,0.8); background: rgba(255,255,255,0.5); outline: none; transition: 0.3s;"
                                onfocus="this.style.background='white';">
                                <option value="Pengumuman">📢 Pengumuman Tekstual</option>
                                <option value="Materi">📚 Materi/Modul</option>
                                <option value="Link Utama">🔗 Link Utama/Drive</option>
                            </select>
                        </div>
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <label
                            style="display:block; font-size: 0.75rem; font-weight: 800; color: #002f45; margin-bottom: 0.6rem; opacity: 0.8;">ISI
                            PENGUMUMAN (TEKS)</label>
                        <textarea name="konten" rows="4" placeholder="Tulis pesan detail di sini..."
                            style="width: 100%; padding: 1rem; border-radius: 1rem; border: 1px solid rgba(255,255,255,0.8); background: rgba(255,255,255,0.5); outline: none; transition: 0.3s; font-family: inherit; resize: vertical;"
                            onfocus="this.style.background='white';"></textarea>
                    </div>

                    <div style="margin-bottom: 2rem;">
                        <label
                            style="display:block; font-size: 0.75rem; font-weight: 800; color: #002f45; margin-bottom: 0.6rem; opacity: 0.8;">URL
                            LINK (OPSIONAL)</label>
                        <input type="url" name="url_link" placeholder="https://drive.google.com/..."
                            style="width: 100%; padding: 0.8rem 1rem; border-radius: 1rem; border: 1px solid rgba(255,255,255,0.8); background: rgba(255,255,255,0.5); outline: none; transition: 0.3s;"
                            onfocus="this.style.background='white';">
                    </div>

                    <button type="submit"
                        style="width: 100%; background: #002f45; color: white; border: none; padding: 1rem; border-radius: 1rem; font-weight: 800; cursor: pointer; transition: 0.3s; box-shadow: 0 10px 20px rgba(0,47,69,0.2);"
                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 15px 25px rgba(0,47,69,0.3)'"
                        onmouseout="this.style.transform='translateY(0)'">
                        Siarkan ke Seluruh Peserta 🚀
                    </button>
                </form>
            </div>

            {{-- List Section --}}
            <h4
                style="color: #002f45; margin-bottom: 1.5rem; font-weight: 800; display: flex; align-items: center; gap: 10px;">
                <span style="display: inline-block; width: 30px; height: 2px; background: #002f45;"></span>
                Informasi yang Sedang Tayang
            </h4>

            <div style="display: flex; flex-direction: column; gap: 1.2rem;">
                @forelse($infos as $info)
                    <div style="background: rgba(255, 255, 255, 0.7); 
                            backdrop-filter: blur(10px); 
                            padding: 1.5rem; 
                            border-radius: 1.5rem; 
                            display: flex; 
                            justify-content: space-between; 
                            align-items: center; 
                            border: 1px solid white;
                            box-shadow: 0 10px 20px rgba(0,0,0,0.02);
                            transition: 0.3s;"
                        onmouseover="this.style.transform='scale(1.01)'; this.style.background='rgba(255, 255, 255, 0.9)'"
                        onmouseout="this.style.transform='scale(1)'; this.style.background='rgba(255, 255, 255, 0.7)'">

                        <div style="flex: 1; padding-right: 1.5rem;">
                            <span
                                style="font-size: 0.65rem; font-weight: 900; background: #002f45; color: white; padding: 0.3rem 0.8rem; border-radius: 2rem; letter-spacing: 0.5px;">
                                {{ strtoupper($info->kategori) }}
                            </span>
                            <h5 style="margin: 0.8rem 0 0.4rem 0; color: #002f45; font-size: 1.1rem; font-weight: 700;">
                                {{ $info->judul }}</h5>

                            @if ($info->konten)
                                <p style="margin: 0; font-size: 0.85rem; color: #002f45; opacity: 0.7; line-height: 1.4;">
                                    {{ Str::limit($info->konten, 100) }}
                                </p>
                            @endif

                            @if ($info->url_link)
                                <p style="margin: 5px 0 0 0; font-size: 0.75rem; color: #d2c296; font-weight: 700;">🔗
                                    {{ Str::limit($info->url_link, 50) }}</p>
                            @endif
                        </div>

                        <form action="{{ route('panitia.info.peserta.destroy', $info->id) }}" method="POST"
                            style="margin: 0;">
                            @csrf @method('DELETE')
                            <button type="submit" onclick="return confirm('Hapus informasi ini dari portal peserta?')"
                                style="background: #ef4444; color: white; border: none; width: 40px; height: 40px; border-radius: 12px; cursor: pointer; transition: 0.3s; display: flex; align-items: center; justify-content: center;"
                                onmouseover="this.style.background='#dc2626'" onmouseout="this.style.background='#ef4444'">
                                🗑️
                            </button>
                        </form>
                    </div>
                @empty
                    <div
                        style="text-align: center; padding: 4rem; background: rgba(255,255,255,0.3); border-radius: 2rem; border: 2px dashed rgba(0,47,69,0.1);">
                        <p style="color: #002f45; opacity: 0.5; font-weight: 600;">Belum ada informasi yang disiarkan.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- <style>
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeInDown {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style> --}}
@endsection
