<!-- Hospital Legal & Regulatory Compliance Modal (Alpine.js) -->
<div 
  x-show="legalModalOpen" 
  x-cloak 
  class="fixed inset-0 z-50 overflow-y-auto" 
  @keydown.escape.window="legalModalOpen = false"
  role="dialog" 
  aria-modal="true" 
  aria-labelledby="legal-modal-title"
>
  <!-- Backdrop Blur Overlay -->
  <div 
    x-show="legalModalOpen" 
    x-transition:enter="ease-out duration-200" 
    x-transition:enter-start="opacity-0" 
    x-transition:enter-end="opacity-100" 
    x-transition:leave="ease-in duration-150" 
    x-transition:leave-start="opacity-100" 
    x-transition:leave-end="opacity-0" 
    class="fixed inset-0 bg-slate-950/75 backdrop-blur-sm"
  ></div>

  <!-- Modal Container -->
  <div class="fixed inset-0 z-10 flex min-h-full items-center justify-center p-3 sm:p-6 lg:p-8">
    <div 
      x-show="legalModalOpen" 
      x-transition:enter="ease-out duration-250" 
      x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95" 
      x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
      x-transition:leave="ease-in duration-150" 
      x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
      x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95" 
      @click.outside="legalModalOpen = false" 
      class="w-full max-w-4xl max-h-[92vh] flex flex-col transform overflow-hidden rounded-3xl bg-white dark:bg-slate-900 shadow-2xl ring-1 ring-slate-200/90 dark:ring-slate-800 transition-all text-left"
    >
      
      <!-- Top Modal Header -->
      <div class="p-5 sm:p-6 border-b border-slate-200/80 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/90 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        
        <div class="flex items-center gap-3.5">
          <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-emerald-600 text-white shadow-sm ring-1 ring-emerald-500/30 text-xl">
            <template x-if="legalActiveTab === 'terms'">
              <i class="ph-bold ph-scales"></i>
            </template>
            <template x-if="legalActiveTab === 'privacy'">
              <i class="ph-bold ph-shield-check"></i>
            </template>
            <template x-if="legalActiveTab === 'aup'">
              <i class="ph-bold ph-shield-warning"></i>
            </template>
          </div>
          <div>
            <div class="flex items-center gap-2">
              <h3 id="legal-modal-title" class="text-base font-bold text-slate-900 dark:text-white">
                Hospital Legal &amp; Regulatory Framework
              </h3>
              <span class="rounded-md bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 font-mono">
                PH STATUTORY
              </span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400">
              Department of Health &bull; Commission on Audit (COA) &bull; National Privacy Commission (NPC)
            </p>
          </div>
        </div>

        <!-- Top Right Action Controls -->
        <div class="flex items-center gap-2 self-end sm:self-auto">
          <!-- Print Button -->
          <button 
            type="button" 
            onclick="window.print()" 
            class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 transition-colors cursor-pointer"
            title="Print Official Document"
          >
            <i class="ph-bold ph-printer text-sm"></i>
            <span class="hidden sm:inline">Print</span>
          </button>

          <!-- Standalone Page Link -->
          <a 
            :href="legalActiveTab === 'privacy' ? '{{ route('legal.privacy') }}' : '{{ route('legal.terms') }}'" 
            target="_blank" 
            rel="noopener noreferrer" 
            class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 transition-colors cursor-pointer"
            title="Open in Full Screen / New Window"
          >
            <i class="ph-bold ph-arrow-square-out text-sm"></i>
            <span class="hidden sm:inline">Full Page</span>
          </a>

          <!-- Close Modal Trigger -->
          <button 
            type="button" 
            @click="legalModalOpen = false" 
            class="rounded-xl p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-200 transition-colors cursor-pointer"
            aria-label="Close modal"
          >
            <i class="ph-bold ph-x text-lg"></i>
          </button>
        </div>

      </div>

      <!-- Navigation Tabs Bar -->
      <div class="flex items-center overflow-x-auto border-b border-slate-200 dark:border-slate-800 px-4 sm:px-6 bg-slate-50/40 dark:bg-slate-900/40 gap-2 py-2">
        <button 
          type="button" 
          @click="legalActiveTab = 'terms'" 
          :class="legalActiveTab === 'terms' ? 'bg-white text-emerald-600 dark:bg-slate-800 dark:text-emerald-400 shadow-sm ring-1 ring-slate-200 dark:ring-slate-700' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'" 
          class="flex items-center gap-2 whitespace-nowrap rounded-xl px-3.5 py-2 text-xs font-semibold transition-all cursor-pointer"
        >
          <i class="ph-bold ph-scales text-sm"></i>
          <span>Terms &amp; Conditions</span>
        </button>

        <button 
          type="button" 
          @click="legalActiveTab = 'privacy'" 
          :class="legalActiveTab === 'privacy' ? 'bg-white text-emerald-600 dark:bg-slate-800 dark:text-emerald-400 shadow-sm ring-1 ring-slate-200 dark:ring-slate-700' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'" 
          class="flex items-center gap-2 whitespace-nowrap rounded-xl px-3.5 py-2 text-xs font-semibold transition-all cursor-pointer"
        >
          <i class="ph-bold ph-shield-check text-sm"></i>
          <span>Privacy Policy (RA 10173)</span>
        </button>

        <button 
          type="button" 
          @click="legalActiveTab = 'aup'" 
          :class="legalActiveTab === 'aup' ? 'bg-white text-emerald-600 dark:bg-slate-800 dark:text-emerald-400 shadow-sm ring-1 ring-slate-200 dark:ring-slate-700' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'" 
          class="flex items-center gap-2 whitespace-nowrap rounded-xl px-3.5 py-2 text-xs font-semibold transition-all cursor-pointer"
        >
          <i class="ph-bold ph-shield-warning text-sm"></i>
          <span>Acceptable Use &amp; Penalties</span>
        </button>
      </div>

      <!-- Scrollable Policy Content Area -->
      <div class="overflow-y-auto p-6 sm:p-8 flex-1 focus:outline-none" tabindex="0">
        
        <!-- Tab 1: Terms & Conditions -->
        <div x-show="legalActiveTab === 'terms'" x-cloak>
          @include('legal.content-terms')
        </div>

        <!-- Tab 2: Privacy Policy -->
        <div x-show="legalActiveTab === 'privacy'" x-cloak>
          @include('legal.content-privacy')
        </div>

        <!-- Tab 3: Acceptable Use & Penalties -->
        <div x-show="legalActiveTab === 'aup'" x-cloak>
          @include('legal.content-aup')
        </div>

      </div>

      <!-- Bottom Compliance Actions & Affirmation Footer -->
      <div class="p-4 sm:p-5 border-t border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/90 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
        
        <div class="flex items-center gap-2 text-slate-500 dark:text-slate-400 text-[11px]">
          <i class="ph-bold ph-seal-check text-base text-emerald-600 dark:text-emerald-400"></i>
          <span>Philippine Government Auditing &amp; Data Privacy Standards Enforced</span>
        </div>

        <div class="flex items-center gap-2.5 w-full sm:w-auto justify-end">
          <button 
            type="button" 
            @click="legalModalOpen = false" 
            class="rounded-xl px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800 transition-colors cursor-pointer"
          >
            Close
          </button>
          
          <button 
            type="button" 
            @click="legalModalOpen = false" 
            class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 transition-all cursor-pointer"
          >
            <i class="ph-bold ph-check text-sm"></i>
            <span>Understood</span>
          </button>
        </div>

      </div>

    </div>
  </div>
</div>
