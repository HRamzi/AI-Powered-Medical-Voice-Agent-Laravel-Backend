# AI-Powered-Medical-Voice-Agent-Laravel-Backend
Laravel 11 backend for the AI-powered medical voice assistant SaaS. Handles real-time consultations, AI integration (AssemblyAI, OpenAI, Wapi.ai), Clerk & Stripe webhooks, and WebSocket audio processing.

# AI-Powered Medical Voice Agent – Laravel Backend

This is the Laravel 11 backend for the **AI-Powered Medical Voice Agent SaaS**. It acts as the core API layer and orchestrator for real-time AI-driven medical consultations, including:

- 🔐 User authentication (Clerk webhooks)
- 🧠 AI-driven medical suggestions (OpenAI)
- 🗣️ Real-time voice transcription (AssemblyAI)
- 🧾 Medical report generation
- 🎙️ Voice response delivery (Wapi.ai)
- 💳 Subscription billing (Clerk + Stripe)
- 🔄 WebSocket server for bi-directional audio streaming
- 🧱 PostgreSQL database with Eloquent ORM

---

## 🔧 Tech Stack

- **Laravel 11** (API only)
- **PostgreSQL** (Eloquent ORM)
- **WebSockets** (Soketi + Laravel Echo)
- **Clerk** (user management & webhooks)
- **Stripe** (subscription billing)
- **AssemblyAI** (real-time STT)
- **OpenAI** (medical reasoning)
- **Wapi.ai** (voice synthesis)
- **Docker** (production ready)

---

## 📁 Project Structure

```
ai-medical-voice-backend/
├── app/
│   ├── Http/Controllers/
│   │   ├── API/
│   │   ├── Webhooks/
│   │   └── WebSocket/
│   ├── Models/
│   ├── Services/
│   │   ├── AI/
│   │   ├── Audio/
│   │   └── Payment/
│   └── Events/
├── database/
│   ├── migrations/
│   └── seeders/
├── routes/
│   ├── api.php
│   ├── web.php
│   └── channels.php
└── config/
```

---

## 🚀 Getting Started

### Prerequisites
- PHP 8.2+
- Composer
- PostgreSQL
- Node.js (for WebSocket server)

### Installation

1. **Clone & Install**
```bash
git clone https://github.com/HRamzi/AI-Powered-Medical-Voice-Agent-Laravel-Backend.git
cd AI-Powered-Medical-Voice-Agent-Laravel-Backend
composer install
```

2. **Environment Setup**
```bash
cp .env.example .env
php artisan key:generate
```

3. **Database Setup**
```bash
php artisan migrate
php artisan db:seed
```

4. **Start Services**
```bash
php artisan serve
php artisan queue:work
```

---

## 🔗 API Endpoints

- `POST /api/auth/webhook` - Clerk user webhooks
- `POST /api/billing/webhook` - Stripe billing webhooks
- `POST /api/consultation/start` - Start AI consultation
- `WebSocket /ws` - Real-time audio streaming
- `GET /api/reports/{id}` - Download medical reports

---

## 🧠 AI Integration Flow

1. **User Input** → AssemblyAI (Speech-to-Text)
2. **Transcript** → OpenAI (Medical Analysis)
3. **AI Response** → Wapi.ai (Text-to-Speech)
4. **Audio Output** → WebSocket (Real-time delivery)

---

## 📊 Database Schema

- `users` - User profiles (synced via Clerk)
- `consultations` - Medical consultation sessions
- `conversations` - AI chat history
- `medical_reports` - Generated reports
- `subscriptions` - Billing information

---

## 🔧 Configuration

Update your `.env` file with:

```env
# Database
DB_CONNECTION=pgsql
DB_DATABASE=ai_medical_voice_db

# AI Services
OPENAI_API_KEY=your_openai_key
ASSEMBLYAI_API_KEY=your_assemblyai_key
WAPIAI_API_KEY=your_wapi_ai_key

# Authentication & Billing
CLERK_SECRET_KEY=your_clerk_secret
STRIPE_SECRET_KEY=your_stripe_secret

# WebSocket
PUSHER_HOST=127.0.0.1
PUSHER_PORT=6001
```

---

## 🐳 Docker Deployment

```bash
docker-compose up -d
```

---

## 📝 License

MIT License - see [LICENSE](LICENSE) file for details.

---

**Built with ❤️ for revolutionizing medical consultations through AI**
