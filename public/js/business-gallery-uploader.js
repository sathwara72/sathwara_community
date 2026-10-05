// Gallery photo picker used by the business registration form (Alpine component).
function multiShowcaseUploader() {
    return {
        files: [],
        isDragging: false,
        maxFiles: 6,
        showLimitModal: false,
        
        selectFiles(e) {
            if (e.target.files && e.target.files.length > 0) {
                this.addFiles(Array.from(e.target.files));
            }
        },
        
        dropFiles(e) {
            this.isDragging = false;
            if (e.dataTransfer && e.dataTransfer.files) {
                this.addFiles(Array.from(e.dataTransfer.files));
            }
        },
        
        addFiles(newFiles) {
            let overflow = false;
            newFiles.forEach(file => {
                if (file.type.startsWith('image/')) {
                    const exists = this.files.some(f => f.name === file.name && f.file.size === file.size);
                    if (!exists) {
                        if (this.files.length < this.maxFiles) {
                            this.files.push({
                                id: Math.random().toString(36).substring(2, 9),
                                file: file,
                                name: file.name,
                                size: (file.size / (1024 * 1024)).toFixed(2) + ' MB',
                                url: URL.createObjectURL(file)
                            });
                        } else {
                            overflow = true;
                        }
                    }
                }
            });

            if (overflow) {
                this.showLimitModal = true;
            }

            this.syncInput();
        },
        
        removeFile(index) {
            if (this.files[index]) {
                URL.revokeObjectURL(this.files[index].url);
                this.files.splice(index, 1);
                this.syncInput();
            }
        },
        
        clearAll() {
            this.files.forEach(f => URL.revokeObjectURL(f.url));
            this.files = [];
            this.syncInput();
        },
        
        syncInput() {
            const input = this.$refs.hiddenFileInput;
            if (input) {
                const dt = new DataTransfer();
                this.files.forEach(f => dt.items.add(f.file));
                input.files = dt.files;
            }
        }
    };
}
