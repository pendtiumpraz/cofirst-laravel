@props([
    'user',
    'size' => 'md',
    'class' => '',
    'showName' => false,
    'gradient' => 'blue'
])

@php
$sizeClasses = [
    'xs' => 'w-6 h-6 text-xs',
    'sm' => 'w-8 h-8 text-sm',
    'md' => 'w-12 h-12 text-lg',
    'lg' => 'w-16 h-16 text-xl',
    'xl' => 'w-20 h-20 text-2xl',
    '2xl' => 'w-24 h-24 text-3xl',
    '3xl' => 'w-32 h-32 text-4xl'
];

$gradients = [
    'blue' => 'from-blue-500 to-purple-600',
    'green' => 'from-green-500 to-teal-600',
    'purple' => 'from-purple-500 to-pink-600',
    'orange' => 'from-orange-500 to-red-600',
    'indigo' => 'from-indigo-500 to-blue-600',
    'teal' => 'from-teal-500 to-green-600'
];

$sizeClass = $sizeClasses[$size] ?? $sizeClasses['md'];
$gradientClass = $gradients[$gradient] ?? $gradients['blue'];

// Get user initials (first letter of first name + first letter of last name if available)
$nameParts = explode(' ', trim($user->name ?? ''));
$initials = strtoupper(substr($nameParts[0] ?? '', 0, 1));
if (count($nameParts) > 1) {
    $initials .= strtoupper(substr(end($nameParts), 0, 1));
}
@endphp

<div class="flex items-center {{ $class }}">
    <div class="relative">
        @if($user->profile_photo_path)
            <img src="{{ $user->profile_photo_url }}" 
                 alt="{{ $user->name }}" 
                 class="{{ $sizeClass }} rounded-full object-cover border-2 border-white/20 shadow-lg">
        @else
            <div class="{{ $sizeClass }} bg-gradient-to-br {{ $gradientClass }} rounded-full flex items-center justify-center shadow-lg">
                <span class="text-white font-bold">{{ $initials }}</span>
            </div>
        @endif
        
        <!-- Online status indicator (optional) -->
        @if(isset($showOnline) && $showOnline && isset($user->is_online) && $user->is_online)
            <div class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white rounded-full"></div>
        @endif
    </div>
    
    @if($showName)
        <div class="ml-3">
            <p class="font-medium text-gray-900">{{ $user->name }}</p>
            @if($user->roles->isNotEmpty())
                <p class="text-sm text-gray-500 capitalize">{{ $user->roles->first()->name }}</p>
            @endif
        </div>
    @endif
</div> 