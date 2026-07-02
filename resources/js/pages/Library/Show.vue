<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import type { Album, Track } from '@/types'

interface Props {
  album: Album & { tracks: Track[] }
}

defineProps<Props>()

const formatDuration = (seconds: number): string => {
  const mins = Math.floor(seconds / 60)
  const secs = seconds % 60
  return `${mins}:${secs.toString().padStart(2, '0')}`
}

const placeholderCover = 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 400 400%22%3E%3Crect fill=%22%23e5e7eb%22 width=%22400%22 height=%22400%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%239ca3af%22 font-family=%22sans-serif%22 font-size=%2232%22%3ENo Cover%3C/text%3E%3C/svg%3E'
</script>

<template>
  <div class="p-6 max-w-4xl mx-auto">
    <Link href="/library" class="text-blue-600 hover:text-blue-700 flex items-center gap-1 mb-6">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
      </svg>
      Back to Library
    </Link>

    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
      <div class="md:flex">
        <!-- Cover Art -->
        <div class="md:w-80 flex-shrink-0">
          <img
            :src="album.cover_path || placeholderCover"
            :alt="`${album.artist} - ${album.title}`"
            class="w-full h-auto aspect-square object-cover"
          />
        </div>

        <!-- Album Info -->
        <div class="p-6 flex flex-col justify-between flex-grow">
          <div>
            <p class="text-gray-600 text-lg">{{ album.artist }}</p>
            <h1 class="text-4xl font-bold mt-2 mb-4">{{ album.title }}</h1>

            <div class="space-y-2 text-gray-700">
              <p v-if="album.year" class="flex items-center gap-2">
                <span class="font-semibold">Year:</span>
                {{ album.year }}
              </p>
              <p class="flex items-center gap-2">
                <span class="font-semibold">Tracks:</span>
                {{ album.tracks.length }}
              </p>
              <p v-if="album.tracks.length > 0" class="flex items-center gap-2">
                <span class="font-semibold">Total Duration:</span>
                {{
                  formatDuration(
                    album.tracks.reduce((sum, t) => sum + (t.duration || 0), 0)
                  )
                }}
              </p>
            </div>
          </div>

          <button class="mt-6 inline-flex items-center gap-2 px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add to SD Card
          </button>
        </div>
      </div>

      <!-- Track List -->
      <div class="border-t">
        <div class="px-6 py-4 bg-gray-50 border-b">
          <h2 class="text-xl font-bold">Tracks</h2>
        </div>

        <div v-if="album.tracks.length === 0" class="p-6 text-center text-gray-500">
          No tracks found
        </div>

        <div v-else class="divide-y">
          <div
            v-for="(track, index) in album.tracks"
            :key="track.id"
            class="px-6 py-4 hover:bg-gray-50 transition flex items-center gap-4"
          >
            <span class="text-gray-400 font-semibold w-8 text-right">
              {{ track.track_number || index + 1 }}
            </span>
            <div class="flex-grow">
              <p class="font-semibold">{{ track.title }}</p>
              <p class="text-sm text-gray-600">{{ track.format?.toUpperCase() || 'Unknown' }}</p>
            </div>
            <div class="text-right">
              <p class="text-gray-700">{{ formatDuration(track.duration || 0) }}</p>
              <p v-if="track.bitrate" class="text-xs text-gray-500">{{ track.bitrate }}kbps</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
