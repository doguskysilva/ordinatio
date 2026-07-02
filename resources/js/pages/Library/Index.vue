<script setup lang="ts">
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import type { Album } from '@/types'

interface Props {
  albums: {
    data: Album[]
    links: any
    meta: any
  }
}

const props = defineProps<Props>()

const searchQuery = ref('')

const filteredAlbums = computed(() => {
  if (!searchQuery.value) {
    return props.albums.data
  }

  const query = searchQuery.value.toLowerCase()
  return props.albums.data.filter(album =>
    album.artist.toLowerCase().includes(query) ||
    album.title.toLowerCase().includes(query)
  )
})

const placeholderCover = 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 200 200%22%3E%3Crect fill=%22%23e5e7eb%22 width=%22200%22 height=%22200%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%239ca3af%22 font-family=%22sans-serif%22 font-size=%2216%22%3ENo Cover%3C/text%3E%3C/svg%3E'
</script>

<template>
  <div class="space-y-6 p-6">
    <div class="flex items-center justify-between">
      <h1 class="text-3xl font-bold">Music Library</h1>
      <Link
        href="/library/scan"
        method="post"
        as-child
      >
        <Button>
          <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          Scan Library
        </Button>
      </Link>
    </div>

    <div>
      <Input
        v-model="searchQuery"
        type="text"
        placeholder="Search by artist or album..."
      />
    </div>

    <div v-if="filteredAlbums.length === 0" class="text-center py-12">
      <p class="text-gray-500">No albums found. Try scanning your library.</p>
    </div>

    <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
      <Link
        v-for="album in filteredAlbums"
        :key="album.id"
        :href="`/library/${album.id}`"
        class="no-underline"
      >
        <Card class="h-full hover:shadow-lg transition cursor-pointer">
          <div class="aspect-square bg-gray-200 overflow-hidden">
            <img
              :src="album.cover_path || placeholderCover"
              :alt="`${album.artist} - ${album.title}`"
              class="w-full h-full object-cover group-hover:scale-105 transition"
            />
          </div>
          <CardContent class="p-3">
            <CardTitle class="text-sm line-clamp-2 text-foreground">{{ album.title }}</CardTitle>
            <CardDescription class="text-xs line-clamp-1 text-muted-foreground">{{ album.artist }}</CardDescription>
            <p class="text-xs text-muted-foreground mt-1">{{ album.tracks_count || 0 }} tracks</p>
          </CardContent>
        </Card>
      </Link>
    </div>

    <div v-if="albums.links" class="flex justify-center gap-2 mt-8">
      <template v-for="link in albums.links" :key="link.label">
        <Link
          v-if="link.url"
          :href="link.url"
          as-child
        >
          <Button
            :variant="link.active ? 'default' : 'outline'"
            size="sm"
          >
            {{ link.label }}
          </Button>
        </Link>
        <Button
          v-else
          :variant="link.active ? 'default' : 'outline'"
          size="sm"
          disabled
        >
          {{ link.label }}
        </Button>
      </template>
    </div>
  </div>
</template>
