@component('mail::message')
# Undangan Tes Psikologi

Halo {{ $candidate->name }},

Anda telah diundang untuk mengikuti tes **{{ $testName }}**.

**Periode aktif tes:**
{{ now()->format('d M Y, H:i') }} hingga {{ now()->addDays($expiryDays)->format('d M Y, H:i') }}
(Waktu sesuai zona waktu Asia/Jakarta)

@component('mail::button', ['url' => $testLink])
Mulai Tes Sekarang
@endcomponent

@if($customMessage)
**Pesan dari Admin:**
{{ $customMessage }}
@endif

**Petunjuk Teknis:**
1. Pastikan koneksi internet stabil
2. Gunakan browser terbaru (Chrome, Firefox, atau Edge)
3. Tes harus diselesaikan dalam satu sesi
4. Jawaban akan otomatis tersimpan
5. Tes akan otomatis berhenti ketika waktu habis

Jika Anda mengalami kendala teknis, silakan hubungi tim HRD.

Terima kasih,
Tim Rekrutmen {{ config('app.name') }}
@endcomponent