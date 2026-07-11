<?php

use App\Models\PersonalBroadcast;
use App\Models\PersonalBroadcastRecipient;
use App\Models\User;

it('admin can create a personal broadcast and participant can mark it as viewed', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $participant = User::factory()->create(['role' => 'peserta']);

    $this->actingAs($admin);

    $response = $this->post(route('panitia.info.peserta.personal.store'), [
        'judul' => 'Pengingat penting',
        'konten' => 'Baca pesan ini',
        'recipient_ids' => [$participant->id],
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('personal_broadcasts', ['judul' => 'Pengingat penting']);

    $broadcast = PersonalBroadcast::where('judul', 'Pengingat penting')->firstOrFail();
    $this->assertDatabaseHas('personal_broadcast_recipients', [
        'personal_broadcast_id' => $broadcast->id,
        'user_id' => $participant->id,
    ]);

    $this->actingAs($participant);
    $viewResponse = $this->post(route('peserta.personal.broadcast.viewed', $broadcast->id));

    $viewResponse->assertOk();
    $recipient = PersonalBroadcastRecipient::where('personal_broadcast_id', $broadcast->id)
        ->where('user_id', $participant->id)
        ->first();

    $this->assertNotNull($recipient->viewed_at);
});
