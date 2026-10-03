<!-- Hospital MIS Support Directory Modal (Alpine.js) -->
<template x-teleport="body">
<div 
  x-show="helpdeskOpen" 
  x-cloak 
  class="fixed inset-0 z-50 overflow-y-auto" 
  @keydown.escape.window="helpdeskOpen = false"
>
  <div 
    x-show="helpdeskOpen" 
    x-transition:enter="ease-out duration-200" 
    x-transition:enter-start="opacity-0" 
    x-transition:enter-end="opacity-100" 
    x-transition:leave="ease-in duration-150" 
    x-transition:leave-start="opacity-100" 
    x-transition:leave-end="opacity-0" 
    class="fixed inset-0 bg-slate-950/60 backdrop-blur-md transition-opacity"
    @click="helpdeskOpen = false"
  ></div>

  <div class="fixed inset-0 z-10 flex min-h-full items-center justify-center p-4 pointer-events-none">
    <div 
      x-show="helpdeskOpen" 
      x-transition:enter="ease-out duration-200" 
      x-transition:enter-start="opacity-0 translate-y-3 sm:scale-95" 
      x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
      x-transition:leave="ease-in duration-150" 
      x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
      x-transition:leave-end="opacity-0 translate-y-3 sm:scale-95" 
      @click.outside="helpdeskOpen = false" 
      class="w-full max-w-lg transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 p-6 text-left shadow-2xl ring-1 ring-slate-200 dark:ring-slate-800 pointer-events-auto"
    >
      <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-sm ring-1 ring-emerald-500/30 text-lg">
            <i class="ph-bold ph-headset"></i>
          </div>
          <div>
            <h3 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">Hospital MIS Support Directory</h3>
            <p class="text-[11px] text-slate-400">Authorized Access &amp; Credential Provisioning</p>
          </div>
        </div>
        <button 
          type="button" 
          @click="helpdeskOpen = false" 
          class="rounded-lg p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer"
        >
          <i class="ph-bold ph-x text-base"></i>
        </button>
      </div>

      <div class="mt-4 space-y-3 text-xs">
        <p class="text-slate-600 dark:text-slate-300 leading-relaxed">
          Per RA 10173 and internal audit policies, credential resets and workstation authorizations are provisioned by the <strong>Hospital MIS Office</strong>:
        </p>

        <div class="space-y-2">
          <!-- 1. MIS Helpdesk VOIP -->
          <div class="flex items-center justify-between p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 ring-1 ring-slate-200/80 dark:ring-slate-700/60">
            <div class="flex items-center gap-2.5">
              <i class="ph-bold ph-phone-call text-base text-emerald-600 dark:text-emerald-400"></i>
              <div>
                <span class="font-bold text-slate-900 dark:text-white block">MIS Helpdesk (LAN)</span>
                <span class="text-slate-400 text-[10px]">Internal VOIP phone extension</span>
              </div>
            </div>
            <span class="font-mono text-xs font-semibold px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 ring-1 ring-emerald-600/20">
              Ext. 401 / 402
            </span>
          </div>

          <!-- 2. Night Shift Admin -->
          <div class="flex items-center justify-between p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 ring-1 ring-slate-200/80 dark:ring-slate-700/60">
            <div class="flex items-center gap-2.5">
              <i class="ph-bold ph-shield-check text-base text-emerald-600 dark:text-emerald-400"></i>
              <div>
                <span class="font-bold text-slate-900 dark:text-white block">Emergency Night Admin</span>
                <span class="text-slate-400 text-[10px]">On-duty supervisor (24/7)</span>
              </div>
            </div>
            <span class="font-mono text-xs font-semibold px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 ring-1 ring-emerald-600/20">
              Ext. 405
            </span>
          </div>

          <!-- 3. Official IT Email -->
          <div class="flex items-center justify-between p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 ring-1 ring-slate-200/80 dark:ring-slate-700/60">
            <div class="flex items-center gap-2.5">
              <i class="ph-bold ph-envelope-simple text-base text-emerald-600 dark:text-emerald-400"></i>
              <div>
                <span class="font-bold text-slate-900 dark:text-white block">Official IT Support</span>
                <span class="text-slate-400 text-[10px]">Hospital domain verification required</span>
              </div>
            </div>
            <a href="mailto:it-support@hospital.gov.ph" class="font-mono text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:underline">
              it-support@hospital.gov.ph
            </a>
          </div>
        </div>
      </div>

      <div class="mt-5 flex justify-end pt-3 border-t border-slate-100 dark:border-slate-800">
        <button 
          type="button" 
          @click="helpdeskOpen = false" 
          class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 text-xs font-semibold shadow-sm ring-1 ring-emerald-600/20 transition-all cursor-pointer"
        >
          <i class="ph-bold ph-check text-sm"></i>
          <span>Close</span>
        </button>
      </div>
    </div>
  </div>
</div>
</template>
