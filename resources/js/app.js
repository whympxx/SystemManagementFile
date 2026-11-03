import './bootstrap';
import Alpine from 'alpinejs';
import uploader from './uploader';

window.Alpine = Alpine;

Alpine.data('uploader', uploader);

// Pilih banyak berkas di daftar untuk aksi massal
Alpine.data('bulkSelect', () => ({
    selected: [],
    toggleAll(event) {
        const boxes = [...document.querySelectorAll('input[data-doc-id]')];
        this.selected = event.target.checked ? boxes.map((b) => b.value) : [];
    },
}));

Alpine.start();
