<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('superadmin'))
                        <x-nav-link :href="route('admin.schedules.index')" :active="request()->routeIs('admin.schedules.index')">
                            {{ __('Schedules') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.curriculums.index')" :active="request()->routeIs('admin.curriculums.*')">
                            {{ __('Kurikulum') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.syllabuses.index')" :active="request()->routeIs('admin.syllabuses.*')">
                            {{ __('Silabus') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.materials.index')" :active="request()->routeIs('admin.materials.*')">
                            {{ __('Materi') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.teacher.ranking')" :active="request()->routeIs('admin.teacher.ranking')">
                            {{ __('Teacher Ranking') }}
                        </x-nav-link>
                    @endif

                    @if(Auth::user()->hasRole('teacher'))
                        <x-nav-link :href="route('teacher.curriculums.index')" :active="request()->routeIs('teacher.curriculums.*')">
                            {{ __('Kurikulum') }}
                        </x-nav-link>
                    @endif

                    @if(Auth::user()->hasRole('student') && (!session('active_role') || session('active_role') === 'student'))
                        <x-nav-link :href="route('student.curriculum.index')" :active="request()->routeIs('student.curriculum.*')">
                            {{ __('Materi Saya') }}
                        </x-nav-link>
                    @endif
                    
                    @if(Auth::user()->hasRole('parent') && session('active_role') === 'parent')
                        <x-nav-link :href="route('parent.progress.index')" :active="request()->routeIs('parent.progress.*')">
                            {{ __('Progress Anak') }}
                        </x-nav-link>
                    @endif

                    @if(Auth::user()->hasAnyRole(['admin', 'superadmin', 'teacher']))
                        <x-nav-link :href="route('class-reports.index')" :active="request()->routeIs('class-reports.*')">
                            {{ __('Berita Acara') }}
                        </x-nav-link>
                    @endif

                    @if(Auth::user()->hasRole('superadmin'))
                        <x-nav-link :href="route('admin.roles.index')" :active="request()->routeIs('admin.roles.index')">
                            {{ __('Roles') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.permissions.index')" :active="request()->routeIs('admin.permissions.index')">
                            {{ __('Permissions') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        @if(Auth::user()->hasRole('parent') && Auth::user()->hasRole('student'))
                            <div class="border-t border-gray-100 my-1"></div>
                            <div class="px-4 py-2">
                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wider">Active Role</div>
                                <div class="text-sm text-gray-700 mt-1">
                                    {{ ucfirst(session('active_role', 'student')) }}
                                </div>
                            </div>
                            <form method="POST" action="{{ route('role.switch') }}" id="role-switch-form">
                                @csrf
                                <input type="hidden" name="role" id="switch-role-input">
                                @if(session('active_role', 'student') === 'student')
                                    <x-dropdown-link href="#" onclick="event.preventDefault(); switchRole('parent');">
                                        {{ __('Switch to Parent') }}
                                    </x-dropdown-link>
                                @else
                                    <x-dropdown-link href="#" onclick="event.preventDefault(); switchRole('student');">
                                        {{ __('Switch to Student') }}
                                    </x-dropdown-link>
                                @endif
                            </form>
                        @endif

                        <!-- Authentication -->
                        <div class="border-t border-gray-100 my-1"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('superadmin'))
                <x-responsive-nav-link :href="route('admin.schedules.index')" :active="request()->routeIs('admin.schedules.index')">
                    {{ __('Schedules') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.curriculums.index')" :active="request()->routeIs('admin.curriculums.*')">
                    {{ __('Kurikulum') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.syllabuses.index')" :active="request()->routeIs('admin.syllabuses.*')">
                    {{ __('Silabus') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.materials.index')" :active="request()->routeIs('admin.materials.*')">
                    {{ __('Materi') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.teacher.ranking')" :active="request()->routeIs('admin.teacher.ranking')">
                    {{ __('Teacher Ranking') }}
                </x-responsive-nav-link>
            @endif

            @if(Auth::user()->hasRole('teacher'))
                <x-responsive-nav-link :href="route('teacher.curriculums.index')" :active="request()->routeIs('teacher.curriculums.*')">
                    {{ __('Kurikulum') }}
                </x-responsive-nav-link>
            @endif

            @if(Auth::user()->hasRole('student') && (!session('active_role') || session('active_role') === 'student'))
                <x-responsive-nav-link :href="route('student.curriculum.index')" :active="request()->routeIs('student.curriculum.*')">
                    {{ __('Materi Saya') }}
                </x-responsive-nav-link>
            @endif
            
            @if(Auth::user()->hasRole('parent') && session('active_role') === 'parent')
                <x-responsive-nav-link :href="route('parent.progress.index')" :active="request()->routeIs('parent.progress.*')">
                    {{ __('Progress Anak') }}
                </x-responsive-nav-link>
            @endif

            @if(Auth::user()->hasAnyRole(['admin', 'superadmin', 'teacher']))
                <x-responsive-nav-link :href="route('class-reports.index')" :active="request()->routeIs('class-reports.*')">
                    {{ __('Berita Acara') }}
                </x-responsive-nav-link>
            @endif

            @if(Auth::user()->hasRole('superadmin'))
                <x-responsive-nav-link :href="route('admin.roles.index')" :active="request()->routeIs('admin.roles.index')">
                    {{ __('Roles') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.permissions.index')" :active="request()->routeIs('admin.permissions.index')">
                    {{ __('Permissions') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>

<script>
function switchRole(role) {
    document.getElementById('switch-role-input').value = role;
    
    fetch('{{ route('role.switch') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            role: role
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert('Error switching role: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error switching role');
    });
}
</script>