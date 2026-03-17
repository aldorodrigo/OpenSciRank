@once
<style>
    .field-tooltip-wrap .field-tooltip-text {
        pointer-events: none;
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        margin-bottom: 0.5rem;
        width: 16rem;
        border-radius: 0.5rem;
        background-color: #1f2937;
        padding: 0.5rem 0.75rem;
        font-size: 0.75rem;
        font-weight: 400;
        line-height: 1.625;
        color: #fff;
        opacity: 0;
        box-shadow: 0 10px 15px -3px rgba(0,0,0,.1);
        transition: opacity 0.2s;
        z-index: 50;
        text-align: center;
    }
    .field-tooltip-wrap .field-tooltip-text::after {
        content: '';
        position: absolute;
        left: 50%;
        top: 100%;
        transform: translateX(-50%);
        border: 4px solid transparent;
        border-top-color: #1f2937;
    }
    .field-tooltip-wrap:hover .field-tooltip-text {
        opacity: 1;
    }
    .field-tooltip-wrap {
        padding: 2px;
        border-radius: 9999px;
        transition: background-color 0.2s, transform 0.15s;
    }
    .field-tooltip-wrap:hover {
        background-color: rgba(99, 102, 241, 0.1);
        transform: scale(1.15);
    }
    .field-tooltip-wrap:hover svg {
        color: #6366f1;
    }
</style>
@endonce

@props(['text'])
<span class="field-tooltip-wrap relative ml-1 inline-flex align-middle">
    <svg class="h-4 w-4 shrink-0 text-gray-400 cursor-help" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"/>
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 16v-4m0-4h.01"/>
    </svg>
    <span class="field-tooltip-text">{{ $text }}</span>
</span>
