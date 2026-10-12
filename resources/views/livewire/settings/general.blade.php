{{-- To attain knowledge, add things every day; To attain wisdom, subtract things every day. --}}
<div>
    <div class="bg-gradient-dark rounded-lg p-5 sm:p-8 mb-6 sm:mb-8 shadow-2xs card-highlight">
        <h2 class="text-xl sm:text-2xl font-bold mb-2 text-white">Notifications</h2>
        <p class="mb-6 text-muted text-sm sm:text-base">Configure how and when you receive notifications</p>
        <div class="sm:ml-4 flex items-center justify-between gap-3 mb-6">
            <div>
                <div class="text-base sm:text-lg text-white font-semibold"> Daily Reminder</div>
                <div class="text-muted text-xs sm:text-sm">Receive a daily reminder to write in your journal</div>
            </div>
            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                <input type="checkbox" wire:model.live="dailyReminder" class="sr-only peer" />
                <div
                    class="w-11 h-6 bg-gray-600 rounded-full peer peer-checked:bg-white transition-colors duration-300 cursor-pointer">
                </div>
                <div
                    class="absolute left-1 top-1 w-4 h-4 bg-gradient-dark rounded-full shadow transform peer-checked:translate-x-5 transition-transform duration-300">
                </div>
            </label>
        </div>
        <div class="sm:ml-4 flex items-center justify-between gap-3 mb-6">
            <div>
                <div class="text-base sm:text-lg text-white font-semibold"> Streak Alerts</div>
                <div class="text-muted text-xs sm:text-sm">Get notified about your writing streak milestones
                </div>
            </div>

            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                <input type="checkbox" wire:model.live="streakAlerts" class="sr-only peer" />
                <div
                    class="w-11 h-6 bg-gray-600 rounded-full peer peer-checked:bg-white transition-colors duration-300 cursor-pointer">
                </div>
                <div
                    class="absolute left-1 top-1 w-4 h-4 bg-gradient-dark rounded-full shadow transform peer-checked:translate-x-5 transition-transform duration-300">
                </div>
            </label>
        </div>
        <div class="sm:ml-4 flex items-center justify-between gap-3 mb-6">
            <div>
                <div class="text-base sm:text-lg text-white font-semibold"> Blog Updates </div>
                <div class="text-muted text-xs sm:text-sm">Stay in the loop—get notified when new blog content drops
                </div>
            </div>

            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                <input type="checkbox" wire:model.live="blogUpdates" class="sr-only peer" />
                <div
                    class="w-11 h-6 bg-gray-600 rounded-full peer peer-checked:bg-white transition-colors duration-300 cursor-pointer">
                </div>
                <div
                    class="absolute left-1 top-1 w-4 h-4 bg-gradient-dark rounded-full shadow transform peer-checked:translate-x-5 transition-transform duration-300">
                </div>
            </label>
        </div>
    </div>
    <div class="bg-gradient-dark rounded-lg p-5 sm:p-8 mb-6 sm:mb-8 shadow-2xs ">
        <h2 class="text-xl sm:text-2xl font-bold mb-2 text-white">Active Sessions</h2>
        <p class="mb-6 text-muted text-sm sm:text-base">Manage and revoke sessions on other devices</p>

        @foreach ($sessions as $session)
            <div class="sm:ml-4 border border-white/5 p-4 rounded-lg flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 card-highlight">
                <div>
                    <div class="text-base sm:text-lg text-white font-semibold break-all">
                        {{ $session['device'] }} — {{ $session['ip_address'] }}
                    </div>
                    <div class="text-muted text-xs sm:text-sm">
                        Last active: {{ $session['last_active'] }}
                        @if ($session['is_current_device'])
                            <span
                                class="inline-flex items-center space-x-1 rounded-full bg-green-900/30 border border-green-400 px-1 py-0.5 text-xs font-medium text-green-400 mt-1 sm:mt-0">
                                <x-icon name="device" class="w-4 h-4" />

                                <span>This Device</span>
                            </span>

                            <!-- its good if i let the user sign out from here i think -->
                        @endif
                    </div>
                </div>
                @if (!$session['is_current_device'])
                    <button wire:click="logoutSession('{{ $session['id'] }}')"
                        class="self-start sm:self-auto bg-transparent border border-white/5 hover:bg-red-600 text-white px-4 py-2 rounded text-xs sm:text-sm cursor-pointer">
                        Revoke
                    </button>
                @endif
            </div>
        @endforeach
    </div>


</div>