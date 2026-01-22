@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'w-full border-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-lg shadow-sm transition-colors']) }}>
