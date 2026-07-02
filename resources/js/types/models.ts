export interface Track {
  id: number
  album_id: number
  title: string
  track_number: number
  duration: number
  file_path: string
  format: string
  bitrate: number | null
  created_at: string
  updated_at: string
}

export interface Album {
  id: number
  artist: string
  title: string
  year: number | null
  cover_path: string | null
  folder_path: string
  tracks_count?: number
  tracks?: Track[]
  created_at: string
  updated_at: string
}
