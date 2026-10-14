<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use Database\Seeders\Data\DemoContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Demo chat: private conversations parent<->teacher, student<->teacher and
 * admin<->teacher, plus one class group. Messages are rebuilt on every run; the
 * last message of each conversation is unread for the other participants.
 */
class ChatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $private = [
            [['dewi', 'budi'], [
                ['dewi', 'Selamat sore Pak Budi, bagaimana perkembangan Raka di kelas Python?'],
                ['budi', 'Sore Bu Dewi. Raka cepat paham, minggu ini dia sudah bisa membuat fungsi sendiri.'],
                ['dewi', 'Alhamdulillah. Apakah ada latihan yang bisa dikerjakan di rumah?'],
                ['budi', 'Ada, latihan di modul pertemuan ini. Cukup 15 menit sehari.'],
                ['dewi', 'Baik Pak, terima kasih banyak.'],
            ]],
            [['raka', 'budi'], [
                ['raka', 'Pak, tugas game Scratch saya error, kucingnya tidak mau bergerak.'],
                ['budi', 'Coba cek blok "when green flag clicked" sudah terpasang belum?'],
                ['raka', 'Oh iya lupa. Sudah jalan Pak!'],
                ['budi', 'Mantap! Jangan lupa tunjukkan di kelas Sabtu ya.'],
            ]],
            [['hendra', 'sari'], [
                ['hendra', 'Bu Sari, Clarissa izin tidak masuk Sabtu ini karena acara keluarga.'],
                ['sari', 'Baik Pak, nanti materinya saya kirim lewat chat.'],
                ['hendra', 'Terima kasih Bu.'],
            ]],
            [['eko', 'rizky'], [
                ['eko', 'Mas Rizky, bisa minta referensi tambahan soal list comprehension?'],
                ['rizky', 'Bisa, saya kirim ringkasannya setelah kelas Kamis.'],
                ['eko', 'Siap, terima kasih.'],
            ]],
            [['admin', 'budi'], [
                ['admin', 'Pak Budi, jadwal Scratch Kids A minggu depan tetap Sabtu 09.00 ya.'],
                ['budi', 'Siap, tetap.'],
            ]],
        ];

        foreach ($private as [$pair, $messages]) {
            $conversation = Conversation::getPrivateConversation(DemoContext::user($pair[0])->id, DemoContext::user($pair[1])->id);
            $this->fill($conversation, $messages);
        }

        $class = DemoContext::classByCode('SCR-A');
        $group = Conversation::firstOrCreate(['type' => 'class', 'class_id' => $class->id], ['name' => 'Grup '.$class->name, 'is_active' => true]);

        foreach (['budi', 'sari', 'raka', 'clarissa', 'keisha'] as $key) {
            $group->addParticipant(DemoContext::user($key)->id, in_array($key, ['budi', 'sari'], true));
        }

        $this->fill($group, [
            ['budi', 'Halo semua! Sabtu ini kita lanjut membuat game kejar-kejaran.'],
            ['keisha', 'Siap Pak!'],
            ['sari', 'Jangan lupa bawa ide karakter game kalian ya.'],
        ]);

        $this->command?->info('Demo chat: '.(count($private) + 1).' conversations.');
    }

    /**
     * Replace the conversation's messages with the given ones (oldest first).
     *
     * @param  array<int, array{0: string, 1: string}>  $messages  [sender key, text]
     */
    private function fill(Conversation $conversation, array $messages): void
    {
        $conversation->messages()->withTrashed()->forceDelete();
        $start = Carbon::now()->subDays(count($messages))->setTime(16, 0);

        foreach ($messages as $index => [$senderKey, $text]) {
            $message = new Message([
                'conversation_id' => $conversation->id,
                'sender_id' => DemoContext::user($senderKey)->id,
                'content' => $text,
                'type' => 'text',
            ]);
            $message->created_at = $message->updated_at = $start->copy()->addDays($index)->addMinutes(7 * $index);
            $message->save();
        }

        $lastSender = DemoContext::user(end($messages)[0])->id;

        // Reason: the created hook counted every message; keep only the last one unread.
        ConversationParticipant::where('conversation_id', $conversation->id)->update(['unread_count' => 0, 'last_read_at' => now()->subDay()]);
        ConversationParticipant::where('conversation_id', $conversation->id)->where('user_id', '!=', $lastSender)->update(['unread_count' => 1]);
    }
}
