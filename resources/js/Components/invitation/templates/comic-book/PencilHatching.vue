<!-- AI: see docs/superpowers/specs/premium-templates/comic-book-design.md before editing -->
<template>
    <svg width="0" height="0" class="cb-pencil-hatch-defs" aria-hidden="true">
        <defs>
            <filter id="cb-pencil-hatch" x="0%" y="0%" width="100%" height="100%">
                <feTurbulence type="fractalNoise" baseFrequency="0.04" numOctaves="3" result="noise"/>
                <feDisplacementMap in="SourceGraphic" in2="noise" scale="3"/>
                <feColorMatrix type="matrix" values="
                    0.8 0.1 0.1 0 0
                    0.1 0.8 0.1 0 0
                    0.1 0.1 0.8 0 0
                    0   0   0   1 0"/>
                <feComponentTransfer>
                    <feFuncR type="discrete" tableValues="0.1 0.3 0.5 0.7 0.9"/>
                    <feFuncG type="discrete" tableValues="0.1 0.3 0.5 0.7 0.9"/>
                    <feFuncB type="discrete" tableValues="0.1 0.3 0.5 0.7 0.9"/>
                </feComponentTransfer>
            </filter>
        </defs>
    </svg>
</template>

<style scoped>
.cb-pencil-hatch-defs {
    position: absolute;
    width: 0;
    height: 0;
    overflow: hidden;
    pointer-events: none;
}
</style>
