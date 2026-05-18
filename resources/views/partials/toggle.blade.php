@props(['name', 'checked' => false, 'label' => '', 'disabled' => false])

<style>
.squire-toggle {
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    margin-bottom: 0;
    font-size: 0.95rem;
    color: #efefef;
}
.squire-toggle input[type="checkbox"] {
    display: none;
}
.squire-toggle-slider {
    position: relative;
    width: 46px;
    height: 24px;
    background-color: #3a1a1a;
    border: 1px solid #8b3a3a;
    border-radius: 24px;
    transition: background-color 0.2s ease;
    flex-shrink: 0;
}
.squire-toggle-slider::after {
    content: '';
    position: absolute;
    top: 3px;
    left: 3px;
    width: 16px;
    height: 16px;
    background-color: #8b3a3a;
    border-radius: 50%;
    transition: transform 0.2s ease, background-color 0.2s ease;
}
.squire-toggle input:checked + .squire-toggle-slider {
    background-color: #8b3a3a;
    border-color: #efefef;
}
.squire-toggle input:checked + .squire-toggle-slider::after {
    transform: translateX(22px);
    background-color: #efefef;
}
.squire-toggle.disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
}
</style>

<label class="squire-toggle {{ $disabled ? 'disabled' : '' }}">
    <input
        type="checkbox"
        name="{{ $name }}"
        value="1"
        {{ $checked ? 'checked' : '' }}
        {{ $disabled ? 'disabled' : '' }}
    >
    <span class="squire-toggle-slider"></span>
    {{ $label }}
</label>