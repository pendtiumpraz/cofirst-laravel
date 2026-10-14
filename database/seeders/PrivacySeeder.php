<?php

namespace Database\Seeders;

use App\Models\Privacy\ConsentPurpose;
use App\Models\Privacy\ConsentRecord;
use App\Models\Privacy\PolicyDocument;
use App\Models\User;
use App\Services\Privacy\ConsentService;
use App\Services\Privacy\PolicyService;
use Illuminate\Database\Seeder;

/**
 * Base data of the PDP consent module, safe in every environment and idempotent:
 *
 * 1. The register of processing purposes (UU 27/2022 Pasal 20) — existing rows are never
 *    overwritten, so admin edits survive.
 * 2. Version 1.0 of the privacy notice, terms, cookie policy, children's notice and DPA
 *    template (texts in database/seeders/privacy/*.md) when a type has no version yet.
 *    In production v1.0 of the privacy notice and terms asks existing accounts to accept
 *    once (they never saw a notice before); demo/test data stays browsable.
 * 3. Outside production, when demo users already exist (e.g. `db:seed --class=PrivacySeeder`
 *    after the demo seed), sample consent records labelled as demo data.
 */
class PrivacySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedPurposes();
        $this->seedPolicies();

        if (DemoSeeder::allowed() && User::query()->exists()) {
            $this->seedDemoConsents();
        }

        PolicyService::flushCache();
    }

    /** Create missing purposes; never overwrite admin edits. */
    private function seedPurposes(): void
    {
        foreach (ConsentPurpose::defaults() as $key => $attributes) {
            ConsentPurpose::query()->firstOrCreate(['key' => $key], $attributes + ['version' => 1, 'is_active' => true]);
        }
    }

    /** Version 1.0 of each policy type that has no version yet. */
    private function seedPolicies(): void
    {
        $policies = app(PolicyService::class);
        $titles = [
            'privacy_notice' => ['Kebijakan Privasi', 'Bagaimana Coding First mengumpulkan, memakai, melindungi dan menghapus data pribadi siswa, orang tua, pengajar dan pengunjung, serta cara Anda menggunakan hak sebagai subjek data.'],
            'terms' => ['Syarat & Ketentuan', 'Aturan penggunaan layanan, pendaftaran kelas, pembayaran, keamanan anak dan penyelesaian masalah.'],
            'cookie_policy' => ['Kebijakan Cookie', 'Cookie dan penyimpanan lokal yang kami pakai, tujuannya, masa simpannya, dan cara mengatur pilihan Anda.'],
            'children_notice' => ['Privasi untuk Anak & Orang Tua', 'Penjelasan singkat dengan bahasa sederhana tentang data anak di Coding First dan peran orang tua/wali.'],
            'dpa_template' => ['Perjanjian Pemrosesan Data Pribadi', 'Templat perjanjian antara Coding First sebagai Pengendali dan penyedia layanan sebagai Prosesor Data Pribadi.'],
        ];

        foreach ($titles as $type => [$title, $summary]) {
            if (PolicyDocument::query()->where('type', $type)->exists()) {
                continue;
            }

            $body = $policies->defaultBody($type);
            if ($body === null) {
                $this->command?->warn("Teks bawaan untuk {$type} tidak ditemukan.");

                continue;
            }

            PolicyDocument::create([
                'type' => $type,
                'revision' => 1,
                'version' => '1.0',
                'title' => $title,
                'summary' => $summary,
                'body' => $body,
                'changelog' => 'Versi pertama, disusun mengikuti UU No. 27 Tahun 2022 tentang Pelindungan Data Pribadi dan PP No. 33 Tahun 2026.',
                'status' => 'published',
                // Reason: real accounts in production have never seen a notice, so they confirm once.
                'requires_reacceptance' => app()->environment('production') && in_array($type, PolicyService::ACCEPTANCE_TYPES, true),
                'effective_at' => now(),
                'published_at' => now(),
            ]);
        }
    }

    /**
     * Demo consent records for existing demo children (labelled as demo evidence).
     */
    private function seedDemoConsents(): void
    {
        if (ConsentRecord::query()->exists()) {
            return;
        }

        $consents = app(ConsentService::class);
        $children = User::role('student')->with(['profile', 'parents'])->get()
            ->filter(fn (User $u) => $consents->isChild($u) && $u->parents->isNotEmpty());

        foreach ($children->values() as $index => $child) {
            $parent = $child->parents->first();
            $evidence = ['source' => 'demo_seed', 'note' => 'Data demo — bukan persetujuan nyata.'];

            $consents->grant($child, 'child_data_processing', 'parental', $parent, $evidence, null, $parent->email, $parent->name);

            // Reason: a realistic mix — about half the families allow publication.
            if ($index % 2 === 0) {
                $consents->grant($child, 'media_publication', 'parental', $parent, $evidence, null, $parent->email, $parent->name);
                $consents->grant($child, 'leaderboard_public', 'parental', $parent, $evidence, null, $parent->email, $parent->name);
            }
        }
    }
}
