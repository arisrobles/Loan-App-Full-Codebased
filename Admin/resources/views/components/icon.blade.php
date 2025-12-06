@props(['d', 'title' => 'icon', 'class' => 'h-5 w-5 shrink-0'])
<svg xmlns="http://www.w3.org/2000/svg" {{ $attributes->merge(['class' => $class]) }}
     viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-label="{{ $title }}">
  <path d="{{ $d }}" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
