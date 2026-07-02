<script setup lang="ts">
import { ref } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import type { Album, Track } from '@/types'

interface Props {
  album: Album & { tracks: Track[] }
}

const props = defineProps<Props>()

const addToCardForm = useForm({})
const isAddingToCard = ref(false)

const handleAddToCard = () => {
  isAddingToCard.value = true
  addToCardForm.post(`/library/${props.album.id}/add-to-card`, {
    onSuccess: () => {
      isAddingToCard.value = false
    },
    onError: () => {
      isAddingToCard.value = false
    },
  })
}

const formatDuration = (seconds: number): string => {
  const mins = Math.floor(seconds / 60)
  const secs = seconds % 60
  return `${mins}:${secs.toString().padStart(2, '0')}`
}

const placeholderCover = 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 400 400%22%3E%3Crect fill=%22%23e5e7eb%22 width=%22400%22 height=%22400%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%239ca3af%22 font-family=%22sans-serif%22 font-size=%2232%22%3ENo Cover%3C/text%3E%3C/svg%3E'
</script>

<template>
  <div class="p-6 max-w-4xl mx-auto">
    <Link href="/library" as-child>
      <Button variant="ghost" class="mb-6">
        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Back to Library
      </Button>
    </Link>

    <Card class="overflow-hidden">
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
        <CardContent class="p-6 flex flex-col justify-between flex-grow">
          <div>
            <p class="text-gray-600 text-lg">{{ album.artist }}</p>
            <CardTitle class="text-4xl mt-2 mb-4">{{ album.title }}</CardTitle>

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

          <Button @click="handleAddToCard" :disabled="isAddingToCard" class="mt-6 w-fit">
            <svg v-if="isAddingToCard" class="mr-2 h-4 w-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <svg v-else class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            {{ isAddingToCard ? 'Adding to Card...' : 'Add to SD Card' }}
          </Button>
        </CardContent>
      </div>

      <!-- Track List -->
      <div class="border-t">
        <CardHeader class="bg-gray-50">
          <CardTitle>Tracks</CardTitle>
        </CardHeader>

        <div v-if="album.tracks.length === 0" class="p-6 text-center text-gray-500">
          No tracks found
        </div>

        <div v-else class="divide-y">
          <div
            v-for="(track, index) in album.tracks"
            :key="track.id"
            class="px-6 py-4 hover:bg-accent hover:text-accent-foreground dark:hover:bg-sidebar-accent dark:hover:text-sidebar-accent-foreground transition flex items-center gap-4"
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
    </Card>
  </div>
</template>
