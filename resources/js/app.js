import Alpine from 'alpinejs';
import songSearch from './song-search';

Alpine.data('songSearch', songSearch)

window.Alpine = Alpine;

Alpine.start();
