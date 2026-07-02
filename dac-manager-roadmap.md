# DAC Manager — Implementation Roadmap

> Stack: Laravel + Inertia + Vue 3 + SQLite + Docker
> Goal: Functional MVP to manage a music repository and sync with DAC via SD card

---

## Phase 1 — Docker + Laravel Foundation

**Goal:** Application running in container, authentication working, database configured.

### Checklist

- [x] Create `docker-compose.yml` with `app` and `worker` services
- [x] Configure `Dockerfile` for PHP + required extensions (pdo_sqlite, pcntl, getid3)
- [x] Map volumes:
  - `~/music` → `/var/music` (read-only)
  - `/mnt/sdcard` → `/var/sdcard` (read-write)
  - `./database` → `/var/database`
- [x] Install Laravel with Vue + Inertia starter kit
- [x] Configure SQLite pointing to `/var/database/app.db`
- [x] Configure queue driver to `database`
- [x] Configure Laravel Reverb for WebSocket
- [x] Validate login/logout working in browser

### Validation Criteria
> `docker compose up` → access `localhost` → login screen appears → can authenticate with default user and password

---

## Phase 2 — Database Modeling

**Goal:** Complete schema created and tested via migrations.

### Checklist

- [x] Migration `albums`: `id`, `artist`, `title`, `year`, `cover_path`, `folder_path`, `created_at`, `updated_at`
- [x] Migration `tracks`: `id`, `album_id`, `title`, `track_number`, `duration`, `file_path`, `format`, `bitrate`
- [x] Migration `playlists`: `id`, `name`, `created_at`, `updated_at`
- [x] Migration `playlist_tracks`: `id`, `playlist_id`, `track_id`, `position`
- [x] Migration `card_states`: `id`, `type` (album|playlist), `reference_id`, `folder_name`, `synced_at`
- [x] Migration `sync_logs`: `id`, `status`, `started_at`, `finished_at`, `summary` (json)
- [x] Models with relationships:
  - `Album` hasMany `Track`
  - `Playlist` belongsToMany `Track` via `playlist_tracks` (with `position`)
  - `SyncLog` hasMany `CardState`
- [x] Factories for all models
- [x] Unit tests for relationships and factories

### Validation Criteria
> `php artisan migrate` with no errors + all model unit tests passing with 100% coverage

---

## Phase 3 — Library Scanner

**Goal:** Application can read the music repository and populate the database.

### Checklist

- [ ] Install `james-heinrich/getid3` via Composer
- [ ] Create `MetadataReader` service:
  - Reads ID3/FLAC/etc tags from a file
  - Returns normalized struct: `title`, `artist`, `album`, `year`, `track_number`, `duration`, `format`, `bitrate`
- [ ] Create `LibraryScanner` service:
  - Recursively scans `/var/music`
  - Detects `Artist/Album/tracks` folder structure
  - Extracts cover art when available
- [ ] Create `ScanLibrary` Job:
  - Uses `LibraryScanner` to find albums and tracks
  - Upserts into database (no duplicates on re-scan)
  - Emits progress events via Reverb
- [ ] Create `LibraryController`:
  - `index()` → Inertia render of library page
  - `scan()` → dispatches `ScanLibrary` job
- [ ] Unit tests for `MetadataReader` and `LibraryScanner` (with audio file fixtures)
- [ ] Integration tests for `ScanLibrary` job

### Validation Criteria
> Click "Scan library" in UI → job runs → albums and tracks appear in database → library page lists albums correctly

---

## Phase 4 — Library UI

**Goal:** Visualize the master repository in the interface.

### Checklist

- [ ] Page `Library/Index.vue`:
  - Album grid with cover art, artist and title
  - Search field (filters by artist or album)
  - "Scan library" button with real-time progress feedback via Reverb
- [ ] Page `Library/Show.vue` (album detail):
  - Large cover art, metadata
  - Track list in order with duration
  - "Add to SD card" button
- [ ] Real-time scan progress indicator (WebSocket)
- [ ] Feature tests for controllers (LibraryController)

### Validation Criteria
> Library displays all scanned albums → clicking an album shows its tracks → search filters correctly

---

## Phase 5 — Playlist Management

**Goal:** Create, edit and organize playlists from repository tracks.

### Checklist

- [ ] `PlaylistController`: index, store, update, destroy
- [ ] `PlaylistTrackController`: store, destroy, reorder
- [ ] Page `Playlists/Index.vue`:
  - Playlist list with name and track count
  - Create new playlist button
- [ ] Page `Playlists/Show.vue`:
  - Playlist track list with drag-and-drop reordering
  - Remove track button
  - Add tracks button (opens search modal from repository)
- [ ] Track search modal:
  - Search by title, artist or album
  - Add track directly to playlist
- [ ] Unit tests for track reordering logic
- [ ] Feature tests for all playlist endpoints

### Validation Criteria
> Create playlist → search tracks → add → reorder via drag-and-drop → save → playlist persists correctly

---

## Phase 6 — SD Card State

**Goal:** Application can see what is on the card and compare it against the repository.

### Checklist

- [ ] Create `CardReader` service:
  - Reads folder structure from `/var/sdcard`
  - Identifies folders as album or playlist by path (`Albums/` vs `Playlists/`)
  - Returns current state snapshot
- [ ] `CardController`:
  - `index()` → current card state (what is there)
  - `diff()` → comparison between `card_states` (desired) and physical card (actual)
- [ ] Page `Card/Index.vue`:
  - What is on the card (albums and playlists)
  - Used / available space
  - Visual indication of what is outdated or missing
  - "Sync" button
- [ ] Unit tests for `CardReader`
- [ ] Feature tests for `CardController`

### Validation Criteria
> Card page lists what is on the SD → diff correctly shows what is missing, what is extra, and what is outdated

---

## Phase 7 — Synchronization

**Goal:** Copy albums and playlists to the SD card with real-time progress.

### Checklist

- [ ] Create `SyncToCard` Job:
  - Reads `card_states` to know what should be on the card
  - Removes from SD what should no longer be there
  - Copies albums to `Albums/Artist - Title/`
  - Copies playlist tracks to `Playlists/Name/` with sequential numbering
  - Updates `card_states` and `sync_logs` on completion
  - Emits progress events via Reverb (current file, % complete)
- [ ] `SyncController`:
  - `store()` → dispatches job, returns `sync_log` id
  - `show()` → state of ongoing sync
- [ ] Progress UI in `Card/Index.vue`:
  - Real-time progress bar
  - Name of file currently being copied
  - Summary on completion (added, removed, errors)
- [ ] Error handling (corrupted file, SD full, permission denied)
- [ ] Unit tests for `SyncToCard` job (with fake filesystem)
- [ ] Integration tests for complete sync flow

### Validation Criteria
> Mark album for card → click sync → progress appears in real time → files are on SD in correct structure → DAC can navigate and play

---

## Phase 8 — Polish and Final MVP

**Goal:** Stable, fully tested application ready for daily use.

### Checklist

- [ ] 100% test coverage (unit + integration)
- [ ] Global error handling in UI (toast notifications)
- [ ] Loading states on all async operations
- [ ] Basic settings page:
  - Repository path (in case volume changes)
  - Username and password (change default credentials)
- [ ] `README.md` with setup and usage instructions
- [ ] `make setup` script or similar for first-run (migrate, seed default user, etc.)
- [ ] Review scan performance on large collections (indexing, chunking)
- [ ] Validate behavior when SD is not mounted (clear message in UI)

### Validation Criteria
> Complete end-to-end flow works: scan → library → playlist → card → sync → DAC plays the music

---

## Post-MVP Backlog (does not block MVP)

- [ ] LLM integration via Prism PHP for playlist generation by prompt
- [ ] Metadata enrichment via LLM (fill in missing artist, year, genre)
- [ ] Sync history with detailed log
- [ ] Multiple SD card profile support
- [ ] Track preview in browser (streaming via Laravel)
- [ ] External playlist import (M3U)
- [ ] Collection statistics (total tracks, formats, bitrates)
