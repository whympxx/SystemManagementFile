// Upload banyak berkas sekaligus dengan drag & drop, progres per berkas,
// dan hasil auto-kategori yang langsung terlihat.
const PARALLEL = 3;

export default (config) => ({
    items: [],
    dragging: false,
    categoryId: '',
    folderId: '',
    expiresAt: '',

    get running() {
        return this.items.some((i) => i.state === 'uploading' || i.state === 'queued');
    },
    get doneCount() {
        return this.items.filter((i) => i.state === 'done').length;
    },

    pick(event) {
        this.add(event.target.files);
        event.target.value = '';
    },

    drop(event) {
        this.dragging = false;
        this.add(event.dataTransfer.files);
    },

    add(fileList) {
        for (const file of fileList) {
            const ext = file.name.split('.').pop().toLowerCase();
            const item = { id: crypto.randomUUID(), file, name: file.name, size: file.size, progress: 0, state: 'queued', error: null, result: null };
            if (!config.extensions.includes(ext)) {
                Object.assign(item, { state: 'error', error: 'Jenis berkas tidak diizinkan' });
            } else if (file.size > config.maxBytes) {
                Object.assign(item, { state: 'error', error: 'Melebihi batas ukuran' });
            }
            this.items.unshift(item);
        }
        this.pump();
    },

    pump() {
        const active = this.items.filter((i) => i.state === 'uploading').length;
        const next = this.items.filter((i) => i.state === 'queued').reverse().slice(0, PARALLEL - active);
        next.forEach((item) => this.upload(item));
    },

    upload(item) {
        item.state = 'uploading';
        const form = new FormData();
        form.append('file', item.file);
        if (this.categoryId) form.append('category_id', this.categoryId);
        if (this.folderId) form.append('folder_id', this.folderId);
        if (this.expiresAt) form.append('expires_at', this.expiresAt);

        window.axios
            .post(config.url, form, {
                onUploadProgress: (e) => {
                    item.progress = e.total ? Math.round((e.loaded / e.total) * 100) : 0;
                    if (item.progress === 100) item.state = 'analyzing';
                },
            })
            .then(({ data }) => {
                item.result = data;
                item.state = 'done';
            })
            .catch((err) => {
                item.state = 'error';
                const errors = err.response?.data?.errors;
                item.error = errors ? Object.values(errors).flat()[0] : err.response?.data?.message || 'Gagal mengunggah';
            })
            .finally(() => this.pump());
    },

    clearDone() {
        this.items = this.items.filter((i) => i.state !== 'done' && i.state !== 'error');
    },

    formatSize(bytes) {
        const units = ['B', 'KB', 'MB', 'GB'];
        let i = 0;
        while (bytes >= 1024 && i < units.length - 1) {
            bytes /= 1024;
            i++;
        }
        return `${i ? bytes.toFixed(1) : bytes} ${units[i]}`;
    },
});
