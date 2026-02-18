import songSearch from './song-search';

document.addEventListener('alpine:init', () => {
    Alpine.data('songSearch', songSearch);
});
