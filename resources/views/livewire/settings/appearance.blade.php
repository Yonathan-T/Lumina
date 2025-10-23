<div>


    <div class="bg-gradient-dark rounded-lg p-8 mb-8 shadow-2xl card-highlight">
        <h2 class="text-2xl font-bold mb-2 text-white">Appearance</h2>
        <p class="mb-6 text-muted">Customize how Lumina looks and feels</p>

        <div class="ml-4 flex items-center justify-between mb-8">
            <div>
                <div class="text-lg text-white font-semibold">Dark Mode</div>
                <div class="text-muted text-sm">Switch between light and dark themes</div>
            </div>
            <x-toggle :model="'darkMode'" />
        </div>
        <hr class="ml-4 border border-white/5 mb-4" />

        <div class="ml-4 mb-6">
            <label for="fontSize" class="block text-sm font-medium text-gray-300 mb-1">Font Size</label>
            <div class="relative">
                <select id="fontSize" wire:model="fontSize" class="flex h-10 w-full rounded-md border border-white/15 shadow-sm bg-background px-3 py-2 text-sm
                text-white">
                    <option value="Small">Small</option>
                    <option value="Medium">Medium</option>
                    <option value="Large">Large</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-muted">
                    <!-- I need better drop downs here. react would  have saved me -->
                </div>
            </div>
        </div>


        <div class="ml-4">
            <label for="fontFamily" class="block text-sm font-medium text-gray-300 mb-1">Font Family</label>
            <div class="relative">
                <select id="fontFamily" wire:model="fontFamily" class="flex h-10 w-full rounded-md border border-white/15 shadow-sm bg-background px-3 py-2 text-sm
                text-white">
                    <option value="sans-serif">Sans-serif</option>
                    <option value="serif">Serif</option>
                    <option value="monospace">Monospace</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-muted">
                    <!-- and here. -->
                </div>
            </div>
        </div>
    </div>
    <div class="bg-gradient-dark rounded-lg p-8 mb-8 shadow-2xs card-highlight space-y-6">


        <div class="card-highlight  rounded-lg overflow-hidden">
            <div class="p-6 border-b border-white/10">
                <h3 class="text-xl font-semibold text-white">Data</h3>
                <p class="text-sm text-gray-400 mt-1">Manage your journal data and exports</p>
            </div>

            <div class="p-6 space-y-6">



                <div class="border-t border-white/10"></div>


                <div class="space-y-3">
                    <button
                        class="w-full px-4 py-3 border border-white/10 hover:bg-blue-300/15 rounded-lg transition-all duration-200 text-left flex items-center justify-between group">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-lg bg-purple-500/10 flex items-center justify-center group-hover:bg-purple-500/20 transition-colors">
                                <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-white">Import Entries</p>
                                <p class="text-xs text-gray-400">Import journal entries from backup or another
                                    service</p>
                            </div>
                        </div>

                        <svg class="w-5 h-5 text-gray-400 group-hover:text-white transition-colors" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>

                    <p class="text-xs text-gray-500 px-1">
                        Upload a JSON file to restore your entries or migrate from another journaling app.
                    </p>
                </div>

                <div class="border-t border-white/10"></div>


                <div class="space-y-3">
                    <button
                        class="w-full px-4 py-3 border border-white/10 hover:bg-red-300/15 rounded-lg transition-all duration-200 text-left flex items-center justify-between group">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-lg bg-red-500/10 flex items-center justify-center group-hover:bg-red-500/20 transition-colors">
                                <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-white">Clear Cache</p>
                                <p class="text-xs text-gray-400">Clear locally stored data and refresh the
                                    application</p>
                            </div>
                        </div>

                        <svg class="w-5 h-5 text-gray-400 group-hover:text-white transition-colors" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>

                    <p class="text-xs text-gray-500 px-1">
                        This will clear your browser's cache and reload the page. Your entries are safe.
                    </p>
                </div>
            </div>
        </div>


        <div id="toast-container" class="fixed bottom-4 right-4 z-50"></div>


        @push('scripts')
            <script>
                // Download JSON file
                window.addEventListener('download-json', event => {
                    const { filename, content } = event.detail[0];

                    // Create blob and download
                    const blob = new Blob([content], { type: 'application/json' });
                    const url = window.URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = filename;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    window.URL.revokeObjectURL(url);
                });

                // Show toast notification
                window.addEventListener('show-toast', event => {
                    const { message, type } = event.detail[0];
                    const container = document.getElementById('toast-container');

                    const toast = document.createElement('div');
                    toast.className = `
                                                    px-6 py-4 rounded-lg shadow-lg mb-4 
                                                    transform transition-all duration-300 ease-out
                                                    ${type === 'success' ? 'bg-green-500/90' : 'bg-red-500/90'}
                                                    text-white backdrop-blur-sm
                                                    translate-x-0 opacity-100
                                                `;
                    toast.innerHTML = `
                                                    <div class="flex items-center gap-3">
                                                        ${type === 'success'
                            ? '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
                            : '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>'
                        }
                                                        <span class="font-medium">${message}</span>
                                                    </div>
                                                `;

                    container.appendChild(toast);

                    // Animate in
                    setTimeout(() => {
                        toast.style.transform = 'translateX(0)';
                    }, 10);

                    // Remove after 3 seconds
                    setTimeout(() => {
                        toast.style.transform = 'translateX(400px)';
                        toast.style.opacity = '0';
                        setTimeout(() => {
                            container.removeChild(toast);
                        }, 300);
                    }, 3000);
                });
            </script>
        @endpush
    </div>
</div>