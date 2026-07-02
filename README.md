# 🎵 Ordinatio — DAC Music Library Manager

A production-ready web application for managing music libraries and syncing them to Digital Audio Converters (DACs) via SD card.

## Overview

Ordinatio is a comprehensive music library management system that lets you:
- **Scan** your music collection and extract metadata
- **Browse** albums with full-text search
- **Organize** playlists from your favorite tracks
- **Monitor** SD card state and synchronization
- **Sync** albums and playlists to your DAC's SD card

Perfect for music enthusiasts who want a simple, web-based interface to manage large audio collections.

## 🚀 Quick Start

### Prerequisites
- Docker & Docker Compose
- Music files in FLAC, MP3, WAV, AAC, OGG, WMA, or Opus format

### Installation

1. **Clone the repository:**
```bash
git clone https://github.com/doguskysilva/ordinatio.git
cd ordinatio
```

2. **Start the application:**
```bash
docker compose up
```

3. **Access the web interface:**
```
http://localhost:8000
```

4. **Login with default credentials:**
```
Email: test@example.com
Password: password
```

## 📋 Features

### Phase 1: Docker + Foundation ✅
- Production-ready Docker Compose setup (3 services)
- PHP 8.4 with SQLite database
- Laravel 13 + Inertia v3 + Vue 3
- Authentication via Laravel Fortify

### Phase 2: Database Modeling ✅
- 6 tables: albums, tracks, playlists, playlist_tracks, card_states, sync_logs
- Complete ORM relationships
- Database migrations and seeders

### Phase 3: Library Scanner ✅
- Recursive directory scanning
- Audio metadata extraction (ID3, FLAC, WAV, AAC, OGG, WMA, Opus)
- Queue-based background jobs
- Real FLAC test fixtures included

### Phase 4: Library UI ✅
- Album grid with cover art
- Full-text search by artist/album
- Pagination support
- Album detail page with track list

### Phase 5: Playlist Management ✅
- Create, edit, and delete playlists
- Add/remove tracks with search
- Track reordering
- Form request validation

### Phase 6: SD Card State ✅
- Monitor what's on your SD card
- Compare desired vs. actual state
- Storage usage calculation
- Sync status indicators

### Phase 7: Synchronization ✅
- Sync albums to SD: `Albums/Artist - Title/`
- Sync playlists to SD: `Playlists/Name/` with sequential numbering
- Automatic cleanup of old files
- Sync progress tracking

## 🏗️ Architecture

### Backend Stack
- **Framework:** Laravel 13 + PHP 8.4
- **Database:** SQLite with migrations
- **Queue:** Database queue driver
- **WebSockets:** Laravel Reverb (configured)
- **Audio:** getid3 library for metadata extraction

### Frontend Stack
- **SPA Framework:** Inertia v3 + Vue 3
- **Styling:** Tailwind CSS v4
- **Components:** Shadcn/ui components
- **Type Safety:** TypeScript
- **Forms:** Inertia form handling

### Infrastructure
- **Containerization:** Docker Compose
- **Services:** 
  - `ordinatio-app` (PHP-FPM) on port 8000
  - `ordinatio-worker` (queue worker)
  - `ordinatio-reverb` (WebSocket) on port 8080

## 📁 Project Structure

```
ordinatio/
├── app/
│   ├── Http/Controllers/
│   │   ├── LibraryController.php
│   │   ├── PlaylistController.php
│   │   ├── CardController.php
│   │   └── SyncController.php
│   ├── Jobs/
│   │   ├── ScanLibrary.php
│   │   └── SyncToCard.php
│   ├── Models/
│   │   ├── Album.php
│   │   ├── Track.php
│   │   ├── Playlist.php
│   │   ├── CardState.php
│   │   └── SyncLog.php
│   └── Services/
│       ├── MetadataReader.php
│       ├── LibraryScanner.php
│       └── CardReader.php
├── resources/js/
│   ├── pages/
│   │   ├── Library/
│   │   ├── Playlists/
│   │   ├── Card/
│   │   └── Dashboard.vue
│   ├── components/
│   │   └── ui/ (Tailwind components)
│   └── types/
├── routes/
│   ├── web.php (authenticated routes)
│   └── settings.php (user settings)
├── tests/
│   ├── Feature/
│   │   └── Controllers/
│   └── Unit/
│       └── Services/
├── database/
│   ├── migrations/
│   ├── factories/
│   └── seeders/
└── docker-compose.yml
```

## 🔌 API Routes

### Library Management
- `GET /library` — Browse albums
- `GET /library/{album}` — View album details
- `POST /library/scan` — Start library scan
- `POST /library/{album}/add-to-card` — Mark album for sync

### Playlist Management
- `GET /playlists` — List playlists
- `POST /playlists` — Create playlist
- `GET /playlists/{playlist}` — View playlist details
- `POST /playlists/{playlist}/tracks` — Add track
- `DELETE /playlists/{playlist}/tracks/{track}` — Remove track
- `DELETE /playlists/{playlist}` — Delete playlist

### SD Card & Synchronization
- `GET /card` — View card state
- `POST /sync` — Start synchronization
- `GET /sync/{syncLog}` — View sync details

## 📊 Workflow

1. **Scan Library**
   - Click "Scan Library" button on Library page
   - Background job reads music files
   - Extracts metadata (artist, title, duration, bitrate, etc.)
   - Updates database with albums and tracks

2. **Browse & Organize**
   - Browse albums with search and pagination
   - Create playlists and add tracks
   - View album details with complete track information

3. **Mark for Sync**
   - From album detail page, click "Add to SD Card"
   - Album is marked for synchronization
   - Redirects to Card page showing sync status

4. **Synchronize**
   - On Card page, click "Sync to Card" button
   - Background job copies files to `/tmp/sdcard`
   - Creates organized folder structure:
     - `Albums/Artist - Album Title/track.flac`
     - `Playlists/Playlist Name/001 - Track Title.flac`
   - Updates sync log with status

5. **Listen on DAC**
   - Mount SD card on your DAC
   - Browse albums and playlists
   - Play your music

## 🧪 Testing

The project includes 72 comprehensive tests covering all features:

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/Controllers/LibraryControllerTest.php

# Run with coverage
php artisan test --coverage
```

**Test Coverage:**
- Unit tests for services (MetadataReader, LibraryScanner, CardReader)
- Feature tests for all controllers
- Integration tests for complete workflows
- Real FLAC audio fixtures for testing

## 🎨 UI Components

All pages use reusable components from the Shadcn/ui library:

- **Button** — Interactive actions with variants
- **Input** — Text fields with validation
- **Dialog** — Modals for confirmations and forms
- **Card** — Content containers with headers
- **Sidebar** — Navigation with quick access
- **Breadcrumb** — Navigation trails

## 🌙 Dark Mode

Full dark mode support across all pages with semantic Tailwind colors:
- Automatic light/dark detection
- Consistent accent colors in both modes
- Hover effects that work in dark mode

## 📦 Dependencies

### PHP Packages
- `laravel/framework` v13
- `inertiajs/inertia-laravel` v3
- `laravel/fortify` v1
- `laravel/reverb` v1
- `james-heinrich/getid3` (audio metadata)
- `pestphp/pest` v4 (testing)

### JavaScript Packages
- `vue` v3
- `@inertiajs/vue3` v3
- `tailwindcss` v4
- `@laravel/vite-plugin-wayfinder`

## 🔐 Security

- CSRF protection on all forms
- Validated input through Form Requests
- SQL injection prevention via Eloquent ORM
- XSS protection via Vue templating
- Password hashing with Laravel Fortify

## 🛠️ Development

### Available Commands

```bash
# Start development server
docker compose up

# Run migrations
php artisan migrate

# Seed database with test user
php artisan db:seed

# Format code with Pint
vendor/bin/pint

# Run tests
php artisan test

# Build frontend assets
npm run build

# Watch for changes
npm run dev
```

### Environment Configuration

Create a `.env` file (or use `.env.example`):

```env
APP_NAME=Ordinatio
DB_CONNECTION=sqlite
DB_DATABASE=database/app.db
QUEUE_CONNECTION=database
BROADCAST_CONNECTION=reverb
```

## 📝 Configuration

### Music Library Path
Mount your music library to `/var/music` in docker-compose.yml:
```yaml
volumes:
  - ~/music:/var/music
```

### SD Card Mount Point
Configure SD card path in docker-compose.yml (default: `/tmp/sdcard`):
```yaml
volumes:
  - /tmp/sdcard:/var/sdcard
```

## 🐛 Troubleshooting

### Scan doesn't find files
- Ensure music library is mounted correctly
- Check file format is supported (FLAC, MP3, WAV, etc.)
- Verify files have proper metadata tags

### Sync fails to copy files
- Check `/tmp/sdcard` directory exists and is writable
- Verify source files are readable
- Check available disk space

### Dark mode not working
- Clear browser cache
- Ensure system dark mode preference is set
- Check browser supports prefers-color-scheme

## 📄 License

This project is open source and available under the MIT License.

## 🤝 Contributing

Contributions are welcome! Please feel free to submit pull requests or open issues for bugs and feature requests.

## 📚 Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Inertia.js Guide](https://inertiajs.com)
- [Vue 3 Documentation](https://vuejs.org)
- [Tailwind CSS](https://tailwindcss.com)
- [getid3 Library](https://www.getid3.org)

## 🙏 Acknowledgments

Built with modern web technologies and best practices for audio enthusiasts who want complete control over their music library.

---

**Version:** 1.0.0 (MVP)  
**Last Updated:** July 2026  
**Repository:** https://github.com/doguskysilva/ordinatio
