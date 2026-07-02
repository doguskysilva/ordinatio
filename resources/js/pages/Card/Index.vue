<script setup lang="ts">
import { ref } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'

interface CardState {
  albums: Array<{ name: string; tracks: number; size: number }>
  playlists: Array<{ name: string; tracks: number; size: number }>
  total_size: number
}

interface Diff {
  card: CardState
  missing_albums: string[]
  extra_albums: string[]
  missing_playlists: string[]
  extra_playlists: string[]
}

interface Props {
  card: CardState
  diff: Diff
}

const props = defineProps<Props>()

const syncForm = useForm({})
const isSyncing = ref(false)

const handleSync = () => {
  isSyncing.value = true
  syncForm.post('/sync', {
    onSuccess: () => {
      isSyncing.value = false
    },
    onError: () => {
      isSyncing.value = false
    },
  })
}

const formatSize = (bytes: number): string => {
  const units = ['B', 'KB', 'MB', 'GB']
  let size = bytes
  let unitIndex = 0

  while (size >= 1024 && unitIndex < units.length - 1) {
    size /= 1024
    unitIndex++
  }

  return `${size.toFixed(2)} ${units[unitIndex]}`
}

const getStatusColor = (status: 'synced' | 'missing' | 'extra') => {
  return {
    synced: 'text-green-600',
    missing: 'text-yellow-600',
    extra: 'text-red-600',
  }[status]
}
</script>

<template>
  <div class="space-y-6 p-6">
    <div class="flex items-center justify-between">
      <h1 class="text-3xl font-bold">SD Card State</h1>
      <Button @click="handleSync" :disabled="isSyncing">
        <svg v-if="isSyncing" class="mr-2 h-4 w-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        <svg v-else class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        {{ isSyncing ? 'Syncing...' : 'Sync to Card' }}
      </Button>
    </div>

    <!-- Card Info -->
    <Card>
      <CardHeader>
        <CardTitle>Card Space</CardTitle>
        <CardDescription>Storage information</CardDescription>
      </CardHeader>
      <CardContent>
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-600">Total Used</p>
            <p class="text-2xl font-bold">{{ formatSize(card.total_size) }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-600">Albums</p>
            <p class="text-2xl font-bold">{{ card.albums.length }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-600">Playlists</p>
            <p class="text-2xl font-bold">{{ card.playlists.length }}</p>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Albums on Card -->
    <Card>
      <CardHeader>
        <CardTitle>Albums on Card</CardTitle>
        <CardDescription v-if="card.albums.length === 0">No albums on card yet</CardDescription>
        <CardDescription v-else>{{ card.albums.length }} albums synced</CardDescription>
      </CardHeader>
      <CardContent>
        <div v-if="card.albums.length === 0" class="text-center py-8 text-gray-500">
          No albums on card. Start a sync to add albums.
        </div>
        <div v-else class="space-y-2">
          <div
            v-for="album in card.albums"
            :key="album.name"
            class="flex items-center justify-between p-3 rounded-lg bg-gray-50"
          >
            <div>
              <p class="font-semibold">{{ album.name }}</p>
              <p class="text-sm text-gray-600">{{ album.tracks }} tracks</p>
            </div>
            <p class="text-sm text-gray-600">{{ formatSize(album.size) }}</p>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Playlists on Card -->
    <Card>
      <CardHeader>
        <CardTitle>Playlists on Card</CardTitle>
        <CardDescription v-if="card.playlists.length === 0">No playlists on card yet</CardDescription>
        <CardDescription v-else>{{ card.playlists.length }} playlists synced</CardDescription>
      </CardHeader>
      <CardContent>
        <div v-if="card.playlists.length === 0" class="text-center py-8 text-gray-500">
          No playlists on card. Start a sync to add playlists.
        </div>
        <div v-else class="space-y-2">
          <div
            v-for="playlist in card.playlists"
            :key="playlist.name"
            class="flex items-center justify-between p-3 rounded-lg bg-gray-50"
          >
            <div>
              <p class="font-semibold">{{ playlist.name }}</p>
              <p class="text-sm text-gray-600">{{ playlist.tracks }} tracks</p>
            </div>
            <p class="text-sm text-gray-600">{{ formatSize(playlist.size) }}</p>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Sync Status -->
    <Card>
      <CardHeader>
        <CardTitle>Sync Status</CardTitle>
        <CardDescription>What needs to be synced</CardDescription>
      </CardHeader>
      <CardContent class="space-y-4">
        <div v-if="diff.missing_albums.length > 0">
          <h4 class="font-semibold mb-2 flex items-center gap-2">
            <svg class="w-4 h-4 text-yellow-600" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" />
            </svg>
            Missing Albums ({{ diff.missing_albums.length }})
          </h4>
          <ul class="space-y-1 text-sm text-gray-600">
            <li v-for="album in diff.missing_albums" :key="album">• {{ album }}</li>
          </ul>
        </div>

        <div v-if="diff.missing_playlists.length > 0">
          <h4 class="font-semibold mb-2 flex items-center gap-2">
            <svg class="w-4 h-4 text-yellow-600" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" />
            </svg>
            Missing Playlists ({{ diff.missing_playlists.length }})
          </h4>
          <ul class="space-y-1 text-sm text-gray-600">
            <li v-for="playlist in diff.missing_playlists" :key="playlist">• {{ playlist }}</li>
          </ul>
        </div>

        <div v-if="diff.missing_albums.length === 0 && diff.missing_playlists.length === 0" class="text-center py-8 text-green-600">
          ✅ Card is up to date!
        </div>
      </CardContent>
    </Card>
  </div>
</template>
