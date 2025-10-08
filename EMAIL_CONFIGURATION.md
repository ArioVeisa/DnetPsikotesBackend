# Email Notification Configuration

## Overview
Sistem email notification telah diimplementasikan untuk mengirim email otomatis ke `arioveisa@gmail.com` ketika ada kandidat yang menyelesaikan tes psikotes.

## Features
- ✅ Email template yang menarik dengan informasi lengkap
- ✅ Email otomatis terkirim saat tes selesai
- ✅ Informasi kandidat, tes, dan skor
- ✅ Link langsung ke hasil tes
- ✅ Logging untuk monitoring

## Email Template
Email notification berisi:
- Nama kandidat
- Email kandidat
- Posisi kandidat
- Nama tes
- Target posisi
- Skor tes
- Waktu selesai
- Link ke hasil tes

## API Endpoints

### 1. Test Email Functionality
```bash
POST /api/test-email-functionality
Authorization: Bearer {token}
```
Mengirim email test dengan data dummy.

### 2. Send Single Test Completion Email
```bash
POST /api/send-test-completion-email
Authorization: Bearer {token}
Content-Type: application/json

{
    "candidate_test_id": 1
}
```

### 3. Send Bulk Test Completion Emails
```bash
POST /api/send-bulk-test-completion-emails
Authorization: Bearer {token}
```
Mengirim email untuk semua tes yang sudah completed.

## Automatic Email Trigger
Email akan otomatis terkirim ketika:
1. Kandidat menyelesaikan tes
2. Method `markAsCompleted()` dipanggil pada model `CandidateTest`
3. Status berubah menjadi `completed`

## Configuration

### Environment Variables
Tambahkan konfigurasi berikut di file `.env`:

```env
# Email Notification Configuration
TEST_COMPLETION_NOTIFICATION_EMAIL=arioveisa@gmail.com
FRONTEND_URL=http://localhost:3000

# Mail Configuration (Production)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="DWP Psikotes System"
```

### Current Setup (Testing)
Saat ini menggunakan `log` driver untuk testing. Email akan tersimpan di log file.

### Production Setup
Untuk production, konfigurasi SMTP di `.env` seperti contoh di atas.

### Gmail Configuration
1. Aktifkan 2-Factor Authentication
2. Generate App Password
3. Gunakan App Password di `MAIL_PASSWORD`

## Files Created/Modified

### New Files:
- `app/Mail/TestCompletionNotification.php` - Email Mailable class
- `app/Services/TestCompletionEmailService.php` - Email service
- `app/Http/Controllers/TestEmailController.php` - Email controller
- `resources/views/emails/test-completion-notification.blade.php` - Email template

### Modified Files:
- `app/Models/CandidateTest.php` - Added email trigger
- `routes/api.php` - Added email routes

## Testing Results
✅ Test email functionality: SUCCESS
✅ Bulk email sending: SUCCESS (2 emails sent)
✅ Email template rendering: SUCCESS

## Email Recipient
Semua email dikirim ke email yang dikonfigurasi di `TEST_COMPLETION_NOTIFICATION_EMAIL` environment variable.

**Default**: `arioveisa@gmail.com`
**Customizable**: Ubah di file `.env` dengan variable `TEST_COMPLETION_NOTIFICATION_EMAIL=your-email@domain.com`

## Next Steps
1. Configure SMTP settings for production
2. Test dengan email real
3. Monitor email delivery
4. Add email preferences/opt-out functionality if needed
