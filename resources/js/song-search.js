import Fuse from 'fuse.js'

export default function songSearch(songs, songSets) {
    return {
        query: '',
        selectedSet: null,
        songs: songs,
        songSets: songSets,
        results: songs,
        fuse: null,

        init() {
            this.fuse = new Fuse(this.songs, {
                keys: ['title'],
                threshold: 0.4,
                ignoreLocation: true
            })
        },

        filter() {
            let filtered = this.songs

            // Filter by set if selected
            if (this.selectedSet) {
                const setId = parseInt(this.selectedSet)
                filtered = this.songs
                    .filter(song => song.sets.some(s => s.id === setId))
                    .sort((a, b) => {
                        const posA = a.sets.find(s => s.id === setId)?.position ?? 999
                        const posB = b.sets.find(s => s.id === setId)?.position ?? 999
                        return posA - posB
                    })
            }

            // Apply search filter
            if (this.query.trim() !== '') {
                const searchFuse = new Fuse(filtered, {
                    keys: ['title'],
                    threshold: 0.4,
                    ignoreLocation: true
                })
                this.results = searchFuse.search(this.query).map(result => result.item)
            } else {
                this.results = filtered
            }
        }
    }
}
