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
            <div class="sm:ml-4 border border-white/5 p-4 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-3 card-highlight bg-white/[0.02]">
                <div class="flex items-start sm:items-center gap-3.5 min-w-0">
                    <div class="p-2.5 rounded-xl bg-white/5 border border-white/10 text-gray-300 shrink-0 mt-0.5 sm:mt-0">
                        <x-icon name="device" class="w-5 h-5" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-sm sm:text-base font-semibold text-white">
                                {{ $session['device'] }}
                            </span>
                            @if ($session['is_current_device'])
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/15 border border-emerald-500/30 px-2 py-0.5 text-[11px] font-medium text-emerald-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                    <span>This Device</span>
                                </span>
                            @endif
                        </div>
                        <div class="flex flex-wrap items-center gap-x-2.5 gap-y-1 text-xs text-muted mt-1">
                            <span class="font-mono text-gray-400">{{ $session['ip_address'] }}</span>
                            <span class="text-gray-600">&bull;</span>
                            <span>Last active: {{ $session['last_active'] }}</span>
                        </div>
                    </div>
                </div>
                @if (!$session['is_current_device'])
                    <button wire:click="logoutSession('{{ $session['id'] }}')"
                        class="self-start sm:self-auto bg-transparent border border-red-500/30 hover:bg-red-500/20 text-red-400 px-3 py-1.5 rounded-lg text-xs font-medium cursor-pointer transition-colors">
                        Revoke
                    </button>
                @endif
            </div>
        @endforeach
    </div>


</div>