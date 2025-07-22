{{-- To attain knowledge, add things every day; To attain wisdom, subtract things every day. --}}
<div>
    <div class="bg-gradient-dark rounded-lg p-8 mb-8 shadow-2xs card-highlight">
        <h2 class="text-2xl font-bold mb-2 text-white">Notifications</h2>
        <p class="mb-6 text-muted">Configure how and when you receive notifications</p>
        <div class=" ml-4 flex items-center justify-between mb-6">
            <div>
                <div class="text-lg text-white font-semibold"> Daily Reminder</div>
                <div class="text-muted text-sm">Receive a daily reminder to write in your journal</div>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" wire:model="darkMode" class="sr-only peer" />
                <div
                    class="w-11 h-6 bg-gray-600 rounded-full peer peer-checked:bg-white transition-colors duration-300">
                </div>
                <div
                    class="absolute left-1 top-1 w-4 h-4 bg-gradient-dark rounded-full shadow transform peer-checked:translate-x-5 transition-transform duration-300">
                </div>
            </label>
        </div>
        <div class="ml-4 flex items-center justify-between mb-6">
            <div>
                <div class="text-lg text-white font-semibold"> Streak Alerts</div>
                <div class="text-muted text-sm">Get notified about your writing streak milestones
                </div>
            </div>

            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" wire:model="darkMode" class="sr-only peer" />
                <div
                    class="w-11 h-6 bg-gray-600 rounded-full peer peer-checked:bg-white transition-colors duration-300">
                </div>
                <div
                    class="absolute left-1 top-1 w-4 h-4 bg-gradient-dark rounded-full shadow transform peer-checked:translate-x-5 transition-transform duration-300">
                </div>
            </label>
        </div>
        <div class="ml-4 flex items-center justify-between mb-6">
            <div>
                <div class="text-lg text-white font-semibold"> Blog Updates </div>
                <div class="text-muted text-sm">Stay in the loop—get notified when new blog content drops
                </div>
            </div>

            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" wire:model="darkMode" class="sr-only peer" />
                <div
                    class="w-11 h-6 bg-gray-600 rounded-full peer peer-checked:bg-blue-600 transition-colors duration-300">
                </div>
                <div
                    class="absolute left-1 top-1 w-4 h-4 bg-gradient-dark rounded-full shadow transform peer-checked:translate-x-5 transition-transform duration-300">
                </div>
            </label>
        </div>

    </div>
    <div class="bg-gradient-dark rounded-lg p-8 mb-8 shadow-2xs ">
        <h2 class="text-2xl font-bold mb-2 text-white">Active Sessions</h2>
        <p class="mb-6 text-muted">Manage and revoke sessions on other devices</p>

        @foreach ($sessions as $session)
            <div class="ml-4 border border-white/5 p-4 rounded-lg flex items-center justify-between mb-4 card-highlight">
                <div>
                    <div class="text-lg text-white font-semibold">
                        {{ $session['device'] }} — {{ $session['ip_address'] }}
                    </div>
                    <div class="text-muted text-sm">
                        Last active: {{ $session['last_active'] }}
                        @if ($session['is_current_device'])
                            <span
                                class="inline-flex items-center space-x-1 rounded-full bg-green-900/30 border border-green-400 px-1 py-0.5 text-xs font-medium text-green-400">
                                <x-icon name="device" class="w-4 h-4" />

                                <span>This Device</span>
                            </span>

                            <!-- its good if i let the user sign out from here i think -->
                        @endif
                    </div>
                </div>
                @if (!$session['is_current_device'])
                    <button wire:click="logoutSession('{{ $session['id'] }}')"
                        class="bg-transparent border border-white/5 hover:bg-red-600 text-white px-4 py-2 rounded">
                        Revoke
                    </button>
                @endif
            </div>
        @endforeach
    </div>


</div>