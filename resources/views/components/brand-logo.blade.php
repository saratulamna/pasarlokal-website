@props([
    'class' => '',
    'height' => 44,
])

<a
    href="{{ route('home') }}"
    class="pl-brand-logo {{ $class }}"
    aria-label="PasarLokal - Beranda"
>
    <img
        src="{{ asset('images/Logo-pasarlokal.png') }}"
        alt="Logo PasarLokal"
        class="pl-brand-logo__image"
        style="height: {{ (int) $height }}px; width: auto; max-width: 100%; object-fit: contain;"
    >
</a>
