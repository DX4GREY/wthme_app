<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;

describe('secure uploads middleware', function () {
    it('allows safe uploads within limits', function () {
        Route::middleware('secure.uploads')->post('/secure-upload-allowed', function () {
            return response()->json(['ok' => true]);
        });

        $file = UploadedFile::fake()->create('document.pdf', 120, 'application/pdf');

        $response = $this->postJson('/secure-upload-allowed', [
            'file' => $file,
        ]);

        $response->assertOk();
        $response->assertJson(['ok' => true]);
    });

    it('rejects dangerous upload extensions', function () {
        Route::middleware('secure.uploads')->post('/secure-upload-blocked', function () {
            return response()->json(['ok' => true]);
        });

        $file = UploadedFile::fake()->create('shell.php', 120, 'application/x-php');

        $response = $this->postJson('/secure-upload-blocked', [
            'file' => $file,
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('message', 'Upload contains forbidden file type.');
    });
});
