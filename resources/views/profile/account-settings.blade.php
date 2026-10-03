@extends('layouts.app')

@section('title', 'Account Settings — Financial Management System')
@section('module', 'user-security')
@section('page', 'account-settings')

@section('content')
<script>
function accountSettingsData() {
  return {
    // Theme state
    currentTheme: localStorage.getItem('fms_theme') || @json(auth()->user()->theme_preference ?? 'system') || 'system',
    themeSaving: false,

    // Profile state
    editModalOpen: false,
    profileName: @json(auth()->user()->name),
    profileFirstName: @json(auth()->user()->first_name ?? ''),
    profileMiddleName: @json(auth()->user()->middle_name ?? ''),
    profileLastName: @json(auth()->user()->last_name ?? ''),
    profileEmail: @json(auth()->user()->email),
    originalEmail: @json(auth()->user()->email),
    currentPassword: '',
    avatarUrl: @json(auth()->user()->avatarUrl() ?? ''),
    profileSaving: false,
    profileError: '',

    // Password Modal state
    passwordModalOpen: false,
    pwdCurrentPassword: '',
    pwdNewPassword: '',
    pwdNewPasswordConfirmation: '',
    passwordSaving: false,
    passwordError: '',

    init() {
      this.$watch('editModalOpen', () => this.syncBodyLock());
      this.$watch('passwordModalOpen', () => this.syncBodyLock());
    },

    syncBodyLock() {
      if (this.editModalOpen || this.passwordModalOpen) {
        document.body.classList.add('overflow-hidden', 'modal-open');
      } else {
        document.body.classList.remove('overflow-hidden', 'modal-open');
      }
    },

    openPasswordModal() {
      this.pwdCurrentPassword = '';
      this.pwdNewPassword = '';
      this.pwdNewPasswordConfirmation = '';
      this.passwordError = '';
      this.passwordModalOpen = true;
    },

    savePassword() {
      if (!this.pwdCurrentPassword) {
        this.passwordError = 'Current password is required.';
        return;
      }
      if (!this.pwdNewPassword) {
        this.passwordError = 'New password is required.';
        return;
      }
      if (this.pwdNewPassword.length < 8) {
        this.passwordError = 'New password must be at least 8 characters.';
        return;
      }
      if (this.pwdNewPassword !== this.pwdNewPasswordConfirmation) {
        this.passwordError = 'New password and confirmation do not match.';
        return;
      }

      this.passwordSaving = true;
      this.passwordError = '';

      fetch('{{ route("password.change.update") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
        },
        body: JSON.stringify({
          current_password: this.pwdCurrentPassword,
          password: this.pwdNewPassword,
          password_confirmation: this.pwdNewPasswordConfirmation
        })
      })
      .then(async (res) => {
        const text = await res.text();
        let body = {};
        try {
          body = JSON.parse(text);
        } catch (e) {
          throw new Error('Unexpected server response (' + res.status + ').');
        }
        return { status: res.status, body: body };
      })
      .then(({ status, body }) => {
        this.passwordSaving = false;
        if (status === 200 && body.success) {
          this.passwordModalOpen = false;
          this.pwdCurrentPassword = '';
          this.pwdNewPassword = '';
          this.pwdNewPasswordConfirmation = '';

          if (typeof window.showToast === 'function') {
            window.showToast(body.message || 'Password changed successfully', 'success');
          }
        } else {
          this.passwordError = body.message || Object.values(body.errors || {})[0]?.[0] || 'Failed to update password.';
        }
      })
      .catch(err => {
        this.passwordSaving = false;
        this.passwordError = err && err.message ? err.message : 'Network error while updating password.';
      });
    },

    // Photo state
    photoUploading: false,

    selectTheme(theme) {
      this.currentTheme = theme;
      this.themeSaving = true;
      
      // Apply theme globally and immediately
      if (typeof window.setFmsTheme === 'function') {
        window.setFmsTheme(theme);
      } else {
        localStorage.setItem('fms_theme', theme);
        localStorage.setItem('himsMainTheme', theme);
        const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        const isDark = theme === 'dark' || (theme === 'system' && prefersDark);
        if (isDark) {
          document.documentElement.classList.add('dark');
        } else {
          document.documentElement.classList.remove('dark');
        }
      }

      // Persist to user record via asynchronous request
      fetch('{{ route("account.theme.update") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
        },
        body: JSON.stringify({ theme: theme })
      })
      .then(res => res.json())
      .then(data => {
        this.themeSaving = false;
        if (typeof window.showToast === 'function') {
          const capitalized = theme.charAt(0).toUpperCase() + theme.slice(1);
          window.showToast('Theme updated to ' + capitalized + ' mode', 'success');
        }
      })
      .catch(err => {
        this.themeSaving = false;
        console.error('Failed to persist theme:', err);
      });
    },

    openEditModal() {
      this.profileError = '';
      this.currentPassword = '';
      this.editModalOpen = true;
    },

    saveProfile() {
      if (!this.profileFirstName.trim() || !this.profileLastName.trim()) {
        this.profileError = 'Last name and first name are required.';
        return;
      }
      if (!this.profileEmail.trim()) {
        this.profileError = 'Email address is required.';
        return;
      }
      if (this.profileEmail.trim().toLowerCase() !== this.originalEmail.toLowerCase() && !this.currentPassword) {
        this.profileError = 'Current password is required to change your email address.';
        return;
      }

      this.profileSaving = true;
      this.profileError = '';

      fetch('{{ route("account.profile.update") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
        },
        body: JSON.stringify({
          first_name: this.profileFirstName,
          middle_name: this.profileMiddleName,
          last_name: this.profileLastName,
          email: this.profileEmail,
          current_password: this.currentPassword
        })
      })
      .then(res => res.json().then(data => ({ status: res.status, body: data })))
      .then(({ status, body }) => {
        this.profileSaving = false;
        if (status === 200 && body.success) {
          this.profileName = body.user.name;
          this.profileFirstName = body.user.first_name;
          this.profileMiddleName = body.user.middle_name || '';
          this.profileLastName = body.user.last_name;
          this.profileEmail = body.user.email;
          this.originalEmail = body.user.email;
          this.currentPassword = '';
          this.editModalOpen = false;

          // Dispatch event to automatically update headbar and sidebar in real time!
          window.dispatchEvent(new CustomEvent('user-profile-updated', {
            detail: {
              name: this.profileName,
              email: this.profileEmail,
              avatarUrl: this.avatarUrl
            }
          }));

          if (typeof window.showToast === 'function') {
            window.showToast('Profile information saved successfully', 'success');
          }
        } else {
          this.profileError = body.message || Object.values(body.errors || {})[0]?.[0] || 'Failed to update profile.';
        }
      })
      .catch(err => {
        this.profileSaving = false;
        this.profileError = 'A network error occurred while updating profile.';
      });
    },

    uploadPhoto(e) {
      const file = e.target.files[0];
      if (!file) return;

      if (!file.type.match(/^image\//i)) {
        if (typeof window.showToast === 'function') {
          window.showToast('Please select a valid image file (JPG, PNG, WebP).', 'warning');
        }
        return;
      }

      this.photoUploading = true;

      // Automatically downscale & optimize image in browser (max 512x512 JPEG)
      // This guarantees instantaneous uploads (<50KB) and avoids server upload size limits
      const reader = new FileReader();
      reader.onload = (event) => {
        const img = new Image();
        img.onload = () => {
          const maxDim = 512;
          let width = img.width;
          let height = img.height;

          if (width > height) {
            if (width > maxDim) {
              height = Math.round((height * maxDim) / width);
              width = maxDim;
            }
          } else {
            if (height > maxDim) {
              width = Math.round((width * maxDim) / height);
              height = maxDim;
            }
          }

          const canvas = document.createElement('canvas');
          canvas.width = width;
          canvas.height = height;
          const ctx = canvas.getContext('2d');
          ctx.drawImage(img, 0, 0, width, height);

          // Generate lightweight base64 JPEG data URL
          const base64Data = canvas.toDataURL('image/jpeg', 0.90);
          this.sendPhoto(file, base64Data);
        };
        img.onerror = () => this.sendPhoto(file, null);
        img.src = event.target.result;
      };
      reader.onerror = () => this.sendPhoto(file, null);
      reader.readAsDataURL(file);

      // Reset file input so re-selecting same file triggers change
      e.target.value = '';
    },

    sendPhoto(file, base64Data) {
      const formData = new FormData();
      const csrfMeta = document.querySelector('meta[name="csrf-token"]');
      const csrfToken = csrfMeta ? csrfMeta.content : '{{ csrf_token() }}';

      formData.append('_token', csrfToken);

      if (base64Data) {
        formData.append('photo_base64', base64Data);
      } else if (file) {
        formData.append('photo', file);
      }

      fetch('{{ route("account.photo.update") }}', {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': csrfToken
        },
        body: formData
      })
      .then(async (res) => {
        const text = await res.text();
        let body = {};
        try {
          body = JSON.parse(text);
        } catch (parseErr) {
          console.error('Photo upload non-JSON response:', text);
          if (res.status === 419) {
            throw new Error('Your session expired. Please refresh the page to continue.');
          } else if (res.status === 413) {
            throw new Error('The selected image is too large for the server.');
          } else {
            throw new Error('Unexpected server response (status ' + res.status + '). Please try again.');
          }
        }
        return { status: res.status, body: body };
      })
      .then(({ status, body }) => {
        this.photoUploading = false;
        if (status === 200 && body.success) {
          this.avatarUrl = body.avatar_url;

          // Dispatch event to automatically update headbar and sidebar in real time!
          window.dispatchEvent(new CustomEvent('user-profile-updated', {
            detail: {
              name: this.profileName,
              email: this.profileEmail,
              avatarUrl: this.avatarUrl
            }
          }));

          if (typeof window.showToast === 'function') {
            window.showToast(body.message || 'Profile photo updated successfully', 'success');
          }
        } else {
          const errMsg = body.message || Object.values(body.errors || {})[0]?.[0] || 'Failed to upload photo.';
          if (typeof window.showToast === 'function') {
            window.showToast(errMsg, 'error');
          }
        }
      })
      .catch(err => {
        this.photoUploading = false;
        console.error('Photo upload error:', err);
        const displayMsg = (err && err.message) ? err.message : 'Network error while uploading photo.';
        if (typeof window.showToast === 'function') {
          window.showToast(displayMsg, 'error');
        }
      });
    },

    removePhoto() {
      if (!confirm('Remove profile photo?')) return;
      this.photoUploading = true;

      fetch('{{ route("account.photo.remove") }}', {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
        }
      })
      .then(res => res.json())
      .then(body => {
        this.photoUploading = false;
        if (body.success) {
          this.avatarUrl = '';

          // Dispatch event to automatically update headbar and sidebar in real time!
          window.dispatchEvent(new CustomEvent('user-profile-updated', {
            detail: {
              name: this.profileName,
              email: this.profileEmail,
              avatarUrl: ''
            }
          }));

          if (typeof window.showToast === 'function') {
            window.showToast('Profile photo removed.', 'success');
          }
        }
      })
      .catch(() => {
        this.photoUploading = false;
      });
    }
  };
}
</script>

<div 
  x-data="accountSettingsData()"
  class="max-w-5xl mx-auto space-y-6"
>
  {{-- Breadcrumbs Navigation --}}
  <nav class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400" aria-label="Breadcrumb">
    <a href="{{ route('accounting.dashboard') }}" class="hover:text-slate-900 dark:text-slate-300 dark:hover:text-white transition-colors">Home</a>
    <i class="ph-bold ph-caret-right text-[10px] text-slate-400"></i>
    <span class="font-medium text-slate-800 dark:text-slate-200">Account Settings</span>
  </nav>

  {{-- Page Title --}}
  <div class="flex items-center justify-between">
    <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 dark:text-white">
      Account Settings
    </h1>
  </div>

  {{-- 1. Profile Information Card --}}
  <div class="rounded-2xl bg-white p-6 sm:p-7 shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800 transition-colors">
    {{-- Top Row: Avatar + Info (Balanced Horizontal Alignment with Generous Breathing Room) --}}
    <div class="flex items-center gap-7 sm:gap-8">
      
      {{-- User Avatar Image / Neutral Blank Icon --}}
      <div class="relative shrink-0">
        <div class="relative h-20 w-20 sm:h-22 sm:w-22 rounded-full overflow-hidden bg-slate-100 dark:bg-slate-800 ring-1 ring-slate-200/90 dark:ring-slate-700/80 shadow-xs flex items-center justify-center">
          <template x-if="avatarUrl">
            <img 
              :src="avatarUrl" 
              :alt="profileName" 
              class="h-full w-full object-cover"
              loading="lazy"
            >
          </template>
          <template x-if="!avatarUrl">
            <div class="flex h-full w-full items-center justify-center text-slate-400 dark:text-slate-500">
              <i class="ph-bold ph-user text-3xl sm:text-4xl"></i>
            </div>
          </template>

          <div 
            x-show="photoUploading" 
            x-cloak 
            class="absolute inset-0 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center text-white"
          >
            <i class="ph-bold ph-spinner animate-spin text-xl"></i>
          </div>
        </div>
      </div>

      {{-- Profile Info Details --}}
      <div class="flex-1 min-w-0 space-y-1">
        <h2 class="text-xs sm:text-sm font-semibold text-slate-500 dark:text-slate-400">
          Profile Information
        </h2>
        <div class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white truncate tracking-tight mt-0.5" x-text="profileName">
          {{ auth()->user()->name }}
        </div>
        <div class="text-sm text-slate-500 dark:text-slate-400 truncate" x-text="profileEmail">
          {{ auth()->user()->email }}
        </div>
      </div>

    </div>

    {{-- Bottom Row: Action Buttons Left-Aligned --}}
    <div class="flex flex-wrap items-center gap-3 mt-7 pt-1">
      {{-- Primary Edit Profile Button --}}
      <button 
        type="button" 
        @click="openEditModal()" 
        class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-emerald-600 px-5 py-2.5 text-xs sm:text-sm font-semibold text-white shadow-sm ring-1 ring-emerald-600/20 hover:bg-emerald-700 active:scale-95 transition-all focus:outline-none focus:ring-2 focus:ring-emerald-500/30"
      >
        <span>Edit profile</span>
      </button>

      {{-- Secondary Change Photo Button --}}
      <button 
        type="button" 
        @click="$refs.photoInput.click()" 
        :disabled="photoUploading"
        class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-white px-5 py-2.5 text-xs sm:text-sm font-semibold text-slate-700 ring-1 ring-slate-300 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-700 dark:hover:bg-slate-750 shadow-sm active:scale-95 transition-all disabled:opacity-60 focus:outline-none"
      >
        <span x-text="photoUploading ? 'Uploading...' : 'Change photo'">Change photo</span>
      </button>

      {{-- Optional Remove Photo if uploaded --}}
      <template x-if="avatarUrl">
        <button 
          type="button" 
          @click="removePhoto()" 
          class="inline-flex items-center justify-center rounded-xl px-3.5 py-2.5 text-xs sm:text-sm font-semibold text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 dark:text-rose-400 transition-colors"
          title="Remove custom photo"
        >
          <i class="ph-bold ph-trash text-sm"></i>
          <span class="ml-1.5">Remove photo</span>
        </button>
      </template>

      {{-- Hidden Photo Input --}}
      <input 
        type="file" 
        x-ref="photoInput" 
        @change="uploadPhoto($event)" 
        accept="image/png, image/jpeg, image/jpg, image/webp" 
        class="hidden" 
      >
    </div>
  </div>

  {{-- 2. Appearance & Theme Card --}}
  <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800 transition-colors">
    <div class="mb-5">
      <h2 class="text-base font-bold text-slate-900 dark:text-white">
        Appearance &amp; Theme
      </h2>
    </div>

    {{-- 3 Theme Tiles --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

      {{-- 1. Light Theme Option --}}
      <div 
        @click="selectTheme('light')" 
        class="group relative flex flex-col justify-between rounded-2xl p-5 cursor-pointer transition-all border"
        :class="currentTheme === 'light' 
          ? 'border-emerald-500 ring-2 ring-emerald-500/20 bg-emerald-50/30 dark:bg-emerald-950/20' 
          : 'border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 bg-white dark:bg-slate-900/60'"
        role="radio"
        :aria-checked="currentTheme === 'light'"
        tabindex="0"
        @keydown.enter="selectTheme('light')"
        @keydown.space.prevent="selectTheme('light')"
      >
        <div class="flex items-start justify-between gap-3">
          <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600 ring-1 ring-amber-500/20 dark:bg-amber-950/40 dark:text-amber-400">
              <i class="ph-bold ph-sun text-lg"></i>
            </div>
            <div>
              <div class="font-bold text-sm text-slate-900 dark:text-white">
                Light
              </div>
            </div>
          </div>

          {{-- Checkmark Badge Indicator --}}
          <div 
            x-show="currentTheme === 'light'" 
            x-cloak
            class="text-emerald-600 dark:text-emerald-400 transition-opacity"
          >
            <i class="ph-bold ph-check-circle text-xl"></i>
          </div>
        </div>

        <p class="mt-4 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed font-normal">
          Bright, clinical surface for high-illumination environments.
        </p>
      </div>

      {{-- 2. Dark Theme Option --}}
      <div 
        @click="selectTheme('dark')" 
        class="group relative flex flex-col justify-between rounded-2xl p-5 cursor-pointer transition-all border"
        :class="currentTheme === 'dark' 
          ? 'border-emerald-500 ring-2 ring-emerald-500/20 bg-emerald-50/30 dark:bg-emerald-950/20' 
          : 'border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 bg-white dark:bg-slate-900/60'"
        role="radio"
        :aria-checked="currentTheme === 'dark'"
        tabindex="0"
        @keydown.enter="selectTheme('dark')"
        @keydown.space.prevent="selectTheme('dark')"
      >
        <div class="flex items-start justify-between gap-3">
          <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-sky-600 ring-1 ring-sky-500/20 dark:bg-sky-950/40 dark:text-sky-400">
              <i class="ph-bold ph-moon text-lg"></i>
            </div>
            <div>
              <div class="font-bold text-sm text-slate-900 dark:text-white">
                Dark
              </div>
            </div>
          </div>

          {{-- Checkmark Badge Indicator --}}
          <div 
            x-show="currentTheme === 'dark'" 
            x-cloak
            class="text-emerald-600 dark:text-emerald-400 transition-opacity"
          >
            <i class="ph-bold ph-check-circle text-xl"></i>
          </div>
        </div>

        <p class="mt-4 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed font-normal">
          Low-glare dark surfaces reducing eye fatigue in dim lighting.
        </p>
      </div>

      {{-- 3. System Theme Option --}}
      <div 
        @click="selectTheme('system')" 
        class="group relative flex flex-col justify-between rounded-2xl p-5 cursor-pointer transition-all border"
        :class="currentTheme === 'system' 
          ? 'border-emerald-500 ring-2 ring-emerald-500/20 bg-emerald-50/30 dark:bg-emerald-950/20' 
          : 'border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 bg-white dark:bg-slate-900/60'"
        role="radio"
        :aria-checked="currentTheme === 'system'"
        tabindex="0"
        @keydown.enter="selectTheme('system')"
        @keydown.space.prevent="selectTheme('system')"
      >
        <div class="flex items-start justify-between gap-3">
          <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-600 ring-1 ring-slate-300/40 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-700">
              <i class="ph-bold ph-desktop text-lg"></i>
            </div>
            <div>
              <div class="font-bold text-sm text-slate-900 dark:text-white">
                System
              </div>
            </div>
          </div>

          {{-- Checkmark Badge Indicator --}}
          <div 
            x-show="currentTheme === 'system'" 
            x-cloak
            class="text-emerald-600 dark:text-emerald-400 transition-opacity"
          >
            <i class="ph-bold ph-check-circle text-xl"></i>
          </div>
        </div>

        <p class="mt-4 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed font-normal">
          Automatically match your operating system's light or dark mode.
        </p>
      </div>

    </div>
  </div>

  {{-- 3. Security & Credentials Shortcut Card --}}
  <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800 transition-colors">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h3 class="text-base font-bold text-slate-900 dark:text-white">
          Security &amp; Credentials
        </h3>
        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
          Manage your hospital password, two-factor authentication, and active terminal sessions.
        </p>
      </div>
      <div class="flex items-center gap-2">
        <button 
          type="button" 
          @click="openPasswordModal()" 
          class="inline-flex items-center gap-1.5 rounded-xl bg-slate-100 px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-200/80 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 transition-colors cursor-pointer"
        >
          <i class="ph-bold ph-key text-sm text-slate-500"></i>
          <span>Change Password</span>
        </button>
        @can('access-workstation-management')
        <a 
          href="{{ route('user-security.workstations') }}" 
          class="inline-flex items-center gap-1.5 rounded-xl bg-slate-100 px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-200/80 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 transition-colors"
        >
          <i class="ph-bold ph-desktop text-sm text-slate-500"></i>
          <span>Workstation Binding</span>
        </a>
        @endcan
      </div>
    </div>
  </div>

  {{-- Edit Profile Modal (Exact Layout Matching Target Specs with Hospital Emerald Theme) --}}
  <template x-teleport="body">
    <div 
      x-show="editModalOpen" 
      x-cloak 
      class="fixed inset-0 z-50 overflow-y-auto" 
      role="dialog" 
      aria-modal="true"
    >
      <div 
        x-show="editModalOpen"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-slate-950/60 backdrop-blur-md transition-opacity"
        @click="editModalOpen = false"
      ></div>

      <div class="fixed inset-0 z-10 flex min-h-full items-center justify-center p-4 pointer-events-none">
        <div 
          x-show="editModalOpen"
          x-transition:enter="ease-out duration-200"
          x-transition:enter-start="opacity-0 scale-95"
          x-transition:enter-end="opacity-100 scale-100"
          x-transition:leave="ease-in duration-150"
          x-transition:leave-start="opacity-100 scale-100"
          x-transition:leave-end="opacity-0 scale-95"
          @click.outside="editModalOpen = false"
          class="w-full max-w-lg rounded-2xl bg-white p-6 sm:p-7 shadow-2xl ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800 pointer-events-auto"
        >
        {{-- Modal Header --}}
        <div class="flex items-center justify-between pb-4">
          <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">
            Edit profile
          </h3>
          <button 
            type="button" 
            @click="editModalOpen = false" 
            class="rounded-lg p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:text-slate-200 dark:hover:bg-slate-800 transition-colors focus:outline-none"
            aria-label="Close dialog"
          >
            <i class="ph-bold ph-x text-lg"></i>
          </button>
        </div>

        <form @submit.prevent="saveProfile" class="space-y-4 pt-1">
          <template x-if="profileError">
            <div class="rounded-xl border border-rose-200/80 bg-rose-50/80 p-3 text-xs text-rose-800 dark:border-rose-900/50 dark:bg-rose-950/40 dark:text-rose-300 flex items-center gap-2">
              <i class="ph-bold ph-warning-circle text-base text-rose-600 dark:text-rose-400 shrink-0"></i>
              <span x-text="profileError"></span>
            </div>
          </template>

          {{-- Row 1: Last Name & First Name side-by-side --}}
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs sm:text-[13px] font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                Last Name
              </label>
              <input 
                type="text" 
                x-model="profileLastName" 
                required 
                placeholder="e.g. De Monteverde"
                class="w-full rounded-xl border-0 bg-slate-100/80 py-2.5 px-3.5 text-sm text-slate-900 ring-1 ring-inset ring-slate-300 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-emerald-600 dark:bg-slate-800 dark:text-white dark:ring-slate-700 dark:focus:ring-emerald-500 transition-all placeholder:text-slate-400"
              >
            </div>
            <div>
              <label class="block text-xs sm:text-[13px] font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                First Name
              </label>
              <input 
                type="text" 
                x-model="profileFirstName" 
                required 
                placeholder="e.g. Zedrick"
                class="w-full rounded-xl border-0 bg-slate-100/80 py-2.5 px-3.5 text-sm text-slate-900 ring-1 ring-inset ring-slate-300 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-emerald-600 dark:bg-slate-800 dark:text-white dark:ring-slate-700 dark:focus:ring-emerald-500 transition-all placeholder:text-slate-400"
              >
            </div>
          </div>

          {{-- Row 2: Middle Name (full width) --}}
          <div>
            <label class="block text-xs sm:text-[13px] font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
              Middle Name
            </label>
            <input 
              type="text" 
              x-model="profileMiddleName" 
              placeholder="e.g. Ganton"
              class="w-full rounded-xl border-0 bg-slate-100/80 py-2.5 px-3.5 text-sm text-slate-900 ring-1 ring-inset ring-slate-300 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-emerald-600 dark:bg-slate-800 dark:text-white dark:ring-slate-700 dark:focus:ring-emerald-500 transition-all placeholder:text-slate-400"
            >
          </div>

          {{-- Row 3: Email (full width) --}}
          <div>
            <label class="block text-xs sm:text-[13px] font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
              Email
            </label>
            <input 
              type="email" 
              x-model="profileEmail" 
              required 
              placeholder="name@example.com"
              class="w-full rounded-xl border-0 bg-slate-100/80 py-2.5 px-3.5 text-sm text-slate-900 ring-1 ring-inset ring-slate-300 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-emerald-600 dark:bg-slate-800 dark:text-white dark:ring-slate-700 dark:focus:ring-emerald-500 transition-all placeholder:text-slate-400"
            >
          </div>

          {{-- Row 4: Current Password (full width with helper text) --}}
          <div>
            <label class="block text-xs sm:text-[13px] font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
              Current Password
            </label>
            <input 
              type="password" 
              x-model="currentPassword" 
              :required="profileEmail.trim().toLowerCase() !== originalEmail.toLowerCase()"
              placeholder="••••••••"
              class="w-full rounded-xl border-0 bg-slate-100/80 py-2.5 px-3.5 text-sm text-slate-900 ring-1 ring-inset ring-slate-300 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-emerald-600 dark:bg-slate-800 dark:text-white dark:ring-slate-700 dark:focus:ring-emerald-500 transition-all"
            >
            <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">
              Required only when changing your email address.
            </p>
          </div>

          {{-- Modal Footer --}}
          <div class="flex items-center justify-end gap-2.5 pt-4">
            <button 
              type="button" 
              @click="editModalOpen = false" 
              class="rounded-xl px-4 py-2 text-xs sm:text-sm font-semibold text-slate-700 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800 transition-colors focus:outline-none"
            >
              Cancel
            </button>
            <button 
              type="submit" 
              :disabled="profileSaving"
              class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-5 py-2 text-xs sm:text-sm font-semibold text-white shadow-sm ring-1 ring-emerald-600/20 hover:bg-emerald-700 active:scale-95 disabled:opacity-60 transition-all focus:outline-none focus:ring-2 focus:ring-emerald-500/30"
            >
              <i x-show="profileSaving" class="ph-bold ph-spinner animate-spin"></i>
              <span x-text="profileSaving ? 'Saving...' : 'Save'">Save</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
  </template>

  {{-- Change Password Modal --}}
  <template x-teleport="body">
    <div 
      x-show="passwordModalOpen" 
      x-cloak 
      class="fixed inset-0 z-50 overflow-y-auto" 
      role="dialog" 
      aria-modal="true"
    >
      <div 
        x-show="passwordModalOpen"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-slate-950/60 backdrop-blur-md transition-opacity"
        @click="passwordModalOpen = false"
      ></div>

      <div class="fixed inset-0 z-10 flex min-h-full items-center justify-center p-4 pointer-events-none">
        <div 
          x-show="passwordModalOpen"
          x-transition:enter="ease-out duration-200"
          x-transition:enter-start="opacity-0 scale-95"
          x-transition:enter-end="opacity-100 scale-100"
          x-transition:leave="ease-in duration-150"
          x-transition:leave-start="opacity-100 scale-100"
          x-transition:leave-end="opacity-0 scale-95"
          @click.outside="passwordModalOpen = false"
          class="w-full max-w-md rounded-2xl bg-white p-6 sm:p-7 shadow-2xl ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800 pointer-events-auto"
        >
        {{-- Modal Header --}}
        <div class="flex items-center justify-between pb-4">
          <div class="flex items-center gap-2.5">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400 ring-1 ring-emerald-600/20">
              <i class="ph-bold ph-key text-lg"></i>
            </div>
            <div>
              <h3 class="text-base font-bold text-slate-900 dark:text-white">
                Change Password
              </h3>
              <p class="text-xs text-slate-500 dark:text-slate-400">
                Update your account security credential
              </p>
            </div>
          </div>
          <button 
            type="button" 
            @click="passwordModalOpen = false" 
            class="rounded-lg p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:text-slate-200 dark:hover:bg-slate-800 transition-colors focus:outline-none"
            aria-label="Close dialog"
          >
            <i class="ph-bold ph-x text-lg"></i>
          </button>
        </div>

        <form @submit.prevent="savePassword" class="space-y-4 pt-1">
          <template x-if="passwordError">
            <div class="rounded-xl border border-rose-200/80 bg-rose-50/80 p-3 text-xs text-rose-800 dark:border-rose-900/50 dark:bg-rose-950/40 dark:text-rose-300 flex items-center gap-2">
              <i class="ph-bold ph-warning-circle text-base text-rose-600 dark:text-rose-400 shrink-0"></i>
              <span x-text="passwordError"></span>
            </div>
          </template>

          {{-- Current Password --}}
          <div>
            <label class="block text-xs sm:text-[13px] font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
              Current Password
            </label>
            <input 
              type="password" 
              x-model="pwdCurrentPassword" 
              required 
              placeholder="••••••••"
              class="w-full rounded-xl border-0 bg-slate-100/80 py-2.5 px-3.5 text-sm text-slate-900 ring-1 ring-inset ring-slate-300 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-emerald-600 dark:bg-slate-800 dark:text-white dark:ring-slate-700 dark:focus:ring-emerald-500 transition-all placeholder:text-slate-400"
            >
          </div>

          {{-- New Password --}}
          <div>
            <label class="block text-xs sm:text-[13px] font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
              New Password
            </label>
            <input 
              type="password" 
              x-model="pwdNewPassword" 
              required 
              placeholder="••••••••"
              class="w-full rounded-xl border-0 bg-slate-100/80 py-2.5 px-3.5 text-sm text-slate-900 ring-1 ring-inset ring-slate-300 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-emerald-600 dark:bg-slate-800 dark:text-white dark:ring-slate-700 dark:focus:ring-emerald-500 transition-all placeholder:text-slate-400"
            >
            <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
              Must be at least 8 characters with letters and numbers.
            </p>
          </div>

          {{-- Confirm New Password --}}
          <div>
            <label class="block text-xs sm:text-[13px] font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
              Confirm New Password
            </label>
            <input 
              type="password" 
              x-model="pwdNewPasswordConfirmation" 
              required 
              placeholder="••••••••"
              class="w-full rounded-xl border-0 bg-slate-100/80 py-2.5 px-3.5 text-sm text-slate-900 ring-1 ring-inset ring-slate-300 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-emerald-600 dark:bg-slate-800 dark:text-white dark:ring-slate-700 dark:focus:ring-emerald-500 transition-all placeholder:text-slate-400"
            >
          </div>

          {{-- Modal Footer --}}
          <div class="flex items-center justify-end gap-2.5 pt-4">
            <button 
              type="button" 
              @click="passwordModalOpen = false" 
              class="rounded-xl px-4 py-2 text-xs sm:text-sm font-semibold text-slate-700 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800 transition-colors focus:outline-none"
            >
              Cancel
            </button>
            <button 
              type="submit" 
              :disabled="passwordSaving"
              class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-5 py-2 text-xs sm:text-sm font-semibold text-white shadow-sm ring-1 ring-emerald-600/20 hover:bg-emerald-700 active:scale-95 disabled:opacity-60 transition-all focus:outline-none focus:ring-2 focus:ring-emerald-500/30 cursor-pointer"
            >
              <i x-show="passwordSaving" class="ph-bold ph-spinner animate-spin"></i>
              <span x-text="passwordSaving ? 'Updating...' : 'Update Password'">Update Password</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
  </template>

</div>
@endsection
