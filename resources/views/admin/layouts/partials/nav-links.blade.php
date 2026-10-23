@php
    $navOn  = 'flex items-center w-full px-3.5 py-2.5 rounded-xl text-xs font-semibold text-white bg-blue-600 shadow-sm shadow-blue-500/25 transition-all';
    $navOff = 'flex items-center w-full px-3.5 py-2.5 rounded-xl text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors group';
@endphp

{{-- 1. UTAMA --}}
<a href="{{ route('admin.dashboard') }}"
   class="{{ request()->routeIs('admin.dashboard') ? $navOn : $navOff }}">
    <i class="ti ti-smart-home text-base w-5 mr-2.5 shrink-0 {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-slate-400 group-hover:text-slate-600' }}"></i>
    <span>Dashboard</span>
</a>

{{-- 2. MASTER DATA --}}
<p class="px-3.5 pt-4 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Master Data</p>

<a href="{{ route('admin.siswa.index') }}"
   class="{{ request()->routeIs('admin.siswa.*') ? $navOn : $navOff }}">
    <i class="ti ti-school text-base w-5 mr-2.5 shrink-0 {{ request()->routeIs('admin.siswa.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-600' }}"></i>
    <span>Data Siswa</span>
</a>

<a href="{{ route('admin.satpam.index') }}"
   class="{{ request()->routeIs('admin.satpam.*') ? $navOn : $navOff }}">
    <i class="ti ti-shield-check text-base w-5 mr-2.5 shrink-0 {{ request()->routeIs('admin.satpam.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-600' }}"></i>
    <span>Data Satpam</span>
</a>

<a href="{{ route('admin.guru.index') }}"
   class="{{ (request()->routeIs('admin.guru.index') || request()->routeIs('admin.guru.create') || request()->routeIs('admin.guru.edit')) ? $navOn : $navOff }}">
    <i class="ti ti-user-check text-base w-5 mr-2.5 shrink-0 {{ (request()->routeIs('admin.guru.index') || request()->routeIs('admin.guru.create') || request()->routeIs('admin.guru.edit')) ? 'text-white' : 'text-slate-400 group-hover:text-slate-600' }}"></i>
    <span>Data Guru</span>
</a>

<a href="{{ route('admin.guru.checklog') }}"
   class="{{ request()->routeIs('admin.guru.checklog') ? $navOn : $navOff }}">
    <i class="ti ti-id text-base w-5 mr-2.5 shrink-0 {{ request()->routeIs('admin.guru.checklog') ? 'text-white' : 'text-slate-400 group-hover:text-slate-600' }}"></i>
    <span>Izin / Log Guru</span>
</a>

{{-- 3. OPERASIONAL --}}
<p class="px-3.5 pt-4 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Operasional</p>

<a href="{{ route('admin.jadwal-piket.index') }}"
   class="{{ request()->routeIs('admin.jadwal-piket.*') ? $navOn : $navOff }}">
    <i class="ti ti-calendar text-base w-5 mr-2.5 shrink-0 {{ request()->routeIs('admin.jadwal-piket.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-600' }}"></i>
    <span>Jadwal Piket</span>
</a>

<a href="{{ route('admin.semua.pengajuan') }}"
   class="{{ request()->routeIs('admin.semua.*') ? $navOn : $navOff }}">
    <i class="ti ti-file-text text-base w-5 mr-2.5 shrink-0 {{ request()->routeIs('admin.semua.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-600' }}"></i>
    <span>Semua Pengajuan</span>
</a>

<a href="{{ route('admin.laporan.index') }}"
   class="{{ request()->routeIs('admin.laporan.*') ? $navOn : $navOff }}">
    <i class="ti ti-chart-bar text-base w-5 mr-2.5 shrink-0 {{ request()->routeIs('admin.laporan.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-600' }}"></i>
    <span>Laporan Rekapitulasi</span>
</a>

<a href="{{ route('admin.whatsapp-templates.index') }}"
   class="{{ request()->routeIs('admin.whatsapp-templates.*') ? $navOn : $navOff }}">
    <i class="ti ti-brand-whatsapp text-base w-5 mr-2.5 shrink-0 {{ request()->routeIs('admin.whatsapp-templates.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-600' }}"></i>
    <span>Template WhatsApp</span>
</a>

<a href="{{ route('admin.whatsapp-bot.index') }}"
   class="{{ request()->routeIs('admin.whatsapp-bot.*') ? $navOn : $navOff }}">
    <i class="ti ti-robot text-base w-5 mr-2.5 shrink-0 {{ request()->routeIs('admin.whatsapp-bot.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-600' }}"></i>
    <span>Bot & Log WhatsApp</span>
</a>

{{-- 4. SISTEM --}}
<p class="px-3.5 pt-4 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Sistem</p>

<a href="{{ route('admin.superadmin.index') }}"
   class="{{ request()->routeIs('admin.superadmin.*') ? $navOn : $navOff }}">
    <i class="ti ti-shield-lock text-base w-5 mr-2.5 shrink-0 {{ request()->routeIs('admin.superadmin.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-600' }}"></i>
    <span>Super Admin</span>
</a>

<a href="{{ route('admin.settings.index') }}"
   class="{{ request()->routeIs('admin.settings.*') ? $navOn : $navOff }}">
    <i class="ti ti-settings text-base w-5 mr-2.5 shrink-0 {{ request()->routeIs('admin.settings.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-600' }}"></i>
    <span>Pengaturan Sistem</span>
</a>

<a href="{{ route('admin.audit.index') }}"
   class="{{ request()->routeIs('admin.audit.*') ? $navOn : $navOff }}">
    <i class="ti ti-history text-base w-5 mr-2.5 shrink-0 {{ request()->routeIs('admin.audit.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-600' }}"></i>
    <span>Audit Log</span>
</a>

<a href="{{ url('/profil') }}"
   class="{{ request()->is('profil*') ? $navOn : $navOff }}">
    <i class="ti ti-user text-base w-5 mr-2.5 shrink-0 {{ request()->is('profil*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-600' }}"></i>
    <span>Profil Saya</span>
</a>

<a href="{{ url('/panduan') }}"
   class="{{ request()->is('panduan*') ? $navOn : $navOff }}">
    <i class="ti ti-book-2 text-base w-5 mr-2.5 shrink-0 {{ request()->is('panduan*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-600' }}"></i>
    <span>Panduan Penggunaan</span>
</a>

<a href="{{ route('admin.tutorial-videos.index') }}"
   class="{{ request()->routeIs('admin.tutorial-videos.*') ? $navOn : $navOff }}">
    <i class="ti ti-video text-base w-5 mr-2.5 shrink-0 {{ request()->routeIs('admin.tutorial-videos.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-600' }}"></i>
    <span>Video Tutorial</span>
</a>
