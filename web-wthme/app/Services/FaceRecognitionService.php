<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;

class FaceRecognitionService
{
    private string $baseUrl;
    private Client $client;

    public function __construct()
    {
        $this->baseUrl = config('services.face_api.url', 'http://localhost:8001');
        $this->client  = new Client([
            'timeout'         => 30,
            'connect_timeout' => 5,
        ]);
    }

    /**
     * Identifikasi wajah dari satu foto.
     * Return array: ['found' => bool, 'user_id' => int|null, 'confidence' => float|null, 'reason' => string|null]
     */
    public function identifyFace(UploadedFile $photo): array
    {
        try {
            $response = $this->client->post("{$this->baseUrl}/identify", [
                'multipart' => [
                    [
                        'name'     => 'photo',
                        'contents' => fopen($photo->getRealPath(), 'r'),
                        'filename' => 'capture.jpg',
                        'headers'  => ['Content-Type' => 'image/jpeg'],
                    ],
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);

        } catch (ConnectException $e) {
            return ['found' => false, 'reason' => 'Server face recognition tidak bisa dijangkau. Pastikan FastAPI berjalan.'];
        } catch (\Exception $e) {
            return ['found' => false, 'reason' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Cek apakah FastAPI server hidup.
     */
    public function isHealthy(): bool
    {
        try {
            $response = $this->client->get("{$this->baseUrl}/health");
            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Hapus data wajah user dari FastAPI.
     */
    public function deleteFace(int $userId): bool
    {
        try {
            $this->client->delete("{$this->baseUrl}/register/{$userId}");
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}