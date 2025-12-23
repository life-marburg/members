import Fuse from 'fuse.js'

export default function songSearch(songs) {
    return {
        query: '',
        songs: songs,
        results: songs,
        fuse: null,

        init() {
            this.fuse = new Fuse(this.songs, {
                keys: ['title'],
                threshold: 0.4,
                ignoreLocation: true
            })
        },

        search() {
            if (this.query.trim() === '') {
                this.results = this.songs
            } else {
                this.results = this.fuse.search(this.query).map(result => result.item)
            }
        }
    }
}
