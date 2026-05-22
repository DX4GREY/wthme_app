<?php

namespace App\Http\Controllers;

use App\Services\FaceRecognitionService;
use Illuminate\Http\Request;

class FaceController extends Controller
{
    public function __construct(private FaceRecognitionService $faceService) {}

    public function registerForm()
    {
        return view('peserta.face.register', ['user' => auth()->user()]);
    }

    public function registerStore(Request $request)
    {
        set_time_limit(120);

        $request->validate([
            'photos'   => 'required|array|min:1|max:3',
            'photos.*' => 'image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $user  = auth()->user();
        $files = $request->file('photos');

        $client    = new \GuzzleHttp\Client(['timeout' => 60, 'connect_timeout' => 5]);
        $multipart = [];

        foreach ($files as $i => $photo) {
            $multipart[] = [
                'name'     => 'photos',
                'contents' => fopen($photo->getRealPath(), 'r'),
                'filename' => "face_{$i}.jpg",
                'headers'  => ['Content-Type' => 'image/jpeg'],
            ];
        }

        try {
            $response = $client->post(
                config('services.face_api.url') . "/register/{$user->id}",
                ['multipart' => $multipart]
            );

            $result = json_decode($response->getBody()->getContents(), true);

        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            return back()->with('error', 'Tidak bisa terhubung ke server face recognition. Pastikan FastAPI sedang berjalan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }

        if (isset($result['status']) && $result['status'] === 'ok') {
            $user->update([
                'face_registered'    => true,
                'face_registered_at' => now(),
            ]);

            return back()->with('success',
                "✅ Wajah berhasil didaftarkan dari {$result['registered']} foto!"
            );
        }

        return back()->with('error',
            $result['detail'] ?? 'Gagal mendaftarkan wajah. Pastikan wajah terlihat jelas dan cukup cahaya.'
        );
    }
}