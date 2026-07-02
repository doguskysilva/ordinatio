<script setup lang="ts">
import { ref, computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import type { Playlist, Track } from '@/types'

interface Props {
  playlist: Playlist & { tracks: Track[] }
  availableTracks: Track[]
}

const props = defineProps<Props>()

const isAddTracksDialogOpen = ref(false)
const searchQuery = ref('')
const selectedTrackId = ref<number | null>(null)

const addTrackForm = useForm({
  track_id: null as number | null,
})

const playlistTrackIds = computed(() => new Set(props.playlist.tracks.map(t => t.id)))

const filteredTracks = computed(() => {
  const query = searchQuery.value.toLowerCase()

  return props.availableTracks
    .filter(track => !playlistTrackIds.value.has(track.id))
    .filter(track =>
      track.title.toLowerCase().includes(query) ||
      track.album?.toLowerCase().includes(query)
    )
})

const handleAddTrack = () => {
  if (selectedTrackId.value) {
    addTrackForm.track_id = selectedTrackId.value
    addTrackForm.post(`/playlists/${props.playlist.id}/tracks`, {
      onSuccess: () => {
        isAddTracksDialogOpen.value = false
        selectedTrackId.value = null
        searchQuery.value = ''
        addTrackForm.reset()
      },
    })
  }
}

const handleRemoveTrack = (trackId: number) => {
  useForm().delete(`/playlists/${props.playlist.id}/tracks/${trackId}`)
}

const formatDuration = (seconds: number): string => {
  const mins = Math.floor(seconds / 60)
  const secs = seconds % 60
  return `${mins}:${secs.toString().padStart(2, '0')}`
}
</script>

<template>
  <div class="space-y-6 p-6 max-w-4xl mx-auto">
    <Link href="/playlists" as-child>
      <Button variant="ghost" class="mb-6">
        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Back to Playlists
      </Button>
    </Link>

    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-4xl font-bold">{{ playlist.name }}</h1>
        <p class="text-gray-600 mt-2">{{ playlist.tracks.length }} tracks</p>
      </div>
      <Dialog v-model:open="isAddTracksDialogOpen">
        <DialogTrigger as-child>
          <Button>
            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Tracks
          </Button>
        </DialogTrigger>
        <DialogContent class="max-w-2xl">
          <DialogHeader>
            <DialogTitle>Add Tracks to Playlist</DialogTitle>
            <DialogDescription>
              Search for tracks from your library to add to this playlist.
            </DialogDescription>
          </DialogHeader>
          <form @submit.prevent="handleAddTrack" class="space-y-4">
            <div>
              <label for="search" class="text-sm font-medium">Search Tracks</label>
              <Input
                id="search"
                v-model="searchQuery"
                placeholder="Search by title or album..."
                class="mt-1"
              />
            </div>

            <div class="border rounded-lg max-h-96 overflow-y-auto">
              <div v-if="filteredTracks.length === 0" class="p-4 text-center text-gray-500">
                <p v-if="props.availableTracks.length === 0">No tracks available</p>
                <p v-else>No tracks match your search</p>
              </div>

              <div v-else class="divide-y">
                <button
                  v-for="track in filteredTracks"
                  :key="track.id"
                  type="button"
                  @click="selectedTrackId = track.id"
                  :class="[
                    'w-full text-left p-3 transition flex items-center gap-3',
                    'hover:bg-gray-100 dark:hover:bg-gray-700',
                    selectedTrackId === track.id ? 'bg-blue-100 dark:bg-blue-900' : ''
                  ]"
                >
                  <input
                    type="radio"
                    :checked="selectedTrackId === track.id"
                    :value="track.id"
                    class="w-4 h-4"
                  />
                  <div class="flex-grow">
                    <p class="font-semibold">{{ track.title }}</p>
                    <p class="text-sm text-gray-600">{{ track.album }}</p>
                  </div>
                  <p class="text-sm text-gray-600">{{ formatDuration(track.duration || 0) }}</p>
                </button>
              </div>
            </div>

            <div class="flex justify-end gap-2">
              <Button variant="outline" @click="isAddTracksDialogOpen = false">
                Cancel
              </Button>
              <Button :disabled="!selectedTrackId || addTrackForm.processing">
                Add Track
              </Button>
            </div>
          </form>
        </DialogContent>
      </Dialog>
    </div>

    <Card>
      <CardHeader>
        <CardTitle>Tracks</CardTitle>
        <CardDescription>
          Manage tracks in this playlist
        </CardDescription>
      </CardHeader>
      <CardContent>
        <div v-if="playlist.tracks.length === 0" class="text-center py-8 text-gray-500">
          No tracks in this playlist yet. Add some to get started!
        </div>

        <div v-else class="space-y-2">
          <div
            v-for="(track, index) in playlist.tracks"
            :key="track.id"
            class="flex items-center gap-4 p-3 rounded-lg hover:bg-gray-50 transition"
          >
            <span class="text-gray-400 font-semibold w-8 text-right">
              {{ index + 1 }}
            </span>
            <div class="flex-grow">
              <p class="font-semibold">{{ track.title }}</p>
              <p class="text-sm text-gray-600">{{ track.album }}</p>
            </div>
            <div class="text-right">
              <p class="text-gray-700">{{ formatDuration(track.duration || 0) }}</p>
              <p v-if="track.bitrate" class="text-xs text-gray-500">{{ track.bitrate }}kbps</p>
            </div>
            <Button
              variant="ghost"
              size="sm"
              @click="handleRemoveTrack(track.id)"
            >
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </Button>
          </div>
        </div>
      </CardContent>
    </Card>
  </div>
</template>
