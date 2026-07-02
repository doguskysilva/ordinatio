<script setup lang="ts">
import { ref } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import type { Playlist } from '@/types'

interface Props {
  playlists: Playlist[]
}

const props = defineProps<Props>()

const isCreateDialogOpen = ref(false)
const createForm = useForm({
  name: '',
})

const handleCreate = () => {
  createForm.post('/playlists', {
    onSuccess: () => {
      isCreateDialogOpen.value = false
      createForm.reset()
    },
  })
}

const handleDelete = (id: number) => {
  if (confirm('Are you sure you want to delete this playlist?')) {
    useForm().delete(`/playlists/${id}`)
  }
}
</script>

<template>
  <div class="space-y-6 p-6">
    <div class="flex items-center justify-between">
      <h1 class="text-3xl font-bold">Playlists</h1>
      <Dialog v-model:open="isCreateDialogOpen">
        <DialogTrigger as-child>
          <Button>
            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            New Playlist
          </Button>
        </DialogTrigger>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Create Playlist</DialogTitle>
            <DialogDescription>
              Create a new playlist to organize your music.
            </DialogDescription>
          </DialogHeader>
          <form @submit.prevent="handleCreate" class="space-y-4">
            <div>
              <label for="name" class="text-sm font-medium">Playlist Name</label>
              <Input
                id="name"
                v-model="createForm.name"
                placeholder="My Awesome Playlist"
                class="mt-1"
              />
            </div>
            <div class="flex justify-end gap-2">
              <Button variant="outline" @click="isCreateDialogOpen = false">
                Cancel
              </Button>
              <Button :disabled="createForm.processing">
                Create
              </Button>
            </div>
          </form>
        </DialogContent>
      </Dialog>
    </div>

    <div v-if="playlists.length === 0" class="text-center py-12">
      <p class="text-gray-500">No playlists yet. Create one to get started!</p>
    </div>

    <div v-else class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
      <Link
        v-for="playlist in playlists"
        :key="playlist.id"
        :href="`/playlists/${playlist.id}`"
        class="no-underline"
      >
        <Card class="h-full hover:shadow-lg transition cursor-pointer">
          <CardHeader>
            <CardTitle>{{ playlist.name }}</CardTitle>
            <CardDescription>
              {{ playlist.tracks?.length || 0 }} tracks
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div class="flex gap-2">
              <Button
                variant="outline"
                size="sm"
                @click.prevent="handleDelete(playlist.id)"
              >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </Button>
            </div>
          </CardContent>
        </Card>
      </Link>
    </div>
  </div>
</template>
